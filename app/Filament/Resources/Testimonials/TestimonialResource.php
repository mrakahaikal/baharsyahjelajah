<?php

namespace App\Filament\Resources\Testimonials;

use App\Filament\Resources\Testimonials\Pages\ManageTestimonials;
use App\Models\Testimonial;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|BackedEnum|null $navigationIcon = 'lucide-message-square';

    protected static string|null|\UnitEnum $navigationGroup = 'Konten Website';

    protected static ?string $navigationLabel = 'Testimoni';

    protected static ?string $modelLabel = 'Testimoni';

    protected static ?string $pluralModelLabel = 'Testimoni';

    protected static ?string $recordTitleAttribute = 'reviewer_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Reviewer')
                    ->description('Kelola identitas reviewer, foto profil, asal negara, dan nama.')
                    ->icon('lucide-user')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('reviewer_country')
                                ->label('Negara Asal')
                                ->placeholder('Contoh: Indonesia')
                                ->maxLength(100)
                                ->prefixIcon('lucide-globe')
                                ->helperText('Negara asal pelanggan yang mengulas.'),
                            TextInput::make('reviewer_flag')
                                ->label('Kode Negara')
                                ->placeholder('Contoh: ID')
                                ->maxLength(10)
                                ->prefixIcon('lucide-flag')
                                ->helperText('Kode negara dua huruf (misal: ID untuk Indonesia, MY untuk Malaysia).'),
                        ]),
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                TextInput::make('reviewer_name')
                                    ->label('Nama Reviewer')
                                    ->placeholder('Masukkan nama lengkap reviewer...')
                                    ->required($locale === 'id')
                                    ->maxLength(255)
                                    ->prefixIcon('lucide-type')
                                    ->helperText('Nama lengkap pelanggan.'),
                            ]),
                        FileUpload::make('photo')
                            ->label('Foto Profil Reviewer')
                            ->image()
                            ->directory('testimonials/photos')
                            ->disk('public')
                            ->visibility('public')
                            ->avatar()
                            ->imageEditor()
                            ->helperText('Foto avatar pelanggan (JPG, PNG, WebP maks 2MB).')
                            ->columnSpanFull(),
                    ]),
                Section::make('Ulasan & Penilaian')
                    ->description('Kelola rating, jenis produk yang diulas, isi komentar, dan status publikasi.')
                    ->icon('lucide-star')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('product_type')
                                ->label('Tipe Produk')
                                ->options([
                                    'App\Models\Tour' => 'Tour Wisata',
                                    'App\Models\Vehicle' => 'Sewa Kendaraan',
                                    'App\Models\UmrahPackage' => 'Paket Umrah',
                                ])
                                ->placeholder('Pilih jenis produk')
                                ->prefixIcon('lucide-package')
                                ->native(false)
                                ->required(),
                            TextInput::make('product_id')
                                ->label('ID Produk')
                                ->numeric()
                                ->required()
                                ->placeholder('Masukkan ID model produk')
                                ->prefixIcon('lucide-hash')
                                ->helperText('Nomor ID unik dari produk yang diulas.'),
                            Select::make('rating')
                                ->label('Rating Penilaian')
                                ->options([
                                    1 => '1 Bintang (Sangat Buruk)',
                                    2 => '2 Bintang (Buruk)',
                                    3 => '3 Bintang (Cukup)',
                                    4 => '4 Bintang (Baik)',
                                    5 => '5 Bintang (Sangat Baik)',
                                ])
                                ->placeholder('Pilih rating')
                                ->prefixIcon('lucide-star')
                                ->native(false)
                                ->required(),
                        ]),
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                TextInput::make('content')
                                    ->label('Isi Ulasan')
                                    ->placeholder('Tuliskan cerita pengalaman dan ulasan pelanggan di sini...')
                                    ->required($locale === 'id')
                                    ->maxLength(1000)
                                    ->prefixIcon('lucide-message-square')
                                    ->helperText('Konten teks ulasan pelanggan.')
                                    ->columnSpanFull(),
                            ]),
                        Grid::make(2)->schema([
                            Toggle::make('is_featured')
                                ->label('Tampilkan di Halaman Utama (Unggulan)')
                                ->default(false),
                            Toggle::make('is_active')
                                ->label('Status Aktif')
                                ->default(true),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reviewer_name')
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular()
                    ->size(40),
                TextColumn::make('reviewer_name')
                    ->label('Nama Reviewer')
                    ->searchable()
                    ->description(fn (Testimonial $record): string => $record->reviewer_country ?? ''),
                TextColumn::make('product_type')
                    ->label('Tipe Produk')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'App\Models\Tour' => 'Tour',
                        'App\Models\Vehicle' => 'Sewa Mobil',
                        'App\Models\UmrahPackage' => 'Umrah',
                        default => $state ?? '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'App\Models\Tour' => 'success',
                        'App\Models\Vehicle' => 'info',
                        'App\Models\UmrahPackage' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state).str_repeat('☆', 5 - $state))
                    ->alignCenter(),
                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_type')
                    ->label('Tipe Produk')
                    ->options([
                        'App\Models\Tour' => 'Tour Wisata',
                        'App\Models\Vehicle' => 'Sewa Kendaraan',
                        'App\Models\UmrahPackage' => 'Paket Umrah',
                    ])
                    ->native(false),
                SelectFilter::make('rating')
                    ->label('Rating')
                    ->options([
                        1 => '1 Bintang',
                        2 => '2 Bintang',
                        3 => '3 Bintang',
                        4 => '4 Bintang',
                        5 => '5 Bintang',
                    ])
                    ->native(false),
            ])
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
            ->emptyStateHeading('Belum Ada Testimoni')
            ->emptyStateDescription('Ulasan dari pelanggan belum terdaftar. Silakan tambahkan testimoni pertama Anda.')
            ->emptyStateIcon('lucide-message-square')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Tambah Testimoni')
                    ->icon('lucide-plus')
                    ->slideOver(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTestimonials::route('/'),
        ];
    }
}
