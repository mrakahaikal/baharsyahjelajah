<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use App\Models\Vehicle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class VehicleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Section::make('Informasi Utama')
                                    ->schema([
                                        ImageEntry::make('thumbnail')
                                            ->hiddenLabel()
                                            ->square()
                                            ->width('100%')
                                            ->height(300),
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('name')
                                                    ->label('Nama Unit')
                                                    ->weight(FontWeight::Bold)
                                                    ->size(TextSize::Large),
                                                TextEntry::make('brand')
                                                    ->label('Merek')
                                                    ->badge()
                                                    ->color('info'),
                                            ]),
                                        TextEntry::make('model')
                                            ->label('Model / Seri')
                                            ->placeholder('-'),
                                    ]),

                                Section::make('Fitur & Spesifikasi Lengkap')
                                    ->schema([
                                        TextEntry::make('features')
                                            ->label('Daftar Fitur Tambahan')
                                            ->badge()
                                            ->placeholder('Tidak ada fitur tambahan khusus.'),
                                    ]),
                            ])
                            ->columnSpan(2),

                        Grid::make(1)
                            ->schema([
                                Section::make('Detail Kapasitas')
                                    ->icon(Heroicon::OutlinedTruck)
                                    ->schema([
                                        TextEntry::make('capacity_pax')
                                            ->label('Kapasitas Penumpang')
                                            ->icon(Heroicon::OutlinedUsers)
                                            ->suffix(' Orang'),
                                        TextEntry::make('capacity_luggage')
                                            ->label('Kapasitas Bagasi')
                                            ->icon(Heroicon::OutlinedBriefcase)
                                            ->suffix(' Koper'),
                                        TextEntry::make('transmission')
                                            ->label('Tipe Transmisi')
                                            ->badge()
                                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                                'automatic' => 'Otomatis',
                                                'manual' => 'Manual',
                                                default => $state,
                                            })
                                            ->color('info'),
                                    ]),

                                Section::make('Fasilitas & Harga')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                IconEntry::make('has_ac')
                                                    ->label('AC')
                                                    ->boolean(),
                                                IconEntry::make('has_wifi')
                                                    ->label('WiFi')
                                                    ->boolean(),
                                            ]),
                                        TextEntry::make('price_per_day_idr')
                                            ->label('Sewa per Hari')
                                            ->money('IDR', locale: 'id')
                                            ->weight(FontWeight::Bold)
                                            ->size(TextSize::Large)
                                            ->color('success'),
                                        TextEntry::make('price_per_trip_idr')
                                            ->label('Sewa per Trip')
                                            ->money('IDR', locale: 'id'),
                                    ]),

                                Section::make('Status Unit')
                                    ->schema([
                                        IconEntry::make('is_available')
                                            ->label('Status Ketersediaan')
                                            ->boolean(),
                                        TextEntry::make('created_at')
                                            ->label('Ditambahkan Pada')
                                            ->dateTime('d M Y H:i'),
                                        TextEntry::make('deleted_at')
                                            ->label('Dihapus Pada')
                                            ->dateTime('d M Y H:i')
                                            ->visible(fn (Vehicle $record): bool => $record->trashed()),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
