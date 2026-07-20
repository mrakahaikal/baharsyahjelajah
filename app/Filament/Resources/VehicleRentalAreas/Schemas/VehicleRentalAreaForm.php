<?php

namespace App\Filament\Resources\VehicleRentalAreas\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class VehicleRentalAreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Wilayah')
                    ->description('Wilayah ini menentukan ketersediaan armada, tarif, dan minimum durasi sewa.')
                    ->icon('lucide-map-pinned')
                    ->columnSpanFull()
                    ->schema([
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale): array => [
                                TextInput::make('name')
                                    ->label('Nama Wilayah')
                                    ->required($locale === 'id')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (?string $state, Set $set) => $locale === 'id' ? $set('../../slug', Str::slug($state ?? '')) : null),
                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(3),
                            ]),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Grid::make(3)->schema([
                            TextInput::make('minimum_rental_days')
                                ->label('Minimum Sewa')
                                ->numeric()
                                ->minValue(1)
                                ->suffix('hari')
                                ->required()
                                ->default(1),
                            TextInput::make('sort_order')
                                ->label('Urutan')
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
                            Toggle::make('is_active')
                                ->label('Wilayah Aktif')
                                ->default(true),
                        ]),
                    ]),
            ]);
    }
}
