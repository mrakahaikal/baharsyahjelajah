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
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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
                        ->icon(Heroicon::OutlinedInformationCircle)
                        ->schema([
                            Section::make('Informasi Dasar')
                                ->schema([
                                    Grid::make(3)
                                        ->schema([
                                            Select::make('tour_category_id')
                                                ->label('Kategori')
                                                ->relationship('category', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->required(),
                                            Select::make('tour_type')
                                                ->label('Tipe Tur')
                                                ->options([
                                                    'domestic' => 'Domestik',
                                                    'international' => 'Internasional',
                                                ])
                                                ->default('domestic')
                                                ->required(),
                                            Select::make('currency')
                                                ->label('Mata Uang Dasar')
                                                ->options(self::currencyOptions())
                                                ->default('IDR')
                                                ->required(),
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
                                                        ->maxLength(255),
                                                    TextInput::make('slug')
                                                        ->label('Slug')
                                                        ->required($locale === 'id')
                                                        ->maxLength(255),
                                                ]),
                                            Textarea::make('short_description')
                                                ->label('Deskripsi Singkat')
                                                ->rows(3)
                                                ->required($locale === 'id')
                                                ->columnSpanFull(),
                                            RichEditor::make('description')
                                                ->label('Deskripsi Lengkap')
                                                ->required($locale === 'id')
                                                ->columnSpanFull(),
                                        ]),
                                ]),
                        ]),

                    Step::make('Paket Tur')
                        ->icon(Heroicon::OutlinedBriefcase)
                        ->schema([
                            Repeater::make('packages')
                                ->label('Paket')
                                ->relationship()
                                ->minItems(1)
                                ->defaultItems(1)
                                ->addActionLabel('Tambah Paket')
                                ->collapsible()
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
                                                        ->maxLength(255),
                                                    TextInput::make('slug')
                                                        ->label('Slug Paket')
                                                        ->required($locale === 'id')
                                                        ->maxLength(255),
                                                ]),
                                        ]),
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('duration_days')
                                                ->label('Durasi Hari')
                                                ->numeric()
                                                ->minValue(1)
                                                ->suffix('Hari')
                                                ->required(),
                                            TextInput::make('duration_nights')
                                                ->label('Durasi Malam')
                                                ->numeric()
                                                ->minValue(0)
                                                ->default(0)
                                                ->suffix('Malam')
                                                ->required(),
                                        ]),
                                    Section::make('Media')
                                        ->schema([
                                            SpatieMediaLibraryFileUpload::make('cover')
                                                ->label('Foto Utama')
                                                ->collection(TourPackage::MEDIA_COLLECTION_COVER)
                                                ->image()
                                                ->imageEditor()
                                                ->visibility('public')
                                                ->columnSpanFull(),
                                            SpatieMediaLibraryFileUpload::make('gallery')
                                                ->label('Galeri')
                                                ->collection(TourPackage::MEDIA_COLLECTION_GALLERY)
                                                ->image()
                                                ->multiple()
                                                ->reorderable()
                                                ->appendFiles()
                                                ->visibility('public')
                                                ->columnSpanFull(),
                                        ]),
                                    Repeater::make('itineraries')
                                        ->label('Itinerary')
                                        ->relationship()
                                        ->orderColumn('day_number')
                                        ->addActionLabel('Tambah Hari')
                                        ->collapsible()
                                        ->schema([
                                            TextInput::make('day_number')
                                                ->label('Hari Ke')
                                                ->numeric()
                                                ->minValue(1)
                                                ->required(),
                                            Translate::make()
                                                ->locales(['id', 'en', 'ms'])
                                                ->schema(fn (string $locale): array => [
                                                    TextInput::make('title')
                                                        ->label('Judul')
                                                        ->required($locale === 'id')
                                                        ->maxLength(255),
                                                    RichEditor::make('description')
                                                        ->label('Deskripsi')
                                                        ->required($locale === 'id'),
                                                ]),
                                            Select::make('destinations')
                                                ->label('Destinasi yang Dikunjungi')
                                                ->relationship('destinations', 'name')
                                                ->multiple()
                                                ->searchable()
                                                ->preload()
                                                ->getOptionLabelFromRecordUsing(fn (Destination $record): string => $record->name)
                                                ->native(false)
                                                ->helperText('Pilih satu atau beberapa destinasi yang dikunjungi pada hari ini.')
                                                ->columnSpanFull(),
                                        ]),
                                    Repeater::make('includes')
                                        ->label('Termasuk dan Tidak Termasuk')
                                        ->relationship()
                                        ->orderColumn('sort_order')
                                        ->addActionLabel('Tambah Item')
                                        ->columns(2)
                                        ->schema([
                                            Select::make('type')
                                                ->label('Jenis')
                                                ->options([
                                                    'include' => 'Termasuk',
                                                    'exclude' => 'Tidak Termasuk',
                                                    'note' => 'Catatan',
                                                ])
                                                ->default('include')
                                                ->required(),
                                            TextInput::make('sort_order')
                                                ->label('Urutan')
                                                ->numeric()
                                                ->minValue(0)
                                                ->default(0)
                                                ->required(),
                                            Translate::make()
                                                ->locales(['id', 'en', 'ms'])
                                                ->schema(fn (string $locale): array => [
                                                    TextInput::make('item')
                                                        ->label('Item')
                                                        ->required($locale === 'id')
                                                        ->maxLength(255),
                                                ])
                                                ->columnSpanFull(),
                                        ]),
                                    Repeater::make('tiers')
                                        ->label('Tier Paket dan Harga')
                                        ->relationship()
                                        ->minItems(1)
                                        ->defaultItems(1)
                                        ->addActionLabel('Tambah Tier')
                                        ->collapsible()
                                        ->schema([
                                            Translate::make()
                                                ->locales(['id', 'en', 'ms'])
                                                ->schema(fn (string $locale): array => [
                                                    TextInput::make('name')
                                                        ->label('Nama Tier')
                                                        ->required($locale === 'id')
                                                        ->maxLength(255),
                                                ]),
                                            Select::make('hotel_stars')
                                                ->label('Bintang Hotel')
                                                ->options([
                                                    1 => '1 Bintang',
                                                    2 => '2 Bintang',
                                                    3 => '3 Bintang',
                                                    4 => '4 Bintang',
                                                    5 => '5 Bintang',
                                                ]),
                                            Repeater::make('priceTiers')
                                                ->label('Harga Berdasarkan Jumlah Peserta')
                                                ->relationship()
                                                ->minItems(1)
                                                ->defaultItems(1)
                                                ->addActionLabel('Tambah Harga')
                                                ->columns(4)
                                                ->schema([
                                                    TextInput::make('min_pax')
                                                        ->label('Minimal Pax')
                                                        ->numeric()
                                                        ->minValue(1)
                                                        ->required(),
                                                    TextInput::make('max_pax')
                                                        ->label('Maksimal Pax')
                                                        ->numeric()
                                                        ->minValue(1),
                                                    TextInput::make('price')
                                                        ->label('Harga per Pax')
                                                        ->numeric()
                                                        ->minValue(0)
                                                        ->required(),
                                                    Select::make('currency')
                                                        ->label('Mata Uang')
                                                        ->options(self::currencyOptions())
                                                        ->default('IDR')
                                                        ->required(),
                                                ]),
                                        ]),
                                ])
                                ->columnSpanFull(),
                        ]),

                    Step::make('Publikasi')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->schema([
                            Section::make('Status')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Toggle::make('is_active')
                                                ->label('Aktif')
                                                ->default(true)
                                                ->required(),
                                            Toggle::make('is_featured')
                                                ->label('Unggulan')
                                                ->default(false)
                                                ->required(),
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
        return [
            'IDR' => 'IDR (Rp)',
            'USD' => 'USD ($)',
            'MYR' => 'MYR (RM)',
            'SGD' => 'SGD (S$)',
        ];
    }
}
