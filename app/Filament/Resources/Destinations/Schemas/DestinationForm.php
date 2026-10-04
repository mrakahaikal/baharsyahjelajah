<?php

namespace App\Filament\Resources\Destinations\Schemas;

use App\Models\Country;
use App\Models\Destination;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class DestinationForm
{
    public static function configure(Schema $schema): Schema
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
                                Select::make('countries')
                                    ->label('Negara Lokasi')
                                    ->relationship('countries', 'name')
                                    ->getOptionLabelFromRecordUsing(fn (Country $record): string => $record->name)
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->helperText('Pilih satu atau lebih negara lokasi destinasi ini.')
                                    ->prefixIcon('lucide-flag'),
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
}
