<?php

namespace App\Filament\Resources\Countries\Schemas;

use App\Models\Country;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class CountryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Negara')
                    ->description('Kelola negara tujuan yang dapat dipilih pada layanan Visa.')
                    ->icon('lucide-globe-2')
                    ->schema([
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale): array => [
                                TextInput::make('name')
                                    ->label('Nama Negara')
                                    ->required($locale === 'id')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) use ($locale): void {
                                        if ($locale === 'id' && blank($get('slug'))) {
                                            $set('slug', Str::slug($state ?? ''));
                                        }
                                    })
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Mesir')
                                    ->prefixIcon('lucide-type'),
                            ])
                            ->columnSpanFull(),
                        Grid::make(3)->schema([
                            TextInput::make('slug')
                                ->label('Slug URL')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->dehydrateStateUsing(fn (?string $state): string => Str::slug($state ?? ''))
                                ->maxLength(255)
                                ->prefixIcon('lucide-link-2'),
                            TextInput::make('iso_alpha_2')
                                ->label('Kode ISO Alpha-2')
                                ->required()
                                ->rules(['alpha', 'size:2'])
                                ->unique(ignoreRecord: true)
                                ->dehydrateStateUsing(fn (?string $state): string => Str::upper($state ?? ''))
                                ->placeholder('EG')
                                ->prefixIcon('lucide-hash'),
                            TextInput::make('iso_alpha_3')
                                ->label('Kode ISO Alpha-3')
                                ->rules(['nullable', 'alpha', 'size:3'])
                                ->unique(ignoreRecord: true)
                                ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Str::upper($state) : null)
                                ->placeholder('EGY')
                                ->prefixIcon('lucide-hash'),
                        ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Bendera & Publikasi')
                    ->description('Tambahkan bendera dan atur ketersediaan negara pada pilihan layanan Visa.')
                    ->icon('lucide-flag')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('flag')
                            ->label('Gambar Bendera')
                            ->collection(Country::MEDIA_COLLECTION_FLAG)
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->imageEditor()
                            ->disk('public')
                            ->visibility('public')
                            ->helperText('Format JPG, PNG, atau WebP. Maksimal 2 MB.'),
                        Grid::make(2)->schema([
                            TextInput::make('sort_order')
                                ->label('Urutan Tampilan')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),
                            Toggle::make('is_active')
                                ->label('Aktif dan Dapat Dipilih')
                                ->default(true),
                        ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }
}
