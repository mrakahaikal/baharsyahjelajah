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
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class TourInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make('Informasi Utama Tur')
                            ->description('Detail dasar mengenai destinasi, kategori, tipe perjalanan, dan deskripsi tur.')
                            ->icon('lucide-info')
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
                                    ->label('Tipe Perjalanan')
                                    ->badge()
                                    ->formatStateUsing(fn (TourType $state): string => $state->getLabel())
                                    ->color(fn (TourType $state): string => match ($state) {
                                        TourType::Domestic => 'success',
                                        TourType::International => 'info',
                                    }),
                                TextEntry::make('currency')
                                    ->label('Mata Uang Utama')
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
                        Section::make('Status & Metadata')
                            ->description('Informasi publikasi, tanggal pembuatan, dan pembaruan terakhir.')
                            ->icon('lucide-settings')
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
                                    ->label('Tanggal Pembuatan')
                                    ->dateTime('d M Y H:i'),
                                TextEntry::make('updated_at')
                                    ->label('Pembaruan Terakhir')
                                    ->dateTime('d M Y H:i'),
                            ])
                            ->columnSpan(1),
                    ]),

                Section::make('Kelola Paket Perjalanan')
                    ->description('Daftar paket wisata lengkap dengan durasi, media, itinerary, fasilitas, dan harga.')
                    ->icon('lucide-package')
                    ->schema([
                        RepeatableEntry::make('packages')
                            ->hiddenLabel()
                            ->schema([
                                Tabs::make('package_tabs')
                                    ->tabs([
                                        Tab::make('Detail & Durasi')
                                            ->icon('lucide-info')
                                            ->schema([
                                                Section::make('Detail Utama Paket')
                                                    ->description('Nama paket tur, tautan URL, dan durasi perjalanan.')
                                                    ->icon('lucide-file-text')
                                                    ->schema([
                                                        Grid::make(3)
                                                            ->schema([
                                                                TextEntry::make('name')
                                                                    ->label('Nama Paket')
                                                                    ->weight(FontWeight::Bold)
                                                                    ->size(TextSize::Large)
                                                                    ->columnSpan(2),
                                                                TextEntry::make('slug')
                                                                    ->label('Slug URL')
                                                                    ->copyable()
                                                                    ->columnSpan(1),
                                                            ]),
                                                        Grid::make(2)
                                                            ->schema([
                                                                TextEntry::make('duration_days')
                                                                    ->label('Durasi Hari')
                                                                    ->suffix(' Hari'),
                                                                TextEntry::make('duration_nights')
                                                                    ->label('Durasi Malam')
                                                                    ->suffix(' Malam'),
                                                            ]),
                                                    ]),
                                            ]),

                                        Tab::make('Media')
                                            ->icon('lucide-image')
                                            ->schema([
                                                Section::make('Media Visual Paket')
                                                    ->description('Foto utama dan galeri pendukung untuk paket tur ini.')
                                                    ->icon('lucide-images')
                                                    ->schema([
                                                        SpatieMediaLibraryImageEntry::make('cover')
                                                            ->label('Foto Utama (Cover)')
                                                            ->collection(TourPackage::MEDIA_COLLECTION_COVER)
                                                            ->height(220)
                                                            ->columnSpanFull(),
                                                        SpatieMediaLibraryImageEntry::make('gallery')
                                                            ->label('Galeri Foto Pendukung')
                                                            ->collection(TourPackage::MEDIA_COLLECTION_GALLERY)
                                                            ->height(120)
                                                            ->columnSpanFull(),
                                                    ]),
                                            ]),

                                        Tab::make('Itinerary')
                                            ->icon('lucide-route')
                                            ->schema([
                                                Section::make('Rencana Perjalanan Harian')
                                                    ->description('Jadwal rincian aktivitas dan destinasi wisata yang dikunjungi.')
                                                    ->icon('lucide-map')
                                                    ->schema([
                                                        RepeatableEntry::make('itineraries')
                                                            ->hiddenLabel()
                                                            ->schema([
                                                                TextEntry::make('day_number')
                                                                    ->label('Hari')
                                                                    ->badge()
                                                                    ->formatStateUsing(fn (int $state): string => "Hari {$state}"),
                                                                TextEntry::make('title')
                                                                    ->label('Agenda Kegiatan')
                                                                    ->weight(FontWeight::Bold),
                                                                TextEntry::make('description')
                                                                    ->label('Deskripsi Kegiatan')
                                                                    ->html()
                                                                    ->prose()
                                                                    ->columnSpanFull(),
                                                                TextEntry::make('destinations.name')
                                                                    ->label('Destinasi Wisata')
                                                                    ->badge()
                                                                    ->color('info')
                                                                    ->columnSpanFull(),
                                                            ])
                                                            ->columns(2),
                                                    ]),
                                            ]),

                                        Tab::make('Fasilitas')
                                            ->icon('lucide-list-checks')
                                            ->schema([
                                                Section::make('Fasilitas & Cakupan Layanan')
                                                    ->description('Daftar fasilitas yang termasuk, tidak termasuk, serta catatan khusus.')
                                                    ->icon('lucide-clipboard-check')
                                                    ->schema([
                                                        RepeatableEntry::make('includes')
                                                            ->hiddenLabel()
                                                            ->schema([
                                                                TextEntry::make('type')
                                                                    ->label('Kategori')
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
                                                                    ->label('Nama Fasilitas / Item'),
                                                            ])
                                                            ->columns(2),
                                                    ]),
                                            ]),

                                        Tab::make('Tiers & Harga')
                                            ->icon('lucide-banknote')
                                            ->schema([
                                                Section::make('Tier Kualitas & Struktur Harga')
                                                    ->description('Detail klasifikasi kualitas hotel dan aturan harga per pax berdasarkan jumlah peserta.')
                                                    ->icon('lucide-trending-up')
                                                    ->schema([
                                                        RepeatableEntry::make('tiers')
                                                            ->hiddenLabel()
                                                            ->schema([
                                                                TextEntry::make('name')
                                                                    ->label('Nama Tier / Paket Kelas')
                                                                    ->weight(FontWeight::Bold),
                                                                TextEntry::make('hotel_stars')
                                                                    ->label('Standar Bintang Hotel')
                                                                    ->formatStateUsing(fn (?int $state): string => $state ? "{$state} Bintang" : 'Tanpa Hotel'),
                                                                RepeatableEntry::make('priceTiers')
                                                                    ->label('Struktur Harga Berdasarkan Pax')
                                                                    ->schema([
                                                                        TextEntry::make('min_pax')
                                                                            ->label('Min Peserta'),
                                                                        TextEntry::make('max_pax')
                                                                            ->label('Max Peserta')
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
                                            ]),
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->columns(1),
                    ]),
            ])
            ->columns(1);
    }
}
