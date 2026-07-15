<?php

namespace App\Filament\Resources\Destinations;

use App\Filament\Resources\Destinations\Pages\ManageDestinations;
use App\Models\Destination;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class DestinationResource extends Resource
{
    protected static ?string $model = Destination::class;

    protected static string|BackedEnum|null $navigationIcon = 'lucide-map-pin';

    protected static string|null|\UnitEnum $navigationGroup = 'Manajemen Tur';

    protected static ?string $navigationLabel = 'Destinasi';

    protected static ?string $modelLabel = 'Destinasi';

    protected static ?string $pluralModelLabel = 'Destinasi';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama Destinasi')
                    ->description('Kelola detail nama, lokasi, dan deskripsi destinasi dalam setiap bahasa.')
                    ->icon('lucide-map-pin')
                    ->schema([
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale): array => [
                                TextInput::make('name')
                                    ->label('Nama Destinasi')
                                    ->required($locale === 'id')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) use ($locale): void {
                                        if ($locale === 'id' && blank($get('slug'))) {
                                            $set('slug', Str::slug($state ?? ''));
                                        }
                                    })
                                    ->maxLength(255)
                                    ->placeholder('Masukkan nama destinasi (contoh: Taman Nasional Tanjung Puting)')
                                    ->helperText('Nama destinasi unik yang akan ditampilkan.')
                                    ->prefixIcon('lucide-type'),
                                Textarea::make('description')
                                    ->label('Deskripsi Destinasi')
                                    ->rows(4)
                                    ->maxLength(2000)
                                    ->placeholder('Tuliskan daya tarik utama dan penjelasan detail mengenai destinasi ini...')
                                    ->helperText('Deskripsi lengkap mengenai suasana dan informasi penting destinasi.')
                                    ->columnSpanFull(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('slug')
                                    ->label('Slug URL')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->dehydrateStateUsing(fn (?string $state): string => Str::slug($state ?? ''))
                                    ->maxLength(255)
                                    ->placeholder('taman-nasional-tanjung-puting')
                                    ->helperText('Tautan URL halaman destinasi. Terisi otomatis dari nama destinasi.')
                                    ->prefixIcon('lucide-link-2'),
                                TextInput::make('location')
                                    ->label('Lokasi Administratif')
                                    ->placeholder('Kota, Provinsi, atau Negara (contoh: Kotawaringin Barat, Kalteng)')
                                    ->maxLength(255)
                                    ->helperText('Wilayah administratif destinasi.')
                                    ->prefixIcon('lucide-globe'),
                                TextInput::make('map_url')
                                    ->label('URL Google Maps')
                                    ->rules(['nullable', 'url:http,https'])
                                    ->placeholder('https://maps.google.com/...')
                                    ->maxLength(2048)
                                    ->helperText('Tautan URL peta Google Maps dari destinasi.')
                                    ->prefixIcon('lucide-map')
                                    ->columnSpanFull(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Aktif dan Dapat Diakses Publik')
                                    ->default(true),
                                Toggle::make('is_featured')
                                    ->label('Destinasi Unggulan')
                                    ->default(false),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Galeri Foto Destinasi')
                    ->description('Unggah foto-foto visual pendukung yang menggambarkan keindahan destinasi.')
                    ->icon('lucide-images')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('gallery')
                            ->hiddenLabel()
                            ->collection(Destination::MEDIA_COLLECTION_GALLERY)
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120)
                            ->multiple()
                            ->reorderable()
                            ->appendFiles()
                            ->imageEditor()
                            ->disk('public')
                            ->visibility('public')
                            ->helperText('Format: JPG, PNG, WebP (Rasio ideal 4:3, maks 5MB per file).')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('created_at', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('gallery')
                    ->label('Foto')
                    ->collection(Destination::MEDIA_COLLECTION_GALLERY)
                    ->square()
                    ->size(52),
                TextColumn::make('name')
                    ->label('Nama Destinasi')
                    ->searchable()
                    ->weight('bold')
                    ->wrap(),
                TextColumn::make('slug')
                    ->label('Slug URL')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('location')
                    ->label('Lokasi')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('tour_packages_count')
                    ->label('Paket Tur')
                    ->counts('tourPackages')
                    ->badge()
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('itineraries_count')
                    ->label('Itinerary')
                    ->counts('itineraries')
                    ->badge()
                    ->alignCenter()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean(),
                TextColumn::make('map_url')
                    ->label('Peta')
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? 'Buka Peta' : '-')
                    ->url(fn (Destination $record): ?string => $record->map_url)
                    ->openUrlInNewTab()
                    ->icon('lucide-external-link'),
                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Pembaruan Terakhir')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
                TernaryFilter::make('is_featured')
                    ->label('Destinasi Unggulan'),
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
            ->emptyStateHeading('Belum Ada Destinasi Wisata')
            ->emptyStateDescription('Buat destinasi wisata baru untuk mulai menghubungkannya ke itinerary paket tur.')
            ->emptyStateIcon('lucide-map-pin')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Tambah Destinasi')
                    ->icon('lucide-plus')
                    ->slideOver(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageDestinations::route('/'),
        ];
    }
}
