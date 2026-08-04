<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use App\Enums\VehicleCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('VehicleFormTabs')
                    ->tabs([
                        Tab::make('Informasi & Spesifikasi Armada')
                            ->icon('lucide-truck')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'lg' => 3,
                                ])->schema([
                                    Grid::make(1)
                                        ->schema([
                                            Section::make('Identitas & Klasifikasi')
                                                ->description('Kelola kode katalog, kategori, merek, model, dan tahun pembuatan armada.')
                                                ->icon('lucide-file-text')
                                                ->schema([
                                                    Grid::make(3)
                                                        ->schema([
                                                            TextInput::make('catalog_code')
                                                                ->label('Kode Katalog')
                                                                ->required()
                                                                ->unique(ignoreRecord: true)
                                                                ->maxLength(100)
                                                                ->placeholder('Contoh: VHC-ALPH-01')
                                                                ->prefixIcon('lucide-hash')
                                                                ->helperText('Kode unik identitas armada.'),
                                                            Select::make('category')
                                                                ->label('Kategori Kendaraan')
                                                                ->options(VehicleCategory::class)
                                                                ->required()
                                                                ->native(false)
                                                                ->prefixIcon('lucide-tag')
                                                                ->helperText('Pilih kategori armada.'),
                                                            TextInput::make('brand')
                                                                ->label('Merek Kendaraan')
                                                                ->placeholder('Contoh: Toyota')
                                                                ->maxLength(100)
                                                                ->prefixIcon('lucide-award')
                                                                ->helperText('Produsen / merek utama.'),
                                                        ]),
                                                    Grid::make(2)
                                                        ->schema([
                                                            TextInput::make('model')
                                                                ->label('Model / Seri')
                                                                ->placeholder('Contoh: Alphard Executive G')
                                                                ->maxLength(100)
                                                                ->prefixIcon('lucide-settings')
                                                                ->helperText('Varian atau seri kendaraan.'),
                                                            TextInput::make('year')
                                                                ->label('Tahun Pembuatan')
                                                                ->placeholder('Contoh: 2024')
                                                                ->numeric()
                                                                ->minValue(2000)
                                                                ->maxValue((int) date('Y') + 1)
                                                                ->prefixIcon('lucide-calendar')
                                                                ->helperText('Tahun perakitan armada.'),
                                                        ]),
                                                ]),

                                            Section::make('Detail Konten & Fitur Armada')
                                                ->description('Atur nama unit, slug URL, deskripsi lengkap, serta fitur opsional kendaraan.')
                                                ->icon('lucide-type')
                                                ->schema([
                                                    Translate::make()
                                                        ->locales(['id', 'en', 'ms'])
                                                        ->schema(fn (string $locale): array => [
                                                            Grid::make(2)
                                                                ->schema([
                                                                    TextInput::make('name')
                                                                        ->label('Nama Unit Kendaraan')
                                                                        ->placeholder('Contoh: Toyota Alphard Executive')
                                                                        ->required($locale === 'id')
                                                                        ->maxLength(255)
                                                                        ->live(onBlur: true)
                                                                        ->afterStateUpdated(function (?string $state, Set $set): void {
                                                                            $set('slug', Str::slug($state ?? ''));
                                                                        })
                                                                        ->prefixIcon('lucide-type')
                                                                        ->helperText('Nama lengkap armada di katalog.'),
                                                                    TextInput::make('slug')
                                                                        ->label('Slug URL')
                                                                        ->required($locale === 'id')
                                                                        ->placeholder('toyota-alphard-executive')
                                                                        ->maxLength(255)
                                                                        ->prefixIcon('lucide-link-2')
                                                                        ->helperText('Tautan URL. Terisi otomatis dari nama unit.'),
                                                                ]),
                                                            Textarea::make('description')
                                                                ->label('Deskripsi Kendaraan')
                                                                ->placeholder('Masukkan penjelasan rinci mengenai kenyamanan dan fasilitas armada...')
                                                                ->rows(4)
                                                                ->required($locale === 'id')
                                                                ->helperText('Penjelasan ringkas kondisi & keunggulan armada.')
                                                                ->columnSpanFull(),
                                                            TagsInput::make('features')
                                                                ->label('Fitur Tambahan')
                                                                ->placeholder('Ketik fitur (misal: Airbags, Audio JBL) lalu tekan enter')
                                                                ->helperText('Daftar kelengkapan fitur opsional.')
                                                                ->columnSpanFull(),
                                                            TextInput::make('capacity_label')
                                                                ->label('Label Kapasitas Khusus')
                                                                ->placeholder('Contoh: 14+1 seat')
                                                                ->helperText('Opsional, bila perlu format penjelasan kapasitas khusus.')
                                                                ->columnSpanFull(),
                                                        ]),
                                                ]),
                                        ])
                                        ->columnSpan([
                                            'default' => 1,
                                            'lg' => 2,
                                        ]),

                                    Grid::make(1)
                                        ->schema([
                                            Section::make('Spesifikasi Technical')
                                                ->description('Kapasitas penumpang, bagasi, transmisi, dan fasilitas pendukung.')
                                                ->icon('lucide-cog')
                                                ->schema([
                                                    TextInput::make('capacity_pax')
                                                        ->label('Kapasitas Penumpang')
                                                        ->placeholder('Contoh: 7')
                                                        ->numeric()
                                                        ->suffix('Orang')
                                                        ->prefixIcon('lucide-users')
                                                        ->helperText('Maksimal jumlah penumpang.'),
                                                    TextInput::make('capacity_luggage')
                                                        ->label('Kapasitas Bagasi')
                                                        ->placeholder('Contoh: 3')
                                                        ->numeric()
                                                        ->suffix('Koper')
                                                        ->default(0)
                                                        ->prefixIcon('lucide-briefcase')
                                                        ->helperText('Estimasi kapasitas koper.'),
                                                    Select::make('transmission')
                                                        ->label('Tipe Transmisi')
                                                        ->options([
                                                            'automatic' => 'Otomatis',
                                                            'manual' => 'Manual',
                                                        ])
                                                        ->placeholder('Pilih tipe transmisi')
                                                        ->prefixIcon('lucide-activity')
                                                        ->native(false)
                                                        ->helperText('Sistem transmisi kendaraan.'),
                                                    Grid::make(2)
                                                        ->schema([
                                                            Toggle::make('has_ac')
                                                                ->label('Dilengkapi AC')
                                                                ->default(true),
                                                            Toggle::make('has_wifi')
                                                                ->label('Dilengkapi WiFi')
                                                                ->default(false),
                                                        ]),
                                                ]),

                                            Section::make('Visibilitas Katalog')
                                                ->description('Status publikasi dan urutan tampilan kendaraan.')
                                                ->icon('lucide-settings')
                                                ->schema([
                                                    Toggle::make('is_active')
                                                        ->label('Tampilkan di Katalog')
                                                        ->default(true)
                                                        ->helperText('Status keaktifan unit di katalog publik.'),
                                                    Toggle::make('is_featured')
                                                        ->label('Armada Unggulan')
                                                        ->default(false)
                                                        ->helperText('Tampilkan sebagai rekomendasi armada unggulan.'),
                                                    TextInput::make('sort_order')
                                                        ->label('Urutan Katalog')
                                                        ->numeric()
                                                        ->minValue(0)
                                                        ->default(0)
                                                        ->prefixIcon('lucide-sort-asc')
                                                        ->helperText('Angka prioritas urutan tampilan.'),
                                                ]),
                                        ])
                                        ->columnSpan([
                                            'default' => 1,
                                            'lg' => 1,
                                        ]),
                                ]),
                            ]),

                        Tab::make('Media Visual Armada')
                            ->icon('lucide-image')
                            ->schema([
                                Section::make('Media Visual Kendaraan')
                                    ->description('Unggah foto sampul utama dan foto-foto galeri pendukung (interior & eksterior).')
                                    ->icon('lucide-images')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                SpatieMediaLibraryFileUpload::make('cover')
                                                    ->label('Foto Utama (Cover)')
                                                    ->collection('cover')
                                                    ->image()
                                                    ->visibility('public')
                                                    ->imageEditor()
                                                    ->columnSpan(1)
                                                    ->helperText('Foto cover utama (JPG, PNG, WebP rasio 4:3 maks 5MB).'),
                                                SpatieMediaLibraryFileUpload::make('gallery')
                                                    ->label('Galeri Foto Kendaraan')
                                                    ->collection('gallery')
                                                    ->multiple()
                                                    ->reorderable()
                                                    ->image()
                                                    ->visibility('public')
                                                    ->imageEditor()
                                                    ->columnSpan(1)
                                                    ->helperText('Foto interior & eksterior (JPG, PNG, WebP rasio 4:3 maks 5MB).'),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Tarif & Lembur')
                            ->icon('lucide-banknote')
                            ->schema([
                                Section::make('Pengaturan Tarif Lembur')
                                    ->description('Tarif harian dikelola per wilayah melalui tab Tarif Sewa setelah armada disimpan.')
                                    ->icon('lucide-clock')
                                    ->schema([
                                        TextInput::make('overtime_rate_idr')
                                            ->label('Tarif Lembur per Jam')
                                            ->numeric()
                                            ->minValue(0)
                                            ->prefix('Rp')
                                            ->prefixIcon('lucide-banknote')
                                            ->helperText('Biaya lembur per jam. Kosongkan bila harus konfirmasi admin.'),
                                    ]),
                            ]),
                    ])
                    ->persistTabInQueryString('tab')
                    ->columnSpanFull(),
            ]);
    }
}
