<?php

namespace App\Filament\Resources\Tours\Schemas\Partials;

use App\Filament\Support\CurrencyOptions;
use App\Filament\Support\FilamentLocale;
use App\Filament\Support\Forms\Components\TranslatableNameSlug;
use App\Filament\Support\Forms\Presets\MediaUploadPreset;
use App\Models\Destination;
use App\Models\TourPackage;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class TourPackagesTab
{
    public static function make(): Tab
    {
        return Tab::make('Paket Perjalanan & Harga')
            ->icon('lucide-package')
            ->schema([
                Section::make('Kelola Pilihan Paket Perjalanan')
                    ->description('Buat dan kelola satu atau beberapa pilihan paket perjalanan untuk tur ini.')
                    ->icon('lucide-layers')
                    ->schema([
                        self::packageRepeater(),
                    ]),
            ]);
    }

    public static function packageRepeater(): Repeater
    {
        return Repeater::make('packages')
            ->label('Pilihan Paket Tur')
            ->relationship()
            ->minItems(1)
            ->defaultItems(1)
            ->addActionLabel('Tambah Paket Baru')
            ->collapsible()
            ->cloneable()
            ->itemLabel(function (array $state): ?string {
                $name = $state['name']['id'] ?? $state['name']['en'] ?? 'Paket Baru';
                $days = isset($state['duration_days']) ? $state['duration_days'].' Hari' : '';
                $nights = isset($state['duration_nights']) ? $state['duration_nights'].' Malam' : '';
                $duration = ($days || $nights) ? trim("{$days} {$nights}") : '';

                return $duration ? "{$name} ({$duration})" : $name;
            })
            ->schema([
                Tabs::make('package_tabs')
                    ->tabs([
                        self::detailAndDurationTab(),
                        self::mediaTab(),
                        self::itineraryTab(),
                        self::facilitiesTab(),
                        self::tiersAndPricingTab(),
                    ]),
            ])
            ->columnSpanFull();
    }

    protected static function detailAndDurationTab(): Tab
    {
        return Tab::make('Detail & Durasi')
            ->icon('lucide-info')
            ->schema([
                Section::make('Detail Utama Paket')
                    ->description('Nama paket tur, tautan URL, dan durasi perjalanan.')
                    ->icon('lucide-file-text')
                    ->schema([
                        Translate::make()
                            ->locales(FilamentLocale::locales())
                            ->schema(fn (string $locale): array => [
                                TranslatableNameSlug::make(
                                    locale: $locale,
                                    nameLabel: 'Nama Paket',
                                    slugLabel: 'Slug Paket',
                                    namePlaceholder: 'Contoh: Paket Hemat 4 Hari',
                                    slugPlaceholder: 'paket-hemat-4-hari',
                                    nameHelperText: 'Nama paket tur spesifik.',
                                    slugHelperText: 'Tautan URL khusus untuk paket ini. Terisi otomatis dari nama paket.',
                                ),
                            ])
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('duration_days')
                                    ->label('Durasi Hari')
                                    ->numeric()
                                    ->minValue(1)
                                    ->suffix('Hari')
                                    ->required()
                                    ->placeholder('Contoh: 4')
                                    ->helperText('Total jumlah hari perjalanan.')
                                    ->prefixIcon('lucide-sun'),
                                TextInput::make('duration_nights')
                                    ->label('Durasi Malam')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->suffix('Malam')
                                    ->required()
                                    ->placeholder('Contoh: 3')
                                    ->helperText('Total jumlah malam menginap.')
                                    ->prefixIcon('lucide-moon'),
                            ]),
                    ]),
            ]);
    }

    protected static function mediaTab(): Tab
    {
        return Tab::make('Media')
            ->icon('lucide-image')
            ->schema([
                Section::make('Media Visual Paket')
                    ->description('Unggah foto utama dan foto-foto galeri pendukung untuk paket tur ini.')
                    ->icon('lucide-images')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                MediaUploadPreset::spatieImage(
                                    name: 'cover',
                                    collection: TourPackage::MEDIA_COLLECTION_COVER,
                                    label: 'Foto Utama (Cover)',
                                    helperText: 'Format: JPG, PNG, WebP (Rasio ideal 4:3, maks 5MB).'
                                )->columnSpan(1),
                                MediaUploadPreset::spatieGallery(
                                    name: 'gallery',
                                    collection: TourPackage::MEDIA_COLLECTION_GALLERY,
                                    label: 'Galeri Foto Pendukung',
                                    helperText: 'Format: JPG, PNG, WebP (Maks 5MB per file). Urutan foto bisa diseret (drag & drop).'
                                )->columnSpan(1),
                            ]),
                    ]),
            ]);
    }

    protected static function itineraryTab(): Tab
    {
        return Tab::make('Itinerary')
            ->icon('lucide-route')
            ->schema([
                Section::make('Rencana Perjalanan Harian')
                    ->description('Kelola aktivitas harian dan destinasi yang dikunjungi sepanjang durasi tur.')
                    ->icon('lucide-map')
                    ->schema([
                        Repeater::make('itineraries')
                            ->label('Daftar Rencana Perjalanan')
                            ->relationship()
                            ->orderColumn('day_number')
                            ->addActionLabel('Tambah Hari Baru')
                            ->collapsible()
                            ->collapsed()
                            ->cloneable()
                            ->itemLabel(function (array $state): ?string {
                                $day = $state['day_number'] ?? '?';
                                $title = $state['title']['id'] ?? $state['title']['en'] ?? 'Tanpa Judul';

                                return "Hari ke-{$day}: {$title}";
                            })
                            ->schema([
                                TextInput::make('day_number')
                                    ->label('Hari Ke-')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required()
                                    ->placeholder('Contoh: 1')
                                    ->helperText('Nomor urutan hari.')
                                    ->prefixIcon('lucide-hash'),
                                Translate::make()
                                    ->locales(FilamentLocale::locales())
                                    ->schema(fn (string $locale): array => [
                                        TextInput::make('title')
                                            ->label('Judul Aktivitas')
                                            ->required($locale === FilamentLocale::primary())
                                            ->maxLength(255)
                                            ->placeholder('Masukkan judul aktivitas hari ini (contoh: Penjemputan & Check-in)')
                                            ->helperText('Judul ringkas untuk aktivitas hari ini.')
                                            ->prefixIcon('lucide-type'),
                                        RichEditor::make('description')
                                            ->label('Deskripsi Aktivitas')
                                            ->required($locale === FilamentLocale::primary())
                                            ->placeholder('Tuliskan detail aktivitas, tempat makan, dan jadwal hari ini...')
                                            ->helperText('Detail lengkap jadwal dan agenda perjalanan hari ini.'),
                                    ])
                                    ->columnSpanFull(),
                                Select::make('destinations')
                                    ->label('Destinasi yang Dikunjungi')
                                    ->relationship('destinations', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->getOptionLabelFromRecordUsing(fn (Destination $record): string => $record->name)
                                    ->native(false)
                                    ->helperText('Pilih satu atau beberapa destinasi wisata dari database yang akan dikunjungi pada hari ini.')
                                    ->prefixIcon('lucide-map-pin')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    protected static function facilitiesTab(): Tab
    {
        return Tab::make('Fasilitas')
            ->icon('lucide-list-checks')
            ->schema([
                Section::make('Fasilitas & Layanan Terkait')
                    ->description('Tentukan apa saja yang termasuk, tidak termasuk, atau catatan khusus untuk paket ini.')
                    ->icon('lucide-clipboard-check')
                    ->schema([
                        Repeater::make('includes')
                            ->label('Daftar Layanan & Fasilitas')
                            ->relationship()
                            ->orderColumn('sort_order')
                            ->addActionLabel('Tambah Item Baru')
                            ->cloneable()
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(function (array $state): ?string {
                                $types = [
                                    'include' => 'Termasuk',
                                    'exclude' => 'Tidak Termasuk',
                                    'note' => 'Catatan',
                                ];
                                $typeLabel = $types[$state['type'] ?? ''] ?? 'Item';
                                $itemLabel = $state['item']['id'] ?? $state['item']['en'] ?? 'Baru';

                                return "[{$typeLabel}] {$itemLabel}";
                            })
                            ->columns(2)
                            ->schema([
                                Select::make('type')
                                    ->label('Jenis Layanan')
                                    ->options([
                                        'include' => 'Termasuk',
                                        'exclude' => 'Tidak Termasuk',
                                        'note' => 'Catatan',
                                    ])
                                    ->default('include')
                                    ->required()
                                    ->placeholder('Pilih jenis')
                                    ->helperText('Kategori item (Fasilitas, Pengecualian, atau Catatan).')
                                    ->prefixIcon('lucide-help-circle')
                                    ->native(false),
                                TextInput::make('sort_order')
                                    ->label('Urutan Tampilan')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required()
                                    ->placeholder('Contoh: 0')
                                    ->helperText('Angka urutan tampilan pada halaman detail.')
                                    ->prefixIcon('lucide-sort-asc'),
                                Translate::make()
                                    ->locales(FilamentLocale::locales())
                                    ->schema(fn (string $locale): array => [
                                        TextInput::make('item')
                                            ->label('Nama Item / Fasilitas')
                                            ->required($locale === FilamentLocale::primary())
                                            ->maxLength(255)
                                            ->placeholder('Masukkan nama item (contoh: Tiket Pesawat Pulang Pergi)')
                                            ->helperText('Penjelasan singkat fasilitas/layanan.')
                                            ->prefixIcon('lucide-check-square'),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    protected static function tiersAndPricingTab(): Tab
    {
        return Tab::make('Tiers & Harga')
            ->icon('lucide-banknote')
            ->schema([
                Section::make('Tiering Kualitas & Harga Peserta')
                    ->description('Kelola tingkatan fasilitas (seperti bintang hotel) beserta struktur harga berdasarkan jumlah pax.')
                    ->icon('lucide-trending-up')
                    ->schema([
                        Repeater::make('tiers')
                            ->label('Daftar Tier Paket')
                            ->relationship()
                            ->minItems(1)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Tier Baru')
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(function (array $state): ?string {
                                $name = $state['name']['id'] ?? $state['name']['en'] ?? 'Tier Baru';
                                $stars = isset($state['hotel_stars']) ? " ({$state['hotel_stars']} Bintang)" : '';

                                return "{$name}{$stars}";
                            })
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Translate::make()
                                            ->locales(FilamentLocale::locales())
                                            ->schema(fn (string $locale): array => [
                                                TextInput::make('name')
                                                    ->label('Nama Tier')
                                                    ->required($locale === FilamentLocale::primary())
                                                    ->maxLength(255)
                                                    ->placeholder('Masukkan nama tier (contoh: Gold, Silver, Deluxe)')
                                                    ->helperText('Nama tingkatan kualitas paket.')
                                                    ->prefixIcon('lucide-award'),
                                            ])
                                            ->columnSpan(1),
                                        Select::make('hotel_stars')
                                            ->label('Bintang Hotel')
                                            ->options([
                                                1 => '1 Bintang',
                                                2 => '2 Bintang',
                                                3 => '3 Bintang',
                                                4 => '4 Bintang',
                                                5 => '5 Bintang',
                                            ])
                                            ->placeholder('Pilih kelas hotel')
                                            ->helperText('Klasifikasi standar hotel yang digunakan pada tier ini.')
                                            ->prefixIcon('lucide-star')
                                            ->native(false)
                                            ->columnSpan(1),
                                    ]),
                                Repeater::make('priceTiers')
                                    ->label('Daftar Harga Per Pax')
                                    ->relationship()
                                    ->minItems(1)
                                    ->defaultItems(1)
                                    ->addActionLabel('Tambah Aturan Harga')
                                    ->cloneable()
                                    ->columns(4)
                                    ->schema([
                                        TextInput::make('min_pax')
                                            ->label('Min Peserta (Pax)')
                                            ->numeric()
                                            ->minValue(1)
                                            ->required()
                                            ->placeholder('Contoh: 1')
                                            ->helperText('Batas minimal jumlah peserta.')
                                            ->prefixIcon('lucide-user'),
                                        TextInput::make('max_pax')
                                            ->label('Max Peserta (Pax)')
                                            ->numeric()
                                            ->minValue(1)
                                            ->placeholder('Contoh: 5')
                                            ->helperText('Batas maksimal (kosongkan jika tidak berbatas).')
                                            ->prefixIcon('lucide-users'),
                                        TextInput::make('price')
                                            ->label('Harga per Pax')
                                            ->numeric()
                                            ->minValue(0)
                                            ->required()
                                            ->placeholder('Contoh: 1500000')
                                            ->helperText('Tarif nominal per orang.')
                                            ->prefixIcon('lucide-banknote'),
                                        Select::make('currency')
                                            ->label('Mata Uang')
                                            ->options(CurrencyOptions::selectOptions())
                                            ->default('IDR')
                                            ->required()
                                            ->placeholder('Pilih mata uang')
                                            ->helperText('Mata uang untuk nominal harga.')
                                            ->prefixIcon('lucide-coins')
                                            ->native(false),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}

