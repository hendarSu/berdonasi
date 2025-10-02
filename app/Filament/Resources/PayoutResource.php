<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayoutResource\Pages;
use App\Models\Campaign;
use App\Models\Payout;
use App\Models\Wallet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PayoutResource extends Resource
{
    protected static ?string $model = Payout::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Payouts';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Sumber Dana')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('campaign_id')
                            ->label('Campaign')
                            ->options(fn () => Campaign::query()->pluck('title', 'id'))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $campaign = Campaign::find($state);
                                if (! $campaign) { $set('wallet_id', null); return; }
                                $wallet = $campaign->wallet; 
                                if (! $wallet) { $wallet = $campaign->wallet()->create(['balance' => 0]); }
                                $set('wallet_id', $wallet->id);
                            })
                            ->dehydrated(false),
                        Forms\Components\Hidden::make('wallet_id')
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Total')
                            ->numeric()
                            ->readOnly()
                            ->default(0),
                        Forms\Components\TextInput::make('status')
                            ->default('pending')
                            ->readOnly(),
                    ]),

                Forms\Components\Section::make('Detail Penyaluran (Items)')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\TextInput::make('memo')->label('Keterangan')->maxLength(255),
                                Forms\Components\TextInput::make('amount')->label('Jumlah')->numeric()->required(),
                            ])
                            ->mutateDehydratedStateUsing(function ($state, callable $set) {
                                $total = collect($state)->sum(fn ($i) => (float)($i['amount'] ?? 0));
                                $set('amount', $total);
                                return $state;
                            })
                            ->defaultItems(1)
                            ->minItems(1)
                            ->reorderable(false)
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('wallet.owner.title')->label('Campaign')
                    ->getStateUsing(fn ($record) => optional($record->wallet->owner)->title),
                Tables\Columns\TextColumn::make('amount')->formatStateUsing(fn ($state) => 'Rp ' . number_format((float)$state, 2, ',', '.')),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'processing',
                        'success' => 'completed',
                        'danger' => 'failed',
                    ]),
                Tables\Columns\TextColumn::make('creator.name')->label('Dibuat Oleh')->getStateUsing(fn ($record) => optional($record->creator)->name),
                Tables\Columns\TextColumn::make('requested_at')->dateTime()->label('Diminta'),
                Tables\Columns\TextColumn::make('processed_at')->dateTime()->label('Diproses')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayouts::route('/'),
            'create' => Pages\CreatePayout::route('/create'),
            'edit' => Pages\EditPayout::route('/{record}/edit'),
        ];
    }
}
