<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use App\Models\Vehicle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

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
                                    ->description('Detail nama unit, deskripsi, dan galeri foto.')
                                    ->icon('lucide-truck')
                                    ->schema([
                                        SpatieMediaLibraryImageEntry::make('cover')
                                            ->hiddenLabel()
                                            ->collection('cover')
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
                                        TextEntry::make('description')
                                            ->label('Deskripsi')
                                            ->columnSpanFull(),
                                        SpatieMediaLibraryImageEntry::make('gallery')
                                            ->label('Galeri')
                                            ->collection('gallery')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Fitur & Spesifikasi Lengkap')
                                    ->description('Daftar kelengkapan fitur tambahan armada.')
                                    ->icon('lucide-cog')
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
                                    ->description('Kapasitas penumpang, bagasi, dan transmisi.')
                                    ->icon('lucide-users')
                                    ->schema([
                                        TextEntry::make('capacity_pax')
                                            ->label('Kapasitas Penumpang')
                                            ->icon('lucide-users')
                                            ->suffix(' Orang'),
                                        TextEntry::make('capacity_luggage')
                                            ->label('Kapasitas Bagasi')
                                            ->icon('lucide-briefcase')
                                            ->suffix(' Koper'),
                                        TextEntry::make('transmission')
                                            ->label('Tipe Transmisi')
                                            ->badge()
                                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                                'automatic' => 'Otomatis',
                                                'manual' => 'Manual',
                                                default => $state ?: 'Tidak ditentukan',
                                            })
                                            ->color('info'),
                                    ]),

                                Section::make('Fasilitas & Harga')
                                    ->description('Ketersediaan fasilitas AC/WiFi dan harga sewa.')
                                    ->icon('lucide-banknote')
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
                                        TextEntry::make('overtime_rate_idr')
                                            ->label('Lembur per Jam')
                                            ->money('IDR', locale: 'id')
                                            ->weight(FontWeight::Bold)
                                            ->size(TextSize::Large)
                                            ->color('success'),
                                        RepeatableEntry::make('rentalRates')
                                            ->label('Tarif per Wilayah')
                                            ->schema([
                                                TextEntry::make('area.name')->label('Wilayah')->weight(FontWeight::Bold),
                                                TextEntry::make('price_per_day_idr')->label('Per Hari')->money('IDR', locale: 'id'),
                                                TextEntry::make('valid_from')->label('Mulai')->date('d M Y'),
                                                TextEntry::make('valid_until')->label('Sampai')->date('d M Y')->placeholder('Tanpa batas'),
                                            ])
                                            ->columns(2)
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Status Unit')
                                    ->description('Status penayangan di katalog dan tanggal sistem.')
                                    ->icon('lucide-info')
                                    ->schema([
                                        IconEntry::make('is_active')
                                            ->label('Tampil di Katalog')
                                            ->boolean(),
                                        IconEntry::make('is_featured')
                                            ->label('Armada Unggulan')
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
