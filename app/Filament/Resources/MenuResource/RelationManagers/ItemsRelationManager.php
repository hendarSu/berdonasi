<?php

namespace App\Filament\Resources\MenuResource\RelationManagers;

use App\Models\MenuItem;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->required(),
                Forms\Components\Select::make('parent_id')
                    ->label('Parent')
                    ->options(fn () => MenuItem::query()->where('menu_id', $this->ownerRecord->id)->pluck('title', 'id'))
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->nullable(),
                Forms\Components\TextInput::make('url')->label('URL')->helperText('Kosongkan jika memilih Page'),
                Forms\Components\Select::make('page_id')->label('Page')
                    ->relationship('page', 'title')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->nullable(),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\Toggle::make('active')->label('Aktif')->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable(),
                Tables\Columns\TextColumn::make('parent.title')->label('Parent')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('url')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('page.title')->label('Page')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('Urutan')->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

