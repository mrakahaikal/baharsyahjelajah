<?php

namespace App\Filament\Resources\Tours\Schemas;

use App\Enums\TourType;
use App\Models\TourPackage;
use App\Models\TourPriceTier;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class TourInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make('Informasi Tur')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nama Tur')
                                    ->weight(FontWeight::Bold)
                                    ->size(TextSize::Large),
                                TextEntry::make('category.name')
                                    ->label('Kategori')
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('tour_type')
                                    ->label('Tipe Tur')
                                    ->badge()
                                    ->formatStateUsing(fn (TourType $state): string => $state->getLabel())
                                    ->color(fn (TourType $state): string => match ($state) {
                                        TourType::Domestic => 'success',
                                        TourType::International => 'info',
                                    }),
                                TextEntry::make('currency')
                                    ->label('Mata Uang Dasar')
                                    ->badge(),
                                TextEntry::make('short_description')
                                    ->label('Deskripsi Singkat')
                                    ->columnSpanFull(),
                                TextEntry::make('description')
                                    ->label('Deskripsi Lengkap')
                                    ->html()
                                    ->prose()
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpan(2),
                        Section::make('Status dan Metadata')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        IconEntry::make('is_active')
                                            ->label('Aktif')
                                            ->boolean(),
                                        IconEntry::make('is_featured')
                                            ->label('Unggulan')
                                            ->boolean(),
                                    ]),
                                TextEntry::make('created_at')
                                    ->label('Dibuat Pada')
                                    ->dateTime('d M Y H:i'),
                                TextEntry::make('updated_at')
                                    ->label('Terakhir Diperbarui')
                                    ->dateTime('d M Y H:i'),
                            ])
                            ->columnSpan(1),
                    ]),

                Section::make('Paket Tur')
                    ->icon(Heroicon::OutlinedBriefcase)
                    ->schema([
                        RepeatableEntry::make('packages')
                            ->hiddenLabel()
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        SpatieMediaLibraryImageEntry::make('cover')
                                            ->label('Foto Utama')
                                            ->collection(TourPackage::MEDIA_COLLECTION_COVER)
                                            ->height(220)
                                            ->columnSpan(1),
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('name')
                                                    ->label('Nama Paket')
                                                    ->weight(FontWeight::Bold)
                                                    ->size(TextSize::Large)
                                                    ->columnSpanFull(),
                                                TextEntry::make('duration_days')
                                                    ->label('Durasi Hari')
                                                    ->suffix(' Hari'),
                                                TextEntry::make('duration_nights')
                                                    ->label('Durasi Malam')
                                                    ->suffix(' Malam'),
                                                TextEntry::make('slug')
                                                    ->label('Slug')
                                                    ->copyable()
                                                    ->columnSpanFull(),
                                            ])
                                            ->columnSpan(2),
                                    ]),
                                SpatieMediaLibraryImageEntry::make('gallery')
                                    ->label('Galeri')
                                    ->collection(TourPackage::MEDIA_COLLECTION_GALLERY)
                                    ->height(120)
                                    ->columnSpanFull(),

                                Section::make('Tier dan Harga')
                                    ->icon(Heroicon::OutlinedCurrencyDollar)
                                    ->schema([
                                        RepeatableEntry::make('tiers')
                                            ->hiddenLabel()
                                            ->schema([
                                                TextEntry::make('name')
                                                    ->label('Nama Tier')
                                                    ->weight(FontWeight::Bold),
                                                TextEntry::make('hotel_stars')
                                                    ->label('Bintang Hotel')
                                                    ->formatStateUsing(fn (?int $state): string => $state ? "{$state} Bintang" : '-'),
                                                RepeatableEntry::make('priceTiers')
                                                    ->label('Rentang Harga')
                                                    ->schema([
                                                        TextEntry::make('min_pax')
                                                            ->label('Minimal Pax'),
                                                        TextEntry::make('max_pax')
                                                            ->label('Maksimal Pax')
                                                            ->placeholder('Tanpa batas'),
                                                        TextEntry::make('price')
                                                            ->label('Harga per Pax')
                                                            ->money(fn (TourPriceTier $record): string => $record->currency, locale: 'id')
                                                            ->weight(FontWeight::Bold)
                                                            ->color('success'),
                                                    ])
                                                    ->columns(3)
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(2),
                                    ]),

                                Section::make('Itinerary')
                                    ->icon(Heroicon::OutlinedMap)
                                    ->schema([
                                        RepeatableEntry::make('itineraries')
                                            ->hiddenLabel()
                                            ->schema([
                                                TextEntry::make('day_number')
                                                    ->label('Hari')
                                                    ->badge()
                                                    ->formatStateUsing(fn (int $state): string => "Hari {$state}"),
                                                TextEntry::make('title')
                                                    ->label('Agenda')
                                                    ->weight(FontWeight::Bold),
                                                TextEntry::make('description')
                                                    ->label('Deskripsi')
                                                    ->html()
                                                    ->prose()
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(2),
                                    ]),

                                Section::make('Cakupan Paket')
                                    ->schema([
                                        RepeatableEntry::make('includes')
                                            ->hiddenLabel()
                                            ->schema([
                                                TextEntry::make('type')
                                                    ->label('Jenis')
                                                    ->badge()
                                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                                        'include' => 'Termasuk',
                                                        'exclude' => 'Tidak Termasuk',
                                                        'note' => 'Catatan',
                                                        default => $state,
                                                    })
                                                    ->color(fn (string $state): string => match ($state) {
                                                        'include' => 'success',
                                                        'exclude' => 'danger',
                                                        'note' => 'warning',
                                                        default => 'gray',
                                                    }),
                                                TextEntry::make('item')
                                                    ->label('Item'),
                                            ])
                                            ->columns(2),
                                    ]),
                            ])
                            ->columns(1),
                    ]),
            ])
            ->columns(1);
    }
}
