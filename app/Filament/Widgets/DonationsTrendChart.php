<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DonationsTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Donasi 30 Hari (Netto)';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 1;
    protected function getData(): array
    {
        $start = now()->subDays(29)->startOfDay();
        $end = now()->endOfDay();

        $rows = Payment::query()
            ->selectRaw('DATE(donations.paid_at) as d, SUM(payments.net_amount) as total')
            ->join('donations', 'donations.id', '=', 'payments.donation_id')
            ->where('donations.status', 'paid')
            ->whereBetween('donations.paid_at', [$start, $end])
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        $labels = [];
        $data = [];
        for ($i = 0; $i < 30; $i++) {
            $date = $start->copy()->addDays($i)->toDateString();
            $labels[] = $date;
            $data[] = (float) ($rows[$date]->total ?? 0);
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

