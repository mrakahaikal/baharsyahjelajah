<?php

namespace App\Filament\Resources\VisaServices\Schemas;

use App\Enums\VisaItemType;
use App\Models\VisaService;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VisaServiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->schema([
                    Section::make('Informasi Layanan')
                        ->icon('lucide-info')
                        ->schema([
                            SpatieMediaLibraryImageEntry::make('cover')
                                ->label('Cover')
                                ->collection(VisaService::MEDIA_COLLECTION_COVER)
                                ->height(280)
                                ->columnSpanFull(),
                            TextEntry::make('name')->label('Nama Layanan')->weight('bold'),
                            TextEntry::make('country.name')->label('Negara Tujuan')->badge(),
                            TextEntry::make('visa_type')->label('Jenis Visa'),
                            TextEntry::make('entry_type')->label('Tipe Masuk')->badge()->placeholder('-'),
                            TextEntry::make('summary')->label('Ringkasan')->columnSpanFull(),
                            TextEntry::make('description')->label('Deskripsi')->html()->prose()->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->columnSpan(2),
                    Section::make('Proses & Publikasi')
                        ->icon('lucide-settings')
                        ->schema([
                            TextEntry::make('price_idr')->label('Harga')->money('IDR', locale: 'id')->placeholder('Hubungi admin'),
                            TextEntry::make('processing_days_min')->label('Proses Minimum')->suffix(' hari')->placeholder('-'),
                            TextEntry::make('processing_days_max')->label('Proses Maksimum')->suffix(' hari')->placeholder('-'),
                            TextEntry::make('validity_days')->label('Masa Berlaku')->suffix(' hari')->placeholder('-'),
                            TextEntry::make('maximum_stay_days')->label('Maksimum Tinggal')->suffix(' hari')->placeholder('-'),
                            IconEntry::make('is_active')->label('Aktif')->boolean(),
                            IconEntry::make('is_featured')->label('Unggulan')->boolean(),
                        ])
                        ->columnSpan(1),
                    Section::make('Galeri')
                        ->icon('lucide-images')
                        ->schema([
                            SpatieMediaLibraryImageEntry::make('gallery')
                                ->hiddenLabel()
                                ->collection(VisaService::MEDIA_COLLECTION_GALLERY)
                                ->stacked()
                                ->limit(8)
                                ->limitedRemainingText(),
                        ])
                        ->columnSpanFull(),
                    Section::make('Persyaratan & Ketentuan')
                        ->icon('lucide-clipboard-check')
                        ->schema([
                            RepeatableEntry::make('items')
                                ->hiddenLabel()
                                ->schema([
                                    TextEntry::make('type')
                                        ->label('Kategori')
                                        ->badge()
                                        ->formatStateUsing(fn ($state): string => ($state instanceof VisaItemType ? $state : VisaItemType::tryFrom((string) $state))?->getLabel() ?? '-'),
                                    TextEntry::make('content')->label('Item')->weight('bold'),
                                    TextEntry::make('details')->label('Keterangan')->placeholder('-'),
                                    IconEntry::make('is_mandatory')->label('Wajib')->boolean(),
                                ])
                                ->columns(4),
                        ])
                        ->columnSpanFull(),
                ]),
            ])
            ->columns(1);
    }
}
