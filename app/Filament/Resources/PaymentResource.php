<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Donation;
use App\Models\Wallet;
use App\Models\LedgerEntry;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Pembayaran';

    protected static ?string $navigationLabel = 'Payments';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        // Not used (monitoring only)
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('donation.reference')
                    ->label('Reference')
                    ->searchable(),
                Tables\Columns\TextColumn::make('donation.campaign.title')
                    ->label('Campaign')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('method.provider')
                    ->label('Provider')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('method.method_code')
                    ->label('Method')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BadgeColumn::make('provider_status')
                    ->label('Status')
                    ->colors([
                        'success' => ['settlement', 'capture'],
                        'warning' => ['pending'],
                        'danger' => ['expire', 'cancel', 'deny', 'failed'],
                        'gray' => ['initiated', null, ''],
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('provider_txn_id')
                    ->label('Transaction ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BadgeColumn::make('manual_status')
                    ->label('Manual')
                    ->colors([
                        'success' => ['approved'],
                        'warning' => ['pending'],
                        'danger' => ['rejected'],
                        'gray' => [null, ''],
                    ])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('gross_amount')
                    ->label('Gross')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 2, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('fee_amount')
                    ->label('Fee')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 2, ',', '.'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('net_amount')
                    ->label('Net')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 2, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Dibuat')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->label('Diupdate')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('provider_status')
                    ->options([
                        'initiated' => 'initiated',
                        'pending' => 'pending',
                        'capture' => 'capture',
                        'settlement' => 'settlement',
                        'expire' => 'expire',
                        'cancel' => 'cancel',
                        'deny' => 'deny',
                        'failed' => 'failed',
                    ]),
                Tables\Filters\SelectFilter::make('manual_status')
                    ->label('Manual Status')
                    ->options([
                        'pending' => 'pending',
                        'approved' => 'approved',
                        'rejected' => 'rejected',
                    ]),
                Tables\Filters\SelectFilter::make('method_id')
                    ->relationship('method', 'method_code')
                    ->label('Method'),
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari'),
                        Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('payload')
                    ->label('Payload')
                    ->modalHeading('Payload Detail')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(function (Payment $record) {
                        $req = json_encode($record->payload_req_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                        $res = json_encode($record->payload_res_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                        $html = '<div class="space-y-4">'
                            . '<div><div class="font-semibold mb-2">Request</div><pre class="text-xs">' . e($req) . '</pre></div>'
                            . '<div><div class="font-semibold mb-2">Response</div><pre class="text-xs">' . e($res) . '</pre></div>'
                            . '</div>';
                        return new HtmlString($html);
                    }),
                Tables\Actions\Action::make('view_proof')
                    ->label('Bukti')
                    ->visible(fn (Payment $record) => filled($record->manual_proof_path))
                    ->modalHeading('Bukti Transfer')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(function (Payment $record) {
                        try {
                            $url = Storage::disk('s3')->temporaryUrl((string)$record->manual_proof_path, now()->addMinutes(10));
                        } catch (\Throwable) {
                            $url = Storage::disk('s3')->url((string)$record->manual_proof_path);
                        }
                        $ext = strtolower(pathinfo((string)$record->manual_proof_path, PATHINFO_EXTENSION));
                        if (in_array($ext, ['pdf'])) {
                            $html = '<div class="space-y-2"><a href="' . e($url) . '" target="_blank" class="text-sky-600">Buka bukti (PDF)</a></div>';
                        } else {
                            $html = '<div class="space-y-2"><img src="' . e($url) . '" class="max-w-full rounded-md border" /></div>';
                        }
                        return new HtmlString($html);
                    }),
                Tables\Actions\Action::make('approve_manual')
                    ->label('Setujui')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Payment $record) => ($record->method?->provider === 'manual') && ($record->manual_status === 'pending'))
                    ->action(function (Payment $record) {
                        DB::transaction(function () use ($record) {
                            // Update manual status
                            $record->manual_status = 'approved';
                            // Force provider status to settlement on approval
                            $record->provider_status = 'settlement';
                            $record->manual_reviewed_by = auth()->id();
                            $record->manual_reviewed_at = now();
                            $record->save();

                            $donation = $record->donation;
                            if ($donation && $donation->status !== 'paid') {
                                $donation->status = 'paid';
                                $donation->paid_at = now();
                                $donation->save();

                                // Credit wallet and ledger
                                $campaign = $donation->campaign()->first();
                                if ($campaign) {
                                    $wallet = Wallet::firstOrCreate(
                                        ['owner_type' => \App\Models\Campaign::class, 'owner_id' => $campaign->id],
                                        ['balance' => 0, 'settings_json' => null]
                                    );
                                    $amount = $record->net_amount ?: $donation->amount;
                                    $wallet->balance = (float)$wallet->balance + (float)$amount;
                                    $wallet->save();

                                    LedgerEntry::create([
                                        'wallet_id' => $wallet->id,
                                        'type' => 'credit',
                                        'amount' => $amount,
                                        'source_type' => Donation::class,
                                        'source_id' => $donation->id,
                                        'memo' => 'Donation ' . $donation->reference . ' (manual)',
                                        'balance_after' => $wallet->balance,
                                        'created_at' => now(),
                                    ]);

                                    $campaign->raised_amount = (float)$campaign->raised_amount + (float)$amount;
                                    $campaign->save();
                                }
                            }
                        });
                        // Send WA success message (manual) if enabled and not yet sent
                        try {
                            $donation = $record->donation;
                            if ($donation) {
                                $svc = new \App\Services\WaService();
                                $cfg = $svc->getConfig();
                                if ((bool)($cfg['send_enabled'] ?? false) && ! empty($cfg['send_client_id'])) {
                                    $orgName = $donation->campaign?->organization?->name ?? config('app.name');
                                    $ptype = (string) data_get($donation->meta_json, 'payment_type', 'automatic');
                                    $payUrl = route('donation.pay', ['reference' => $donation->reference]);
                                    if ($ptype === 'manual') {
                                        $payUrl = route('donation.manual', ['reference' => $donation->reference]);
                                    }
                                    $vars = [
                                        'donor_name' => (string)($donation->donor_name ?? ''),
                                        'donor_phone' => (string)($donation->donor_phone ?? ''),
                                        'donor_email' => (string)($donation->donor_email ?? ''),
                                        'amount' => number_format((float)$donation->amount, 0, ',', '.'),
                                        'amount_raw' => (string)$donation->amount,
                                        'campaign_title' => (string)($donation->campaign?->title ?? ''),
                                        'campaign_url' => $donation->campaign ? route('campaign.show', $donation->campaign->slug) : '',
                                        'pay_url' => $payUrl,
                                        'donation_reference' => (string)$donation->reference,
                                        'organization_name' => (string)$orgName,
                                    ];
                                    $template = (string) ($cfg['message_template_paid'] ?? ($cfg['message_template'] ?? ''));
                                    $already = (bool) (data_get($donation->meta_json, 'wa.sent')
                                               || data_get($donation->meta_json, 'wa.sent_initiated')
                                               || data_get($donation->meta_json, 'wa.sent_paid'));
                                    if ($template !== '' && ! empty($donation->donor_phone) && ! $already) {
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
                            // ignore failures
                        }
                    }),
                Tables\Actions\Action::make('reject_manual')
                    ->label('Tolak')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Payment $record) => ($record->method?->provider === 'manual') && ($record->manual_status === 'pending'))
                    ->form([
                        Forms\Components\Textarea::make('reason')->label('Alasan')->rows(3),
                    ])
                    ->action(function (Payment $record, array $data) {
                        $record->manual_status = 'rejected';
                        $record->manual_note = trim(($record->manual_note ? $record->manual_note . "\n" : '') . (string)($data['reason'] ?? ''));
                        $record->manual_reviewed_by = auth()->id();
                        $record->manual_reviewed_at = now();
                        $record->provider_status = $record->provider_status ?: 'failed';
                        $record->save();

                        $donation = $record->donation;
                        if ($donation && $donation->status !== 'paid') {
                            $donation->status = 'failed';
                            $donation->save();
                        }
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
        ];
    }
}
