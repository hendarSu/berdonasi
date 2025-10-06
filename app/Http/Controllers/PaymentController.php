<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Payment;
use App\Models\PaymentChannel;
use App\Models\PaymentMethod;
use App\Models\Wallet;
use App\Models\LedgerEntry;
use App\Models\WebhookEvent;
use App\Services\Payments\MidtransService;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Services\WaService;

class PaymentController extends Controller
{
    public function pay(Request $request, string $reference)
    {
        $donation = Donation::query()->with('campaign')->where('reference', $reference)->firstOrFail();

        $midtrans = new MidtransService();

        // Reuse token if already exists in meta
        $meta = $donation->meta_json ?? [];
        $token = $meta['midtrans']['snap_token'] ?? null;
        if (! $token) {
            // Ensure channel & method exist (Midtrans Snap)
            $channel = PaymentChannel::firstOrCreate(
                ['code' => 'MIDTRANS'],
                ['name' => 'Midtrans', 'active' => true]
            );
            $method = PaymentMethod::firstOrCreate(
                ['provider' => 'midtrans', 'method_code' => 'snap'],
                ['channel_id' => $channel->id, 'config_json' => null, 'active' => true, 'created_at' => now()]
            );

            $res = $midtrans->createSnapTransaction($donation);
            $token = $res['token'];
            $meta['midtrans'] = ($meta['midtrans'] ?? []) + [
                'snap_token' => $token,
                'order_id' => $res['order_id'] ?? $donation->reference,
                'redirect_url' => $res['redirect_url'] ?? null,
            ];
            $donation->meta_json = $meta;
            $donation->save();

            // Create payment record if not exists for this donation
            $existing = Payment::query()->where('donation_id', $donation->id)->latest('id')->first();
            if (! $existing) {
                Payment::create([
                    'donation_id' => $donation->id,
                    'payment_method_id' => $method->id,
                    'provider_txn_id' => $res['order_id'] ?? $donation->reference,
                    'provider_status' => 'initiated',
                    'gross_amount' => $res['gross_amount'] ?? $donation->amount,
                    'fee_amount' => 0,
                    'net_amount' => $res['gross_amount'] ?? $donation->amount,
                    'payload_req_json' => $res['request'] ?? null,
                    'payload_res_json' => $res['response'] ?? null,
                ]);
            }
        }

        return view('donation.pay', [
            'donation' => $donation,
            'snapToken' => $token,
            'clientKey' => $midtrans->clientKey(),
            'snapJsUrl' => $midtrans->snapJsUrl(),
        ]);
    }

