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
                Section::make('Informasi Kendaraan')
                    ->description('Kelola detail identitas, merek, tipe, deskripsi, dan fitur unik kendaraan.')
                    ->icon('lucide-truck')
                    ->columnSpanFull()
                    ->schema([
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
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
                                    ->helperText('Nama lengkap tipe armada kendaraan.'),
                                TextInput::make('slug')
                                    ->label('Slug URL')
                                    ->required($locale === 'id')
                                    ->placeholder('toyota-alphard-executive')
                                    ->maxLength(255)
                                    ->prefixIcon('lucide-link-2')
                                    ->helperText('Tautan URL kendaraan. Terisi otomatis dari nama.'),
                                Textarea::make('description')
                                    ->label('Deskripsi Kendaraan')
                                    ->placeholder('Masukkan penjelasan rinci mengenai armada kendaraan...')
                                    ->rows(4)
                                    ->required($locale === 'id')
                                    ->helperText('Deskripsi lengkap kondisi, kenyamanan, dan keunggulan armada.')
                                    ->columnSpanFull(),
                                TagsInput::make('features')
                                    ->label('Fitur Tambahan')
                                    ->placeholder('Ketik fitur (misal: Airbags, JBL Audio) lalu tekan enter')
                                    ->helperText('Daftar kelengkapan fitur opsional kendaraan.')
                                    ->columnSpanFull(),
                                TextInput::make('capacity_label')
                                    ->label('Label Kapasitas')
                                    ->placeholder('Contoh: 14+1 seat')
                                    ->helperText('Opsional, digunakan bila format kapasitas perlu penjelasan khusus.'),
                            ]),
                        Grid::make(4)->schema([
                            TextInput::make('catalog_code')
                                ->label('Kode Katalog')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(100),
                            Select::make('category')
                                ->label('Kategori')
                                ->options(VehicleCategory::class)
                                ->required()
                                ->native(false),
                            TextInput::make('brand')
                                ->label('Merek')
                                ->placeholder('Contoh: Toyota')
                                ->maxLength(100)
                                ->prefixIcon('lucide-tag'),
                            TextInput::make('model')
                                ->label('Model / Seri')
                                ->placeholder('Contoh: Alphard G')
                                ->maxLength(100)
                                ->prefixIcon('lucide-settings'),
                            TextInput::make('year')
                                ->label('Tahun Pembuatan')
                                ->placeholder('Contoh: 2024')
                                ->numeric()
                                ->minValue(2000)
                                ->maxValue((int) date('Y') + 1)
                                ->prefixIcon('lucide-calendar')
                                ->helperText('Tahun pembuatan armada.'),
                        ]),
                    ]),

                Section::make('Spesifikasi & Fasilitas')
                    ->description('Tentukan kapasitas penumpang, bagasi, transmisi, dan fitur kenyamanan.')
                    ->icon('lucide-cog')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
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
                                ->helperText('Estimasi kapasitas koper besar di bagasi.'),
                            Select::make('transmission')
                                ->label('Tipe Transmisi')
                                ->options([
                                    'automatic' => 'Otomatis',
                                    'manual' => 'Manual',
                                ])
                                ->placeholder('Tidak ditentukan')
                                ->prefixIcon('lucide-activity')
                                ->native(false)
                                ->helperText('Pilih sistem transmisi kendaraan.'),
                        ]),
                        Grid::make(2)->schema([
                            Toggle::make('has_ac')
                                ->label('Dilengkapi AC')
                                ->default(true)
                                ->inline(false),
                            Toggle::make('has_wifi')
                                ->label('Dilengkapi WiFi')
                                ->default(false)
                                ->inline(false),
                        ]),
                    ]),

                Section::make('Tarif, Media & Status')
                    ->description('Tarif utama dikelola per wilayah melalui tab Tarif Sewa setelah kendaraan disimpan.')
                    ->icon('lucide-banknote')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('overtime_rate_idr')
                            ->label('Tarif Lembur per Jam')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->helperText('Kosongkan bila lembur harus dikonfirmasi kepada admin.'),
                        SpatieMediaLibraryFileUpload::make('cover')
                            ->label('Foto Utama Kendaraan')
                            ->collection('cover')
                            ->image()
                            ->visibility('public')
                            ->imageEditor()
                            ->helperText('Foto cover utama kendaraan (JPG, PNG, WebP rasio 4:3 maks 5MB).')
                            ->columnSpanFull(),
                        SpatieMediaLibraryFileUpload::make('gallery')
                            ->label('Galeri Foto Kendaraan')
                            ->collection('gallery')
                            ->multiple()
                            ->reorderable()
                            ->image()
                            ->visibility('public')
                            ->imageEditor()
                            ->helperText('Kumpulan foto interior & eksterior kendaraan (JPG, PNG, WebP rasio 4:3 maks 5MB).')
                            ->columnSpanFull(),
                        Grid::make(3)->schema([
                            Toggle::make('is_active')
                                ->label('Tampilkan di Katalog')
                                ->default(true)
                                ->inline(false),
                            Toggle::make('is_featured')
                                ->label('Armada Unggulan')
                                ->default(false)
                                ->inline(false),
                            TextInput::make('sort_order')
                                ->label('Urutan Katalog')
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
                        ]),
                    ]),
            ]);
    }
}
