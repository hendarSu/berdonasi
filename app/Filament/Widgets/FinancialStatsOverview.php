<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\Payout;
use App\Models\Wallet;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class FinancialStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getCards(): array
    {
        $today = now()->toDateString();
        $startMonth = now()->startOfMonth()->toDateString();

        $paidPayments = Payment::query()->whereHas('donation', fn ($q) => $q->where('status', 'paid'));

        $totalNet = (float) ($paidPayments->clone()->sum('net_amount') ?? 0);
        $todayNet = (float) ($paidPayments->clone()->whereHas('donation', fn ($q) => $q->whereDate('paid_at', $today))->sum('net_amount') ?? 0);
        $monthNet = (float) ($paidPayments->clone()->whereHas('donation', fn ($q) => $q->whereDate('paid_at', '>=', $startMonth))->sum('net_amount') ?? 0);

        $walletBalance = (float) (Wallet::query()->sum('balance') ?? 0);
        $payoutCompleted = (float) (Payout::query()->where('status', 'completed')->sum('amount') ?? 0);
        $payoutPending = (float) (Payout::query()->whereIn('status', ['pending', 'processing'])->sum('amount') ?? 0);

        $fmt = fn ($n) => 'Rp ' . number_format($n, 2, ',', '.');

        return [
            Card::make('Terkumpul Hari Ini', $fmt($todayNet)),
            Card::make('Terkumpul Bulan Ini', $fmt($monthNet)),
            Card::make('Terkumpul (Netto)', $fmt($totalNet))
                ->descriptionIcon('heroicon-o-arrow-trending-up'),
            Card::make('Saldo Dompet', $fmt($walletBalance))
                ->description('Total semua wallet'),
            Card::make('Payout Selesai', $fmt($payoutCompleted))
                ->description('Akan mengurangi saldo')
                ->color('success'),
            Card::make('Payout Menunggu/Proses', $fmt($payoutPending))
                ->color('warning'),
        ];
    }
}

