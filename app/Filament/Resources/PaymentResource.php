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

