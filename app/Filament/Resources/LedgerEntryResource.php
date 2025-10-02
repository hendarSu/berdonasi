<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LedgerEntryResource\Pages;
use App\Models\LedgerEntry;
use App\Models\Campaign;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LedgerEntryResource extends Resource
{
    protected static ?string $model = LedgerEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Ledger Entries';

    protected static ?int $navigationSort = 10;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('wallet.owner_type')
                    ->label('Owner Type')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('wallet.owner.title')
                    ->label('Campaign')
                    ->getStateUsing(fn ($record) => optional(optional($record->wallet->owner)->title))
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'success' => 'credit',
                        'danger' => 'debit',
                    ]),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 2, ',', '.')),
                Tables\Columns\TextColumn::make('memo')->limit(40),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
                Tables\Columns\TextColumn::make('balance_after')
                    ->label('Balance After')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 2, ',', '.'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'credit' => 'Credit',
                        'debit' => 'Debit',
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLedgerEntries::route('/'),
        ];
    }
}

