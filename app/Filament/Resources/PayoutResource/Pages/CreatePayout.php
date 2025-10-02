<?php

namespace App\Filament\Resources\PayoutResource\Pages;

use App\Filament\Resources\PayoutResource;
use App\Models\LedgerEntry;
use App\Models\Payout;
use App\Models\Wallet;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreatePayout extends CreateRecord
{
    protected static string $resource = PayoutResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['status'] = $data['status'] ?? 'pending';
        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Payout $payout */
        $payout = $this->record;

        DB::transaction(function () use ($payout) {
            $wallet = Wallet::lockForUpdate()->findOrFail($payout->wallet_id);

            $items = $payout->items()->get();
            $total = $items->sum('amount');

            // Update payout total in case it changed
            $payout->amount = $total;
            $payout->save();

            foreach ($items as $item) {
                $newBalance = (float) $wallet->balance - (float) $item->amount;
                LedgerEntry::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'debit',
                    'amount' => $item->amount,
                    'source_type' => $payout->getMorphClass(),
                    'source_id' => $payout->id,
                    'memo' => $item->memo,
                    'balance_after' => $newBalance,
                    'created_at' => now(),
                ]);
                $wallet->balance = $newBalance;
            }

            $wallet->save();
        });
    }
}

