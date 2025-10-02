<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignArticleResource\Pages;
use App\Models\Campaign;
use App\Models\CampaignArticle;
use App\Models\Payout;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CampaignArticleResource extends Resource
{
    protected static ?string $model = CampaignArticle::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan Penyaluran';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Artikel Laporan')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('campaign_id')
                            ->label('Campaign')
                            ->options(fn () => Campaign::query()->pluck('title', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('title')->label('Judul')->required()->maxLength(255),
                        Forms\Components\Select::make('payout_id')
                            ->label('Terkait Payout')
                            ->options(fn () => Payout::query()->with('wallet.owner')->get()->mapWithKeys(fn ($p) => [
                                $p->id => 'Payout #' . $p->id . ' - ' . (optional($p->wallet->owner)->title ?? 'Wallet') . ' - Rp ' . number_format((float)$p->amount, 2, ',', '.'),
                            ]))
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->hint('Opsional'),
                        Forms\Components\DateTimePicker::make('published_at')->label('Terbit')->seconds(false),
                        Forms\Components\MarkdownEditor::make('body_md')->label('Isi')->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('campaign.title')->label('Campaign')->searchable(),
                Tables\Columns\TextColumn::make('payout_id')->label('Payout')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('published_at')->dateTime()->label('Terbit'),
                Tables\Columns\TextColumn::make('author_id')->label('Penulis')->getStateUsing(fn ($record) => optional($record->author)->name),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('published_at', 'desc')
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
            'index' => Pages\ListCampaignArticles::route('/'),
            'create' => Pages\CreateCampaignArticle::route('/create'),
            'edit' => Pages\EditCampaignArticle::route('/{record}/edit'),
        ];
    }
}
