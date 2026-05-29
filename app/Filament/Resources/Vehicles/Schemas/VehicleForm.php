<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kendaraan')
                    ->description('Kelola detail identitas dan merek kendaraan.')
                    ->icon(Heroicon::OutlinedTruck)
                    ->columnSpanFull()
                    ->schema([
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                TextInput::make('name')
                                    ->label('Nama Unit')
                                    ->placeholder('Misal: Toyota Alphard Executive')
                                    ->required($locale === 'id')
                                    ->maxLength(255),
                            ]),
                        Grid::make(3)->schema([
                            TextInput::make('brand')
                                ->label('Merek')
                                ->placeholder('Misal: Toyota')
                                ->required()
                                ->maxLength(100),
                            TextInput::make('model')
                                ->label('Model / Seri')
                                ->placeholder('Misal: Alphard G')
                                ->required()
                                ->maxLength(100),
                            TextInput::make('year')
                                ->label('Tahun')
                                ->placeholder('2024')
                                ->numeric()
                                ->minValue(2000)
                                ->maxValue(2030),
                        ]),
                    ]),

                Section::make('Spesifikasi & Fasilitas')
                    ->description('Tentukan kapasitas dan fitur kenyamanan kendaraan.')
                    ->icon(Heroicon::OutlinedCog6Tooth)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('capacity_pax')
                                ->label('Kapasitas Penumpang')
                                ->placeholder('7')
                                ->numeric()
                                ->suffix('Orang')
                                ->required(),
                            TextInput::make('capacity_luggage')
                                ->label('Kapasitas Bagasi')
                                ->placeholder('3')
                                ->numeric()
                                ->suffix('Koper')
                                ->default(0),
                            Select::make('transmission')
                                ->label('Transmisi')
                                ->options([
                                    'automatic' => 'Otomatis',
                                    'manual' => 'Manual',
                                ])
                                ->default('automatic')
                                ->required(),
                        ]),
                        Grid::make(2)->schema([
                            Toggle::make('has_ac')
                                ->label('Dilengkapi AC')
                                ->default(true)
                                ->inline(false),
                            Toggle::make('has_wifi')
                                ->label('Dilengkapi WiFi')
                                ->default(false)
                                ->inline(false),
                        ]),
                        TagsInput::make('features')
                            ->label('Fitur Tambahan Lainnya')
                            ->placeholder('Tambah fitur (tekan enter)...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Harga & Media')
                    ->description('Atur harga sewa dan foto utama kendaraan.')
                    ->icon(Heroicon::OutlinedCurrencyDollar)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('price_per_day_idr')
                                ->label('Harga per Hari')
                                ->placeholder('0')
                                ->numeric()
                                ->prefix('Rp')
                                ->helperText('Biaya sewa untuk penggunaan harian.'),
                            TextInput::make('price_per_trip_idr')
                                ->label('Harga per Trip')
                                ->placeholder('0')
                                ->numeric()
                                ->prefix('Rp')
                                ->helperText('Biaya sewa untuk satu kali perjalanan.'),
                        ]),
                        FileUpload::make('thumbnail')
                            ->label('Foto Utama Kendaraan')
                            ->image()
                            ->directory('vehicles/thumbnails')
                            ->visibility('public')
                            ->imageEditor()
                            ->columnSpanFull(),
                        Toggle::make('is_available')
                            ->label('Unit Tersedia / Siap Jalan')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}
