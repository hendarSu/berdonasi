<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class DonationsTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Donasi 30 Hari (Netto)';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 1;
    protected function getData(): array
    {
        $timezone = 'Asia/Jakarta';
        $startLocal = now($timezone)->subDays(29)->startOfDay();
        $endLocal = now($timezone)->endOfDay();
        $startUtc = $startLocal->copy()->setTimezone('UTC');
        $endUtc = $endLocal->copy()->setTimezone('UTC');

        $totalsByDate = Payment::query()
            ->select(['payments.net_amount', 'donations.paid_at'])
            ->join('donations', 'donations.id', '=', 'payments.donation_id')
            ->where('donations.status', 'paid')
            ->whereBetween('donations.paid_at', [$startUtc, $endUtc])
            ->get()
            ->groupBy(fn ($row) => Carbon::parse($row->paid_at, 'UTC')->setTimezone($timezone)->toDateString())
            ->map(fn ($items) => (float) $items->sum('net_amount'));

        $labels = [];
        $data = [];
        for ($i = 0; $i < 30; $i++) {
            $date = $startLocal->copy()->addDays($i)->toDateString();
            $labels[] = $date;
            $data[] = (float) ($totalsByDate[$date] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Netto per Hari',
                    'data' => $data,
                    'borderColor' => '#0284c7',
                    'backgroundColor' => 'rgba(2,132,199,0.2)',
                    'tension' => 0.3,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
