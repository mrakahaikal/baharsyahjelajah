<?php

namespace App\Filament\Resources\Destinations;

use App\Filament\Resources\Destinations\Pages\ManageDestinations;
use App\Models\Destination;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class DestinationResource extends Resource
{
    protected static ?string $model = Destination::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|null|\UnitEnum $navigationGroup = 'Manajemen Tur';

    protected static ?string $navigationLabel = 'Destinasi';

    protected static ?string $modelLabel = 'Destinasi';

    protected static ?string $pluralModelLabel = 'Destinasi';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Destinasi')
                    ->description('Kelola nama, lokasi, dan deskripsi destinasi dalam setiap bahasa.')
                    ->icon(Heroicon::OutlinedMapPin)
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
                                    ->maxLength(255),
                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(4)
                                    ->maxLength(2000)
                                    ->columnSpanFull(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->dehydrateStateUsing(fn (?string $state): string => Str::slug($state ?? ''))
                                    ->maxLength(255),
                                TextInput::make('location')
                                    ->label('Lokasi')
                                    ->placeholder('Kota, Provinsi, atau Negara')
                                    ->maxLength(255),
                                TextInput::make('map_url')
                                    ->label('URL Peta')
                                    ->url()
                                    ->placeholder('https://maps.google.com/...')
                                    ->maxLength(2048)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Galeri Destinasi')
                    ->description('Unggah foto yang membantu mengenali suasana dan daya tarik destinasi.')
                    ->icon(Heroicon::OutlinedPhoto)
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
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('location')
                    ->label('Lokasi')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('tour_packages_count')
                    ->label('Paket')
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
                TextColumn::make('map_url')
                    ->label('Peta')
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? 'Buka peta' : '-')
                    ->url(fn (Destination $record): ?string => $record->map_url)
                    ->openUrlInNewTab()
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Ubah'),
                DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageDestinations::route('/'),
        ];
    }
}