    public function notify(Request $request)
    {
        $payload = $request->all();

        $midtrans = new MidtransService();
        if (! $midtrans->validateNotificationSignature($payload)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $orderId = $payload['order_id'] ?? null;
        if (! $orderId) {
            return response()->json(['message' => 'Missing order_id'], 422);
        }

        $donation = Donation::query()->where('reference', $orderId)->first();
        if (! $donation) {
            return response()->json(['message' => 'Donation not found'], 404);
        }
        // Find latest payment for this donation
        $payment = Payment::query()->where('donation_id', $donation->id)->latest('id')->first();

        // Record webhook first
        $webhook = WebhookEvent::create([
            'payment_id' => $payment?->id,
            'event_type' => $payload['transaction_status'] ?? null,
            'raw_body_json' => $payload,
            'signature' => $payload['signature_key'] ?? null,
            'received_at' => now(),
            'processed' => false,
        ]);

        $map = $midtrans->mapTransactionStatusToDonation($payload);

        // Update payment info
        if ($payment) {
            $payment->provider_txn_id = $payload['transaction_id'] ?? ($payload['order_id'] ?? $payment->provider_txn_id);
            $payment->provider_status = $payload['transaction_status'] ?? $payment->provider_status;
            $gross = (float) ($payload['gross_amount'] ?? $donation->amount);
            $payment->gross_amount = $gross;
            // If fee unknown, keep 0 for now
            $payment->fee_amount = $payment->fee_amount ?? 0;
            $payment->net_amount = $gross - (float) $payment->fee_amount;
            $resPayload = $payment->payload_res_json ?? [];
            $resPayload['last_notification'] = $payload;
            $payment->payload_res_json = $resPayload;
            $payment->save();
        }

        // Update donation status
        $donation->status = $map['status'];
        $donation->paid_at = $map['paid_at'];
        $meta = $donation->meta_json ?? [];
        $meta['midtrans'] = ($meta['midtrans'] ?? []) + [
            'notification' => $payload,
            'last_updated_at' => now()->toISOString(),
        ];
        $donation->meta_json = $meta;
        $donation->save();

        // If paid, credit campaign wallet and update campaign raised amount
        if ($donation->status === 'paid') {
            $campaign = $donation->campaign()->first();
            if ($campaign) {
                // credit to campaign's wallet
                $ownerType = \App\Models\Campaign::class;
                $ownerId = $campaign->id;
                $wallet = Wallet::firstOrCreate(
                    ['owner_type' => $ownerType, 'owner_id' => $ownerId],
                    ['balance' => 0, 'settings_json' => null]
                );

                $amount = $payment?->net_amount ?? $donation->amount;
                $wallet->balance = (float) $wallet->balance + (float) $amount;
                $wallet->save();

                LedgerEntry::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'credit',
                    'amount' => $amount,
                    'source_type' => Donation::class,
                    'source_id' => $donation->id,
                    'memo' => 'Donation ' . $donation->reference,
                    'balance_after' => $wallet->balance,
                    'created_at' => now(),
                ]);

                // Update campaign raised_amount
                $campaign->raised_amount = (float) $campaign->raised_amount + (float) $amount;
                $campaign->save();
            }

            // Optionally send WA message on payment success (only once overall)
            try {
                $svc = new WaService();
                $cfg = $svc->getConfig();
                if ((bool)($cfg['send_enabled'] ?? false) && ! empty($cfg['send_client_id'])) {
                    $orgName = $donation->campaign?->organization?->name ?? config('app.name');
                    $vars = [
                        'donor_name' => (string)($donation->donor_name ?? ''),
                        'donor_phone' => (string)($donation->donor_phone ?? ''),
                        'donor_email' => (string)($donation->donor_email ?? ''),
                        'amount' => number_format((float)$donation->amount, 0, ',', '.'),
                        'amount_raw' => (string)$donation->amount,
                        'campaign_title' => (string)($donation->campaign?->title ?? ''),
                        'campaign_url' => $donation->campaign ? route('campaign.show', $donation->campaign->slug) : '',
                        'pay_url' => route('donation.pay', ['reference' => $donation->reference]),
                        'donation_reference' => (string)$donation->reference,
                        'organization_name' => (string)$orgName,
                    ];
                    $template = (string) ($cfg['message_template'] ?? '');
                    if ($template !== '' && ! empty($donation->donor_phone)) {
                        $already = (bool) (data_get($donation->meta_json, 'wa.sent')
                                   || data_get($donation->meta_json, 'wa.sent_initiated')
                                   || data_get($donation->meta_json, 'wa.sent_paid'));
                        if (! $already) {
                            $message = $svc->renderTemplate($template, $vars);
                            $ok = $svc->sendText((string)$donation->donor_phone, $message);
                            if ($ok) {
                                $meta = $donation->meta_json ?? [];
                                $meta['wa'] = ($meta['wa'] ?? []) + [
                                    'sent' => now()->toISOString(),
                                    'sent_event' => 'paid',
                                ];
                                $donation->meta_json = $meta;
                                $donation->save();
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // ignore WA failures silently
            }
        }

        // Mark webhook processed
        $webhook->processed = true;
        $webhook->processed_at = now();
        $webhook->save();

        return response()->json(['message' => 'ok']);
    }
}
