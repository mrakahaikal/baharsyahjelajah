<?php

namespace App\Filament\Resources\Banners;

use App\Enums\BannerCtaType;
use App\Enums\BannerPlacement;
use App\Filament\Resources\Banners\Pages\ManageBanners;
use App\Models\Banner;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|BackedEnum|null $navigationIcon = 'lucide-images';

    protected static string|null|\UnitEnum $navigationGroup = 'Konten Website';

    protected static ?string $navigationLabel = 'Banner';

    protected static ?string $modelLabel = 'Banner';

    protected static ?string $pluralModelLabel = 'Banner';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Konten Banner')
                    ->description('Kelola penempatan, gambar banner utama, judul, subjudul, dan teks tombol aksi.')
                    ->icon('lucide-image')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('placement')
                                ->label('Penempatan Banner')
                                ->options(BannerPlacement::class)
                                ->placeholder('Pilih penempatan')
                                ->prefixIcon('lucide-layout')
                                ->native(false)
                                ->required(),
                            TextInput::make('image_path')
                                ->label('Tautan Gambar Eksternal (Alternatif)')
                                ->placeholder('https://example.com/image.jpg')
                                ->prefixIcon('lucide-image')
                                ->helperText('Kosongkan jika menggunakan upload berkas gambar di bawah.')
                                ->url()
                                ->maxLength(2048),
                        ]),
                        SpatieMediaLibraryFileUpload::make('image')
                            ->label('Berkas Gambar Banner')
                            ->collection(Banner::MEDIA_COLLECTION_IMAGE)
                            ->disk('public')
                            ->visibility('public')
                            ->imageEditor()
                            ->helperText('Rasio ideal banner: 16:9 (JPG, PNG, WebP maks 5MB).')
                            ->requiredWithout('image_path')
                            ->columnSpanFull(),
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                TextInput::make('title')
                                    ->label('Judul Banner')
                                    ->placeholder('Tuliskan judul utama banner...')
                                    ->required($locale === 'id')
                                    ->maxLength(255)
                                    ->prefixIcon('lucide-type')
                                    ->helperText('Judul besar yang menarik perhatian.'),
                                TextInput::make('subtitle')
                                    ->label('Subjudul / Deskripsi')
                                    ->placeholder('Tuliskan subjudul deskripsi banner...')
                                    ->maxLength(500)
                                    ->prefixIcon('lucide-file-text')
                                    ->helperText('Deskripsi pelengkap berukuran lebih kecil.'),
                                TextInput::make('cta_label')
                                    ->label('Teks Tombol Aksi (CTA)')
                                    ->placeholder('Contoh: Hubungi Kami / Pesan Sekarang')
                                    ->maxLength(100)
                                    ->prefixIcon('lucide-mouse-pointer')
                                    ->helperText('Teks yang tertera pada tombol aksi banner.'),
                            ]),
                    ]),
                Section::make('Tombol CTA & Pengaturan Penjadwalan')
                    ->description('Kelola aksi tombol Call-To-Action (CTA), jadwal penayangan, urutan prioritas, dan status banner.')
                    ->icon('lucide-settings')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('cta_type')
                                ->label('Tipe Aksi (CTA)')
                                ->options(BannerCtaType::class)
                                ->placeholder('Tanpa tombol aksi')
                                ->prefixIcon('lucide-settings')
                                ->native(false)
                                ->live()
                                ->afterStateUpdated(fn (Set $set) => $set('cta_value', null))
                                ->helperText(fn (Get $get): ?string => self::ctaTypeIs($get('cta_type'), BannerCtaType::Whatsapp)
                                    ? 'Tombol akan membuka WhatsApp dengan nomor dari Pengaturan Umum.'
                                    : null),
                            Select::make('cta_value')
                                ->label('Halaman Tujuan')
                                ->options(Banner::CTA_ROUTE_OPTIONS)
                                ->placeholder('Pilih halaman website')
                                ->prefixIcon('lucide-link')
                                ->native(false)
                                ->required()
                                ->in(array_keys(Banner::CTA_ROUTE_OPTIONS))
                                ->visible(fn (Get $get): bool => self::ctaTypeIs($get('cta_type'), BannerCtaType::Route)),
                            TextInput::make('cta_value')
                                ->label('URL Tujuan')
                                ->placeholder('https://example.com/promo')
                                ->prefixIcon('lucide-link')
                                ->url()
                                ->startsWith(['http://', 'https://'])
                                ->required()
                                ->maxLength(255)
                                ->visible(fn (Get $get): bool => self::ctaTypeIs($get('cta_type'), BannerCtaType::Url)),
                        ]),
                        Grid::make(2)->schema([
                            DateTimePicker::make('starts_at')
                                ->label('Mulai Ditampilkan')
                                ->placeholder('Pilih tanggal & waktu')
                                ->helperText('Jadwal banner mulai ditampilkan. Kosongkan untuk langsung tampil.')
                                ->prefixIcon('lucide-calendar')
                                ->native(false),
                            DateTimePicker::make('ends_at')
                                ->label('Selesai Ditampilkan')
                                ->placeholder('Pilih tanggal & waktu')
                                ->helperText('Jadwal banner berhenti ditampilkan. Kosongkan untuk tampil terus-menerus.')
                                ->prefixIcon('lucide-calendar')
                                ->native(false),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('sort_order')
                                ->label('Urutan Tampilan')
                                ->placeholder('Contoh: 0')
                                ->numeric()
                                ->default(0)
                                ->prefixIcon('lucide-sort-asc')
                                ->helperText('Prioritas urutan penampilan slide banner.'),
                            Toggle::make('is_active')
                                ->label('Tampilkan di Beranda')
                                ->default(true),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('sort_order', 'asc')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Pratinjau')
                    ->state(fn (Banner $record): ?string => $record->image_url)
                    ->height(50),
                TextColumn::make('title')
                    ->label('Judul Banner')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('placement')
                    ->label('Penempatan')
                    ->badge(),
                TextColumn::make('cta_type')
                    ->label('Tipe CTA')
                    ->badge(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
                TextColumn::make('starts_at')
                    ->label('Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ends_at')
                    ->label('Selesai')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make()
                    ->label('Ubah')
                    ->icon('lucide-pencil')
                    ->slideOver()
                    ->color('primary'),
                DeleteAction::make()
                    ->label('Hapus')
                    ->icon('lucide-trash')
                    ->color('danger'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Banner')
            ->emptyStateDescription('Banner promosi utama belum terdaftar. Silakan tambahkan banner pertama Anda.')
            ->emptyStateIcon('lucide-images')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Tambah Banner')
                    ->icon('lucide-plus')
                    ->slideOver(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBanners::route('/'),
        ];
    }

    private static function ctaTypeIs(mixed $state, BannerCtaType $expected): bool
    {
        if ($state instanceof BannerCtaType) {
            return $state === $expected;
        }

        return is_string($state) && $state === $expected->value;
    }
}
