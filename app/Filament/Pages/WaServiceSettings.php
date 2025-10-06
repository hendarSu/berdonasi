<?php

namespace App\Filament\Pages;

use App\Models\Organization;
use App\Services\WaService;
use Filament\Forms;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
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
    public array $accountOptions = [];

    public function mount(): void
    {
        $this->org = Organization::query()->first();
        $meta = $this->org?->meta_json ?? [];
        $cfg = data_get($meta, 'integrations.wa_service', data_get($meta, 'wa_service', []));

        // Load WA accounts as select options
        try {
            $accounts = (new WaService())->listAccounts();
            $opts = [];
            foreach ($accounts as $row) {
                $cid = (string)($row['clientId'] ?? '');
                if ($cid !== '') {
                    $status = strtoupper((string)($row['status'] ?? ''));
                    $opts[$cid] = $status ? ($cid . ' — ' . $status) : $cid;
                }
            }
            // Ensure existing configured values appear in options
            foreach ([(string)($cfg['validate_client_id'] ?? ''), (string)($cfg['send_client_id'] ?? '')] as $existing) {
                if ($existing !== '' && ! array_key_exists($existing, $opts)) {
                    $opts[$existing] = $existing;
                }
            }
            $this->accountOptions = $opts;
        } catch (\Throwable $e) {
            $this->accountOptions = [];
        }

        $this->form->fill([
            'url' => $cfg['url'] ?? 'http://localhost:3100',
            'type_secret' => $cfg['type_secret'] ?? 'headers',
            'headers' => $cfg['headers'] ?? ($cfg['value_secret'] ?? ['x-api-key' => 'keyadmin']),
            'validate_client_id' => $cfg['validate_client_id'] ?? '',
            'validate_enabled' => (bool)($cfg['validate_enabled'] ?? false),
            'send_enabled' => (bool)($cfg['send_enabled'] ?? false),
            'send_client_id' => $cfg['send_client_id'] ?? '',
            'message_template' => $cfg['message_template'] ?? (
                <<<HTML
<p>Halo {donor_name},</p>
<p>Terima kasih atas niat baik Anda untuk berdonasi di program "{campaign_title}".</p>
<p>Nominal: Rp {amount}
<br/>Referensi: {donation_reference}</p>
<p>Silakan selesaikan pembayaran melalui tautan berikut:
<br/>{pay_url}</p>
<p>— {organization_name}</p>
HTML
            ),
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
                        Select::make('validate_client_id')
                            ->label('Client ID untuk Validasi Nomor')
                            ->options(fn () => $this->accountOptions)
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText('Pilih client/nomor WA untuk validasi nomor donatur.'),
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
                Section::make('Pengiriman Pesan')
                    ->description('Atur pengiriman pesan WhatsApp otomatis dan templatenya')
                    ->schema([
                        Toggle::make('send_enabled')
                            ->label('Aktifkan Kirim WhatsApp Otomatis')
                            ->helperText('Jika aktif, sistem akan mengirim pesan WA ke donatur sesuai template.')
                            ->inline(false),
                        Select::make('send_client_id')
                            ->label('Client ID untuk Kirim Pesan')
                            ->options(fn () => $this->accountOptions)
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText('Pilih client/nomor WA yang digunakan untuk mengirim pesan.'),
                        RichEditor::make('message_template')
                            ->label('Template Pesan')
                            ->toolbarButtons([
                                'bold', 'italic', 'strike', 'underline', 'link', 'bulletList', 'orderedList', 'blockquote', 'codeBlock', 'h2', 'h3'
                            ])
                            ->helperText('Gunakan placeholder: {donor_name}, {donor_phone}, {donor_email}, {amount}, {amount_raw}, {campaign_title}, {campaign_url}, {pay_url}, {donation_reference}, {organization_name}')
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
            'send_enabled' => (bool)($state['send_enabled'] ?? false),
            'send_client_id' => $state['send_client_id'] ?? null,
            'message_template' => $state['message_template'] ?? null,
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
