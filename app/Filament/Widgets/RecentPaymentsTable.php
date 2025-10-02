<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentPaymentsTable extends BaseWidget
{
    protected static ?string $heading = 'Pembayaran Terbaru';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Payment::query()->with(['donation.campaign', 'method'])->latest('id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('donation.reference')->label('Ref')->searchable(),
                Tables\Columns\TextColumn::make('donation.campaign.title')->label('Campaign')->limit(40),
                Tables\Columns\TextColumn::make('method.provider')->label('Provider')->badge(),
                Tables\Columns\TextColumn::make('provider_status')->label('Status')->badge(),
                Tables\Columns\TextColumn::make('net_amount')->label('Net')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float)$state, 2, ',', '.')),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Dibuat'),
            ])
            ->paginated([5,10,25])
            ->defaultPaginationPageOption(10);
    }
}
