<?php

namespace App\Filament\Pages;

use App\Models\Donation;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Donors extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Manajemen';
    protected static ?string $navigationLabel = 'Donatur';
    protected static ?int $navigationSort = 60;

    protected static string $view = 'filament.pages.donors';

    public function getTitle(): string
    {
        return 'Donatur';
    }

    protected function getTableQuery(): Builder
    {
        $identityExpr = "COALESCE(NULLIF(TRIM(donor_email), ''), NULLIF(TRIM(donor_phone), ''), NULLIF(TRIM(donor_name), ''))";
        $identity = DB::raw($identityExpr);

        return Donation::query()
            ->select([
                DB::raw('MIN(id) as id'),
                DB::raw("MAX(donor_name) as donor_name"),
                DB::raw("MAX(donor_email) as donor_email"),
                DB::raw("MAX(donor_phone) as donor_phone"),
                DB::raw("SUM(CASE WHEN status='paid' THEN amount ELSE 0 END) as total_amount"),
                DB::raw("SUM(CASE WHEN status='paid' THEN 1 ELSE 0 END) as donation_count"),
                DB::raw("MAX(paid_at) as last_paid_at"),
                DB::raw($identityExpr . " as identity"),
            ])
            ->where(function ($w) {
                $w->whereNotNull('donor_email')
                  ->orWhereNotNull('donor_phone')
                  ->orWhereNotNull('donor_name');
            })
            ->groupBy(DB::raw($identityExpr));
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('donor_name')
                ->label('Nama')
                ->formatStateUsing(function ($state, $record) {
                    $name = $record->donor_name;
                    $email = $record->donor_email;
                    $phone = $record->donor_phone;
                    return $name ?: ($email ?: ($phone ?: '—'));
                })
                ->searchable(['donor_name', 'donor_email', 'donor_phone'])
                ->wrap(),
            Tables\Columns\TextColumn::make('donor_email')
                ->label('Email')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('donor_phone')
                ->label('Nomor HP')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('total_amount')
                ->label('Total Donasi')
                ->money('idr', true)
                ->sortable(),
            Tables\Columns\TextColumn::make('donation_count')
                ->label('Transaksi')
                ->sortable(),
            Tables\Columns\TextColumn::make('last_paid_at')
                ->label('Terakhir')
                ->dateTime()
                ->sortable(),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('detail')
                ->label('Detail')
                ->icon('heroicon-o-eye')
                ->modalHeading('Detail Donatur')
                ->modalCancelActionLabel('Tutup')
                ->modalContent(function ($record) {
                    $identity = (string) ($record->getAttribute('identity') ?? '');
                    $identityExpr = "COALESCE(NULLIF(TRIM(donor_email), ''), NULLIF(TRIM(donor_phone), ''), NULLIF(TRIM(donor_name), ''))";

                    $rows = Donation::query()
                        ->select([
                            'campaign_id',
                            DB::raw('COUNT(*) as trx'),
                            DB::raw("SUM(CASE WHEN status='paid' THEN amount ELSE 0 END) as total"),
                            DB::raw('MAX(paid_at) as last_paid_at'),
                        ])
                        ->whereRaw($identityExpr . ' = ?', [$identity])
                        ->groupBy('campaign_id')
                        ->with(['campaign:id,title,slug'])
                        ->orderByDesc(DB::raw('MAX(paid_at)'))
                        ->get();

                    return view('filament.pages.partials.donor-detail', [
                        'identity' => $identity,
                        'rows' => $rows,
                    ]);
                }),
        ];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'Belum ada data donatur';
    }
}
