<?php

namespace App\Filament\Resources\Tours\Schemas;

use App\Models\Destination;
use App\Models\TourPackage;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class TourForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Informasi Tur')
                        ->description('Kategori, jenis destinasi, nama, dan deskripsi tur.')
                        ->icon('lucide-info')
                        ->schema([
                            Section::make('Informasi Dasar & Klasifikasi')
                                ->description('Atur kategori utama, jenis destinasi, mata uang dasar, dan detail penulisan nama serta deskripsi tur.')
                                ->icon('lucide-file-text')
                                ->schema([
                                    Grid::make(3)
                                        ->schema([
                                            Select::make('tour_category_id')
                                                ->label('Kategori Tur')
                                                ->relationship('category', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->placeholder('Pilih kategori tur')
                                                ->helperText('Pilih kategori yang paling sesuai dengan destinasi tur ini.')
                                                ->prefixIcon('lucide-tag')
                                                ->native(false),
                                            Select::make('tour_type')
                                                ->label('Tipe Destinasi')
                                                ->options([
                                                    'domestic' => 'Domestik',
                                                    'international' => 'Internasional',
                                                ])
                                                ->default('domestic')
                                                ->required()
                                                ->placeholder('Pilih tipe destinasi')
                                                ->helperText('Tentukan apakah perjalanan ini mencakup destinasi domestik atau internasional.')
                                                ->prefixIcon('lucide-globe')
                                                ->native(false),
                                            Select::make('currency')
                                                ->label('Mata Uang Utama')
                                                ->options(self::currencyOptions())
                                                ->default('IDR')
                                                ->required()
                                                ->placeholder('Pilih mata uang')
                                                ->helperText('Mata uang default yang digunakan untuk penentuan harga dasar tur.')
                                                ->prefixIcon('lucide-coins')
                                                ->native(false),
                                        ]),
                                    Translate::make()
                                        ->locales(['id', 'en', 'ms'])
                                        ->schema(fn (string $locale): array => [
                                            Grid::make(2)
                                                ->schema([
                                                    TextInput::make('name')
                                                        ->label('Nama Tur')
                                                        ->required($locale === 'id')
                                                        ->live(onBlur: true)
                                                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set(
                                                            "slug.{$locale}",
                                                            Str::slug($state ?? ''),
                                                        ))
                                                        ->maxLength(255)
                                                        ->placeholder('Masukkan nama tur (contoh: Pesona Keindahan Raja Ampat)')
                                                        ->helperText('Nama tur unik yang akan ditampilkan pada website.')
                                                        ->prefixIcon('lucide-type'),
                                                    TextInput::make('slug')
                                                        ->label('Slug URL')
                                                        ->required($locale === 'id')
                                                        ->maxLength(255)
                                                        ->placeholder('pesona-keindahan-raja-ampat')
                                                        ->helperText('Tautan URL halaman tur. Terisi otomatis dari nama tur jika dikosongkan.')
                                                        ->prefixIcon('lucide-link-2'),
                                                ]),
                                            Textarea::make('short_description')
                                                ->label('Deskripsi Singkat')
                                                ->rows(3)
                                                ->required($locale === 'id')
                                                ->columnSpanFull()
                                                ->placeholder('Masukkan ringkasan singkat daya tarik tur ini...')
                                                ->helperText('Ringkasan menarik 2-3 kalimat yang tampil di kartu daftar tur.'),
                                            RichEditor::make('description')
                                                ->label('Deskripsi Lengkap')
                                                ->required($locale === 'id')
                                                ->columnSpanFull()
                                                ->placeholder('Tuliskan detail lengkap perjalanan, daya tarik utama, dan informasi umum tur...')
                                                ->helperText('Deskripsi terperinci mengenai tur ini.'),
                                        ]),
                                ]),
                        ]),

                    Step::make('Paket Tur')
                        ->description('Atur durasi, media, itinerary, fasilitas, dan detail harga paket.')
                        ->icon('lucide-package')
                        ->schema([
                            Section::make('Kelola Paket Perjalanan')
                                ->description('Buat dan kelola satu atau beberapa pilihan paket perjalanan untuk tur ini.')
                                ->icon('lucide-layers')
                                ->schema([
                                    Repeater::make('packages')
                                        ->label('Pilihan Paket Tur')
                                        ->relationship()
                                        ->minItems(1)
                                        ->defaultItems(1)
                                        ->addActionLabel('Tambah Paket Baru')
                                        ->collapsible()
                                        ->cloneable()
                                        ->itemLabel(fn (array $state): ?string => $state['name']['id'] ?? $state['name']['en'] ?? 'Paket Baru')
                                        ->schema([
                                            Tabs::make('package_tabs')
                                                ->tabs([
                                                    Tab::make('Detail & Durasi')
                                                        ->icon('lucide-info')
                                                        ->schema([
                                                            Section::make('Detail Utama Paket')
                                                                ->description('Nama paket tur, tautan URL, dan durasi perjalanan.')
                                                                ->icon('lucide-file-text')
                                                                ->schema([
                                                                    Translate::make()
                                                                        ->locales(['id', 'en', 'ms'])
                                                                        ->schema(fn (string $locale): array => [
                                                                            Grid::make(2)
                                                                                ->schema([
                                                                                    TextInput::make('name')
                                                                                        ->label('Nama Paket')
                                                                                        ->required($locale === 'id')
                                                                                        ->live(onBlur: true)
                                                                                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set(
                                                                                            "slug.{$locale}",
                                                                                            Str::slug($state ?? ''),
                                                                                        ))
                                                                                        ->maxLength(255)
                                                                                        ->placeholder('Masukkan nama paket (contoh: Paket Hemat 4 Hari)')
                                                                                        ->helperText('Nama paket tur spesifik.')
                                                                                        ->prefixIcon('lucide-type'),
                                                                                    TextInput::make('slug')
                                                                                        ->label('Slug Paket')
                                                                                        ->required($locale === 'id')
                                                                                        ->maxLength(255)
                                                                                        ->placeholder('paket-hemat-4-hari')
                                                                                        ->helperText('Tautan URL khusus untuk paket ini. Terisi otomatis dari nama paket.')
                                                                                        ->prefixIcon('lucide-link-2'),
                                                                                ]),
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
                                                        ]),

                                                    Tab::make('Media')
                                                        ->icon('lucide-image')
                                                        ->schema([
                                                            Section::make('Media Visual Paket')
                                                                ->description('Unggah foto utama dan foto-foto galeri pendukung untuk paket tur ini.')
                                                                ->icon('lucide-images')
                                                                ->schema([
                                                                    SpatieMediaLibraryFileUpload::make('cover')
                                                                        ->label('Foto Utama (Cover)')
                                                                        ->collection(TourPackage::MEDIA_COLLECTION_COVER)
                                                                        ->image()
                                                                        ->imageEditor()
                                                                        ->visibility('public')
                                                                        ->columnSpanFull()
                                                                        ->helperText('Format: JPG, PNG, WebP (Rasio ideal 4:3, maks 5MB).'),
                                                                    SpatieMediaLibraryFileUpload::make('gallery')
                                                                        ->label('Galeri Foto Pendukung')
                                                                        ->collection(TourPackage::MEDIA_COLLECTION_GALLERY)
                                                                        ->image()
                                                                        ->multiple()
                                                                        ->reorderable()
                                                                        ->appendFiles()
                                                                        ->visibility('public')
                                                                        ->columnSpanFull()
                                                                        ->helperText('Format: JPG, PNG, WebP (Maks 5MB per file). Urutan foto bisa diseret (drag & drop).'),
                                                                ]),
                                                        ]),

                                                    Tab::make('Itinerary')
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
                                                                        ->cloneable()
                                                                        ->itemLabel(fn (array $state): ?string => isset($state['day_number']) ? 'Hari ke-'.$state['day_number'].': '.($state['title']['id'] ?? $state['title']['en'] ?? 'Tanpa Judul') : 'Hari Baru')
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
                                                                                ->locales(['id', 'en', 'ms'])
                                                                                ->schema(fn (string $locale): array => [
                                                                                    TextInput::make('title')
                                                                                        ->label('Judul Aktivitas')
                                                                                        ->required($locale === 'id')
                                                                                        ->maxLength(255)
                                                                                        ->placeholder('Masukkan judul aktivitas hari ini (contoh: Penjemputan & Check-in)')
                                                                                        ->helperText('Judul ringkas untuk aktivitas hari ini.')
                                                                                        ->prefixIcon('lucide-type'),
                                                                                    RichEditor::make('description')
                                                                                        ->label('Deskripsi Aktivitas')
                                                                                        ->required($locale === 'id')
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
                                                        ]),

                                                    Tab::make('Fasilitas')
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
                                                                        ->itemLabel(fn (array $state): ?string => isset($state['type']) ? ucfirst($state['type']).': '.($state['item']['id'] ?? $state['item']['en'] ?? 'Item Baru') : 'Item Baru')
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
                                                                                ->locales(['id', 'en', 'ms'])
                                                                                ->schema(fn (string $locale): array => [
                                                                                    TextInput::make('item')
                                                                                        ->label('Nama Item / Fasilitas')
                                                                                        ->required($locale === 'id')
                                                                                        ->maxLength(255)
                                                                                        ->placeholder('Masukkan nama item (contoh: Tiket Pesawat Pulang Pergi)')
                                                                                        ->helperText('Penjelasan singkat fasilitas/layanan.')
                                                                                        ->prefixIcon('lucide-check-square'),
                                                                                ])
                                                                                ->columnSpanFull(),
                                                                        ]),
                                                                ]),
                                                        ]),

                                                    Tab::make('Tiers & Harga')
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
                                                                        ->itemLabel(fn (array $state): ?string => ($state['name']['id'] ?? $state['name']['en'] ?? 'Tier Baru').(isset($state['hotel_stars']) ? " ({$state['hotel_stars']} Bintang)" : ''))
                                                                        ->schema([
                                                                            Translate::make()
                                                                                ->locales(['id', 'en', 'ms'])
                                                                                ->schema(fn (string $locale): array => [
                                                                                    TextInput::make('name')
                                                                                        ->label('Nama Tier')
                                                                                        ->required($locale === 'id')
                                                                                        ->maxLength(255)
                                                                                        ->placeholder('Masukkan nama tier (contoh: Gold, Silver, Deluxe)')
                                                                                        ->helperText('Nama tingkatan kualitas paket.')
                                                                                        ->prefixIcon('lucide-award'),
                                                                                ])
                                                                                ->columnSpanFull(),
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
                                                                                ->native(false),
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
                                                                                        ->options(self::currencyOptions())
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
                                                        ]),
                                                ]),
                                        ])
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    Step::make('Publikasi')
                        ->description('Atur status visibilitas dan penayangan paket tur pada website.')
                        ->icon('lucide-check-circle')
                        ->schema([
                            Section::make('Pengaturan Status Publikasi')
                                ->description('Tentukan apakah tur ini aktif untuk dipesan dan ditampilkan sebagai tur unggulan di halaman utama.')
                                ->icon('lucide-settings')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Toggle::make('is_active')
                                                ->label('Aktif (Dapat Dipesan & Diakses Publik)')
                                                ->default(true)
                                                ->required()
                                                ->helperText('Jika dinonaktifkan, tur tidak akan muncul di katalog publik.'),
                                            Toggle::make('is_featured')
                                                ->label('Tampilkan Sebagai Tur Unggulan')
                                                ->default(false)
                                                ->required()
                                                ->helperText('Menampilkan tur ini pada section rekomendasi utama/unggulan di homepage.'),
                                        ]),
                                ]),
                        ]),
                ])
                    ->columnSpanFull(),
            ]);
    }

    /** @return array<string, string> */
    private static function currencyOptions(): array
    {
        return collect(config('currencies.supported'))
            ->mapWithKeys(fn (array $metadata, string $code): array => [
                $code => "{$code} ({$metadata['symbol']})",
            ])
            ->all();
    }
}
