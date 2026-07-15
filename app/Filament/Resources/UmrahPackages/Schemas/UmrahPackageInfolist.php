<?php

namespace App\Filament\Resources\UmrahPackages\Schemas;

use App\Enums\UmrahPackageType;
use App\Enums\UmrahRoomType;
use App\Models\UmrahPackage;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class UmrahPackageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make('Informasi Utama Paket')
                            ->description('Detail utama berupa nama, tipe, durasi perjalanan, deskripsi, dan visual paket umrah.')
                            ->icon('lucide-info')
                            ->schema([
                                ImageEntry::make('cover')
                                    ->hiddenLabel()
                                    ->state(fn (UmrahPackage $record): string => $record->thumbnail_url)
                                    ->height(320)
                                    ->columnSpanFull(),
                                SpatieMediaLibraryImageEntry::make('gallery')
                                    ->label('Galeri Foto Pendukung')
                                    ->collection(UmrahPackage::MEDIA_COLLECTION_GALLERY)
                                    ->stacked()
                                    ->limit(6)
                                    ->limitedRemainingText()
                                    ->columnSpanFull(),
                                TextEntry::make('name')
                                    ->label('Nama Paket')
                                    ->weight(FontWeight::Bold)
                                    ->size(TextSize::Large),
                                TextEntry::make('package_type')
                                    ->label('Tipe Paket Umrah')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => UmrahPackageType::tryFrom($state)?->getLabel() ?? $state),
                                TextEntry::make('duration_days')
                                    ->label('Durasi Perjalanan')
                                    ->suffix(' Hari'),
                                TextEntry::make('description')
                                    ->label('Deskripsi Lengkap')
                                    ->html()
                                    ->prose()
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpan(2),
                        Grid::make(1)
                            ->schema([
                                Section::make('Struktur Harga Kamar')
                                    ->description('Rincian biaya paket per jamaah berdasarkan pilihan tipe kamar.')
                                    ->icon('lucide-banknote')
                                    ->schema([
                                        RepeatableEntry::make('prices')
                                            ->hiddenLabel()
                                            ->schema([
                                                TextEntry::make('room_type')
                                                    ->label('Tipe Kamar')
                                                    ->formatStateUsing(fn (string $state): string => UmrahRoomType::tryFrom($state)?->getLabel() ?? $state),
                                                TextEntry::make('price_idr')
                                                    ->label('Harga')
                                                    ->money('IDR', locale: 'id'),
                                            ])
                                            ->columns(2),
                                    ]),
                                Section::make('Status & Metadata')
                                    ->description('Status aktif, status unggulan, dan tanggal pembaruan paket.')
                                    ->icon('lucide-settings')
                                    ->schema([
                                        IconEntry::make('is_active')
                                            ->label('Aktif')
                                            ->boolean(),
                                        IconEntry::make('is_featured')
                                            ->label('Unggulan')
                                            ->boolean(),
                                        TextEntry::make('updated_at')
                                            ->label('Terakhir Diperbarui')
                                            ->dateTime('d M Y H:i'),
                                        TextEntry::make('deleted_at')
                                            ->label('Dihapus Pada')
                                            ->dateTime('d M Y H:i')
                                            ->visible(fn (UmrahPackage $record): bool => $record->trashed()),
                                    ]),
                            ])
                            ->columnSpan(1),
                        Section::make('Detail Akomodasi & Fasilitas')
                            ->description('Informasi maskapai penerbangan, hotel di Makkah & Madinah, serta cakupan visa dan handling.')
                            ->icon('lucide-briefcase')
                            ->schema([
                                TextEntry::make('airline')
                                    ->label('Maskapai Penerbangan')
                                    ->placeholder('-'),
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('hotel_makkah')
                                            ->label('Hotel Makkah')
                                            ->placeholder('-'),
                                        TextEntry::make('hotel_makkah_stars')
                                            ->label('Bintang Hotel Makkah')
                                            ->formatStateUsing(fn (?int $state): string => $state ? "{$state} Bintang" : '-'),
                                        TextEntry::make('hotel_madinah')
                                            ->label('Hotel Madinah')
                                            ->placeholder('-'),
                                        TextEntry::make('hotel_madinah_stars')
                                            ->label('Bintang Hotel Madinah')
                                            ->formatStateUsing(fn (?int $state): string => $state ? "{$state} Bintang" : '-'),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        IconEntry::make('visa_included')
                                            ->label('Termasuk Visa')
                                            ->boolean(),
                                        IconEntry::make('handling_included')
                                            ->label('Termasuk Handling & Perlengkapan')
                                            ->boolean(),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(1);
    }
}
