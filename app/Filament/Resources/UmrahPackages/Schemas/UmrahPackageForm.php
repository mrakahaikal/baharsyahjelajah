<?php

namespace App\Filament\Resources\UmrahPackages\Schemas;

use App\Enums\UmrahPackageType;
use App\Models\Country;
use App\Models\Destination;
use App\Models\UmrahPackage;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class UmrahPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('UmrahPackageFormTabs')
                    ->tabs([
                        Tab::make('Informasi & Program Umrah')
                            ->icon('lucide-info')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'lg' => 3,
                                ])->schema([
                                    Grid::make(1)
                                        ->schema([
                                            Section::make('Informasi Dasar & Klasifikasi')
                                                ->description('Atur klasifikasi jenis paket, durasi perjalanan, serta detail translasi nama dan penjelasan paket.')
                                                ->icon('lucide-file-text')
                                                ->schema([
                                                    Grid::make(3)
                                                        ->schema([
                                                            Select::make('package_type')
                                                                ->label('Tipe Paket Umrah')
                                                                ->options(UmrahPackageType::class)
                                                                ->required()
                                                                ->placeholder('Pilih tipe paket')
                                                                ->helperText('Klasifikasi jenis paket umrah.')
                                                                ->prefixIcon('lucide-tag')
                                                                ->native(false),
                                                            TextInput::make('duration_days')
                                                                ->label('Durasi Perjalanan')
                                                                ->numeric()
                                                                ->minValue(1)
                                                                ->suffix('Hari')
                                                                ->required()
                                                                ->placeholder('Contoh: 9')
                                                                ->helperText('Total jumlah hari perjalanan.')
                                                                ->prefixIcon('lucide-calendar'),
                                                            TextInput::make('price_idr')
                                                                ->label('Harga Dasar Fallback')
                                                                ->numeric()
                                                                ->minValue(1)
                                                                ->prefix('Rp')
                                                                ->required()
                                                                ->placeholder('Contoh: 28500000')
                                                                ->helperText('Harga dasar acuan sebelum tipe kamar.')
                                                                ->prefixIcon('lucide-banknote'),
                                                        ]),
                                                    Translate::make()
                                                        ->locales(['id', 'en', 'ms'])
                                                        ->schema(fn (string $locale): array => [
                                                            Grid::make(2)
                                                                ->schema([
                                                                    TextInput::make('name')
                                                                        ->label('Nama Paket')
                                                                        ->required($locale === 'id')
                                                                        ->maxLength(255)
                                                                        ->placeholder('Contoh: Umrah Reguler Awal Musim')
                                                                        ->helperText('Nama paket umrah unik.')
                                                                        ->prefixIcon('lucide-type'),
                                                                    TextInput::make('slug')
                                                                        ->label('Slug URL')
                                                                        ->required($locale === 'id')
                                                                        ->maxLength(255)
                                                                        ->placeholder('umrah-reguler-awal-musim')
                                                                        ->helperText('Tautan URL halaman detail.')
                                                                        ->prefixIcon('lucide-link-2'),
                                                                ]),
                                                            RichEditor::make('description')
                                                                ->label('Deskripsi Paket')
                                                                ->required($locale === 'id')
                                                                ->columnSpanFull()
                                                                ->placeholder('Tuliskan detail program, kelebihan paket, dan rincian lengkap...')
                                                                ->helperText('Deskripsi lengkap paket umrah.'),
                                                        ])
                                                        ->columnSpanFull(),
                                                    Select::make('countries')
                                                        ->label('Negara Tujuan')
                                                        ->relationship('countries', 'name')
                                                        ->getOptionLabelFromRecordUsing(fn (Country $record): string => $record->name)
                                                        ->multiple()
                                                        ->searchable()
                                                        ->preload()
                                                        ->native(false)
                                                        ->helperText('Pilih satu atau lebih negara tujuan paket umrah.')
                                                        ->prefixIcon('lucide-globe')
                                                        ->columnSpanFull(),
                                                    Select::make('destinations')
                                                        ->label('Destinasi & Ziarah Terkait')
                                                        ->relationship('destinations', 'name')
                                                        ->getOptionLabelFromRecordUsing(fn (Destination $record): string => $record->name)
                                                        ->multiple()
                                                        ->searchable()
                                                        ->preload()
                                                        ->native(false)
                                                        ->helperText('Paket akan tampil pada halaman destinasi yang dipilih.')
                                                        ->prefixIcon('lucide-map-pin')
                                                        ->columnSpanFull(),
                                                ]),
                                        ])
                                        ->columnSpan([
                                            'default' => 1,
                                            'lg' => 2,
                                        ]),

                                    Grid::make(1)
                                        ->schema([
                                            Section::make('Status & Visibilitas')
                                                ->description('Status penayangan dan rekomendasi di homepage.')
                                                ->icon('lucide-settings')
                                                ->schema([
                                                    Toggle::make('is_active')
                                                        ->label('Aktif (Tampil di Katalog)')
                                                        ->default(true)
                                                        ->helperText('Jika dinonaktifkan, paket tidak akan muncul di website.'),
                                                    Toggle::make('is_featured')
                                                        ->label('Tampilkan Sebagai Paket Unggulan')
                                                        ->default(false)
                                                        ->helperText('Menampilkan paket di rekomendasi utama homepage.'),
                                                ]),
                                        ])
                                        ->columnSpan([
                                            'default' => 1,
                                            'lg' => 1,
                                        ]),
                                ]),
                            ]),

                        Tab::make('Akomodasi & Transportasi')
                            ->icon('lucide-hotel')
                            ->schema([
                                Section::make('Maskapai & Penginapan Hotel')
                                    ->description('Atur maskapai penerbangan serta akomodasi hotel di Makkah dan Madinah.')
                                    ->icon('lucide-briefcase')
                                    ->schema([
                                        TextInput::make('airline')
                                            ->label('Maskapai Penerbangan')
                                            ->maxLength(100)
                                            ->placeholder('Contoh: Saudia Airlines, Garuda Indonesia')
                                            ->helperText('Nama maskapai penerbangan utama.')
                                            ->prefixIcon('lucide-plane'),
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('hotel_makkah')
                                                    ->label('Hotel Makkah')
                                                    ->maxLength(255)
                                                    ->placeholder('Contoh: Hilton Suites Makkah')
                                                    ->helperText('Nama hotel di Makkah.')
                                                    ->prefixIcon('lucide-building-2'),
                                                Select::make('hotel_makkah_stars')
                                                    ->label('Bintang Hotel Makkah')
                                                    ->options([
                                                        3 => '3 Bintang',
                                                        4 => '4 Bintang',
                                                        5 => '5 Bintang',
                                                    ])
                                                    ->placeholder('Pilih bintang hotel')
                                                    ->helperText('Kelas hotel di Makkah.')
                                                    ->prefixIcon('lucide-star')
                                                    ->native(false),
                                                TextInput::make('hotel_madinah')
                                                    ->label('Hotel Madinah')
                                                    ->maxLength(255)
                                                    ->placeholder('Contoh: Pullman Zamzam Madinah')
                                                    ->helperText('Nama hotel di Madinah.')
                                                    ->prefixIcon('lucide-building-2'),
                                                Select::make('hotel_madinah_stars')
                                                    ->label('Bintang Hotel Madinah')
                                                    ->options([
                                                        3 => '3 Bintang',
                                                        4 => '4 Bintang',
                                                        5 => '5 Bintang',
                                                    ])
                                                    ->placeholder('Pilih bintang hotel')
                                                    ->helperText('Kelas hotel di Madinah.')
                                                    ->prefixIcon('lucide-star')
                                                    ->native(false),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                Toggle::make('visa_included')
                                                    ->label('Termasuk Visa Umrah')
                                                    ->default(true)
                                                    ->helperText('Harga paket sudah termasuk pengurusan visa.'),
                                                Toggle::make('handling_included')
                                                    ->label('Termasuk Handling & Perlengkapan')
                                                    ->default(true)
                                                    ->helperText('Harga paket sudah termasuk handling bandara & perlengkapan.'),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Media Visual')
                            ->icon('lucide-image')
                            ->schema([
                                Section::make('Media Visual Paket')
                                    ->description('Unggah foto utama dan foto-foto galeri pendukung paket umrah.')
                                    ->icon('lucide-images')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                SpatieMediaLibraryFileUpload::make('cover')
                                                    ->label('Foto Utama (Cover)')
                                                    ->collection(UmrahPackage::MEDIA_COLLECTION_COVER)
                                                    ->image()
                                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                                    ->maxSize(5120)
                                                    ->imageEditor()
                                                    ->disk('public')
                                                    ->visibility('public')
                                                    ->columnSpan(1)
                                                    ->helperText('Format: JPG, PNG, WebP (Rasio ideal 4:3, maks 5MB).'),
                                                SpatieMediaLibraryFileUpload::make('gallery')
                                                    ->label('Galeri Foto Pendukung')
                                                    ->collection(UmrahPackage::MEDIA_COLLECTION_GALLERY)
                                                    ->image()
                                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                                    ->maxSize(5120)
                                                    ->multiple()
                                                    ->reorderable()
                                                    ->appendFiles()
                                                    ->imageEditor()
                                                    ->disk('public')
                                                    ->visibility('public')
                                                    ->columnSpan(1)
                                                    ->helperText('Format: JPG, PNG, WebP (Maks 5MB per file). Urutan foto bisa diseret.'),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->persistTabInQueryString('tab')
                    ->columnSpanFull(),
            ]);
    }
}
