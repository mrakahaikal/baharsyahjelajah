<?php

namespace App\Filament\Resources\UmrahPackages\Schemas;

use App\Enums\UmrahPackageType;
use App\Models\Destination;
use App\Models\UmrahPackage;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class UmrahPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Informasi Paket')
                        ->description('Identitas paket, tipe, durasi, dan penjelasan program.')
                        ->icon('lucide-info')
                        ->schema([
                            Section::make('Informasi Dasar Paket Umrah')
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
                                                ->helperText('Klasifikasi jenis paket umrah (Regular, Plus, atau Ramadan).')
                                                ->prefixIcon('lucide-tag')
                                                ->native(false),
                                            TextInput::make('duration_days')
                                                ->label('Durasi Perjalanan')
                                                ->numeric()
                                                ->minValue(1)
                                                ->suffix('Hari')
                                                ->required()
                                                ->placeholder('Contoh: 9')
                                                ->helperText('Total jumlah hari perjalanan umrah.')
                                                ->prefixIcon('lucide-calendar'),
                                            TextInput::make('price_idr')
                                                ->label('Harga Dasar Fallback')
                                                ->numeric()
                                                ->minValue(1)
                                                ->prefix('Rp')
                                                ->required()
                                                ->placeholder('Contoh: 28500000')
                                                ->helperText('Harga dasar acuan sebelum detail tipe kamar ditambahkan.')
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
                                                        ->placeholder('Masukkan nama paket (contoh: Umrah Reguler Awal Musim)')
                                                        ->helperText('Nama paket umrah unik yang tampil di website.')
                                                        ->prefixIcon('lucide-type'),
                                                    TextInput::make('slug')
                                                        ->label('Slug URL')
                                                        ->required($locale === 'id')
                                                        ->maxLength(255)
                                                        ->placeholder('umrah-reguler-awal-musim')
                                                        ->helperText('Tautan URL halaman detail. Terisi otomatis dari nama paket.')
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
                                    Select::make('destinations')
                                        ->label('Destinasi Terkait')
                                        ->relationship('destinations', 'name')
                                        ->getOptionLabelFromRecordUsing(fn (Destination $record): string => $record->name)
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->helperText('Paket akan tampil pada halaman destinasi yang dipilih.')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    Step::make('Akomodasi')
                        ->description('Maskapai penerbangan, hotel penginapan, dan kelengkapan visa.')
                        ->icon('lucide-hotel')
                        ->schema([
                            Section::make('Fasilitas Transportasi & Hotel')
                                ->description('Atur maskapai penerbangan, akomodasi hotel di Makkah dan Madinah, serta ketersediaan visa dan handling.')
                                ->icon('lucide-briefcase')
                                ->schema([
                                    TextInput::make('airline')
                                        ->label('Maskapai Penerbangan')
                                        ->maxLength(100)
                                        ->placeholder('Masukkan nama maskapai (contoh: Saudia, Garuda Indonesia)')
                                        ->helperText('Nama maskapai penerbangan utama yang digunakan.')
                                        ->prefixIcon('lucide-plane'),
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('hotel_makkah')
                                                ->label('Hotel Makkah')
                                                ->maxLength(255)
                                                ->placeholder('Contoh: Hilton Suites Makkah')
                                                ->helperText('Nama hotel tempat menginap selama berada di Makkah.')
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
                                                ->helperText('Nama hotel tempat menginap selama berada di Madinah.')
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
                                                ->helperText('Aktifkan jika harga paket sudah termasuk pengurusan visa.'),
                                            Toggle::make('handling_included')
                                                ->label('Termasuk Handling & Perlengkapan')
                                                ->default(true)
                                                ->helperText('Aktifkan jika harga paket sudah mencakup handling bandara dan perlengkapan.'),
                                        ]),
                                ]),
                        ]),

                    Step::make('Media')
                        ->description('Cover utama dan galeri foto pendukung paket.')
                        ->icon('lucide-image')
                        ->schema([
                            Section::make('Media Visual Paket')
                                ->description('Unggah foto utama dan foto-foto galeri pendukung untuk paket umrah ini.')
                                ->icon('lucide-images')
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
                                        ->helperText('Format: JPG, PNG, WebP (Maks 5MB per file). Urutan foto bisa diseret (drag & drop).'),
                                ]),
                        ]),

                    Step::make('Publikasi')
                        ->description('Visibilitas dan status penayangan di website.')
                        ->icon('lucide-check-circle')
                        ->schema([
                            Section::make('Pengaturan Status Publikasi')
                                ->description('Tentukan status penayangan paket umrah dan setel sebagai paket unggulan di halaman utama.')
                                ->icon('lucide-settings')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Toggle::make('is_active')
                                                ->label('Aktif dan Dapat Diakses Publik')
                                                ->default(true)
                                                ->helperText('Jika dinonaktifkan, paket tidak akan muncul di katalog website.'),
                                            Toggle::make('is_featured')
                                                ->label('Tampilkan Sebagai Paket Unggulan')
                                                ->default(false)
                                                ->helperText('Menampilkan paket ini pada section rekomendasi utama di homepage.'),
                                        ]),
                                ]),
                        ]),
                ])
                    ->columnSpanFull(),
            ]);
    }
}
