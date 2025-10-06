<?php

namespace App\Filament\Pages;

use App\Models\Organization;
use Filament\Forms;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;

class WaServiceSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationGroup = 'Integrasi';
    protected static ?string $navigationLabel = 'WA Service - Setting';
    protected static ?int $navigationSort = 10;
    protected static ?string $slug = 'integrations/wa-service/settings';
    protected static ?string $title = 'WA Service - Pengaturan';

    protected static string $view = 'filament.pages.wa-service-settings';

    public array $data = [];

    public ?Organization $org = null;

    public function mount(): void
    {
        $this->org = Organization::query()->first();
        $meta = $this->org?->meta_json ?? [];
        $cfg = data_get($meta, 'integrations.wa_service', data_get($meta, 'wa_service', []));

        $this->form->fill([
            'url' => $cfg['url'] ?? 'http://localhost:3100',
            'type_secret' => $cfg['type_secret'] ?? 'headers',
            'headers' => $cfg['headers'] ?? ($cfg['value_secret'] ?? ['x-api-key' => 'keyadmin']),
            'validate_client_id' => $cfg['validate_client_id'] ?? '',
            'validate_enabled' => (bool)($cfg['validate_enabled'] ?? false),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Section::make('WA Service')
                    ->description('Konfigurasi koneksi ke layanan WhatsApp internal')
                    ->schema([
                        TextInput::make('url')
                            ->label('Base URL')
                            ->placeholder('http://localhost:3100')
                            ->required()
                            ->maxLength(255),
                        Toggle::make('validate_enabled')
                            ->label('Aktifkan Validasi Nomor WA di Form Donasi')
                            ->inline(false),
                        TextInput::make('validate_client_id')
                            ->label('Client ID untuk Validasi Nomor')
                            ->placeholder('contoh: 6285xxxxxxxxxx')
                            ->helperText('Client/nomor WA yang dipakai untuk memvalidasi nomor WA donatur.')
                            ->maxLength(30),
                        Select::make('type_secret')
                            ->label('Tipe Secret')
                            ->options([
                                'headers' => 'Headers',
                            ])
                            ->required()
                            ->native(false),
                        KeyValue::make('headers')
                            ->label('Headers')
                            ->helperText('Contoh: x-api-key => keyadmin')
                            ->keyLabel('Header Name')
                            ->valueLabel('Header Value')
                            ->addButtonLabel('Tambah Header')
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('simpan')
                ->label('Simpan')
                ->submit('save')
                ->color('primary'),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $meta = $this->org?->meta_json ?? [];
        data_set($meta, 'integrations.wa_service', [
            'url' => $state['url'] ?? null,
            'type_secret' => $state['type_secret'] ?? 'headers',
            'headers' => $state['headers'] ?? [],
            'validate_client_id' => $state['validate_client_id'] ?? null,
            'validate_enabled' => (bool)($state['validate_enabled'] ?? false),
        ]);

        $org = $this->org ?? new Organization();
        $org->meta_json = $meta;
        $org->save();
        $this->org = $org;

        Notification::make()
            ->title('Pengaturan WA Service disimpan')
            ->success()
            ->send();
    }
}
