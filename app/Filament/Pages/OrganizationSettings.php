<?php

namespace App\Filament\Pages;

use App\Models\Organization;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Page;

class OrganizationSettings extends Page implements HasForms
{
    use InteractsWithForms;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Manajemen';

    protected static ?string $navigationLabel = 'Organisasi';

    protected static ?int $navigationSort = 5; // paling atas di grup

    protected static ?string $slug = 'organization';

    protected static ?string $title = 'Pengaturan Organisasi';

    protected static string $view = 'filament.pages.organization-settings';

    public array $data = [];

    public ?Organization $org = null;

    public function mount(): void
    {
        $this->org = Organization::query()->first();

        $this->form->fill([
            'name' => $this->org->name ?? null,
            'slug' => $this->org->slug ?? 'default',
            'email' => $this->org->email ?? null,
            'phone' => $this->org->phone ?? null,
            'summary' => $this->org->summary ?? null,
            'commitment' => $this->org->commitment ?? null,
            'address' => $this->org->address ?? null,
            'lat' => $this->org->lat ?? null,
            'lng' => $this->org->lng ?? null,
            'logo_path' => $this->org->logo_path ?? null,
            'is_verified' => $this->org->is_verified ?? false,
            'meta_json' => isset($this->org->meta_json) ? json_encode($this->org->meta_json, JSON_PRETTY_PRINT) : null,
            'social' => $this->org?->social_json ?? [
                'website' => null,
                'instagram' => null,
                'facebook' => null,
                'youtube' => null,
                'tiktok' => null,
            ],
            // Homepage hero selections from meta_json
            'hero_campaign_ids' => collect($this->org?->meta_json['hero_campaign_ids'] ?? [])->filter()->values()->all(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Section::make('Profil Organisasi')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()
                            ->disk('s3')
                            ->directory('organization')
                            ->visibility('private')
                            ->imageEditor()
                            ->imageResizeMode('contain')
                            ->imageResizeTargetWidth('512')
                            ->imageResizeTargetHeight('512')
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->helperText('Digunakan untuk URL/identifikasi internal')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Textarea::make('summary')
                            ->label('Ringkasan')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('commitment')
                            ->label('Komitmen')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('lat')->label('Latitude')->numeric(),
                        TextInput::make('lng')->label('Longitude')->numeric(),
                        Fieldset::make('Sosial Media')
                            ->columns(2)
                            ->schema([
                                TextInput::make('social.website')->label('Website')->url()->maxLength(255),
                                TextInput::make('social.instagram')->label('Instagram')->maxLength(255),
                                TextInput::make('social.facebook')->label('Facebook')->maxLength(255),
                                TextInput::make('social.youtube')->label('YouTube')->maxLength(255),
                                TextInput::make('social.tiktok')->label('TikTok')->maxLength(255),
                            ])->columnSpanFull(),
                        Toggle::make('is_verified')
                            ->label('Terverifikasi')
                            ->inline(false),
                    ]),

                Section::make('Meta')
                    ->schema([
                        Textarea::make('meta_json')
                            ->rows(8)
                            ->helperText('Opsional. JSON bebas untuk metadata tambahan.')
                            ->placeholder('{\n  "instagram": "@brand",\n  "website": "https://example.com"\n}')
                            ->rule('nullable')
                            ->rule('json'),
                    ]),

                Section::make('Homepage')
                    ->columns(2)
                    ->schema([
                        Select::make('hero_campaign_ids')
                            ->label('Hero Campaigns')
                            ->helperText('Pilih hingga 5 campaign untuk slider beranda (urut sesuai pilihan). Jika kosong, akan otomatis tampil campaign aktif terbaru yang punya gambar.')
                            ->multiple()
                            ->maxItems(5)
                            ->options(\App\Models\Campaign::query()->orderBy('title')->pluck('title', 'id'))
                            ->searchable()
                            ->preload()
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

        $payload = [
            'name' => $state['name'] ?? null,
            'slug' => $state['slug'] ?? 'default',
            'email' => $state['email'] ?? null,
            'phone' => $state['phone'] ?? null,
            'summary' => $state['summary'] ?? null,
            'commitment' => $state['commitment'] ?? null,
            'address' => $state['address'] ?? null,
            'lat' => $state['lat'] ?? null,
            'lng' => $state['lng'] ?? null,
            'logo_path' => $state['logo_path'] ?? null,
            'is_verified' => (bool) ($state['is_verified'] ?? false),
        ];

        $meta = [];
        if (! empty($state['meta_json'])) {
            $decoded = json_decode($state['meta_json'], true);
            if (is_array($decoded)) {
                $meta = $decoded;
            }
        }
        if (isset($state['hero_campaign_ids']) && is_array($state['hero_campaign_ids'])) {
            $ids = array_values(array_filter($state['hero_campaign_ids']));
            if (!empty($ids)) {
                $meta['hero_campaign_ids'] = $ids;
            } else {
                unset($meta['hero_campaign_ids']);
            }
        }
        $payload['meta_json'] = !empty($meta) ? $meta : null;

        // Persist social media as JSON
        if (isset($state['social']) && is_array($state['social'])) {
            $payload['social_json'] = array_filter($state['social'], fn ($v) => filled($v));
        } else {
            $payload['social_json'] = null;
        }

        $org = $this->org ?? new Organization();
        $org->fill($payload);
        $org->save();

        $this->org = $org;

        $this->dispatch('notify',
            status: 'success',
            message: 'Pengaturan organisasi disimpan.'
        );
    }
}
