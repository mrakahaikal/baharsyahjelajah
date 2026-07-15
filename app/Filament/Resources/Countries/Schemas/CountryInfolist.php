<?php

namespace App\Filament\Resources\Countries\Schemas;

use App\Models\Country;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CountryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->schema([
                    Section::make('Negara Tujuan')
                        ->icon('lucide-globe-2')
                        ->schema([
                            SpatieMediaLibraryImageEntry::make('flag')
                                ->label('Bendera')
                                ->collection(Country::MEDIA_COLLECTION_FLAG)
                                ->height(160)
                                ->columnSpanFull(),
                            TextEntry::make('name')->label('Nama Negara')->weight('bold'),
                            TextEntry::make('slug')->label('Slug URL'),
                            TextEntry::make('iso_alpha_2')->label('ISO Alpha-2')->badge(),
                            TextEntry::make('iso_alpha_3')->label('ISO Alpha-3')->placeholder('-'),
                        ])
                        ->columns(2)
                        ->columnSpan(2),
                    Section::make('Status')
                        ->icon('lucide-settings')
                        ->schema([
                            IconEntry::make('is_active')->label('Aktif')->boolean(),
                            TextEntry::make('sort_order')->label('Urutan Tampilan'),
                            TextEntry::make('visa_services_count')
                                ->label('Jumlah Layanan Visa')
                                ->state(fn (Country $record): int => $record->visaServices()->withTrashed()->count())
                                ->badge(),
                            TextEntry::make('updated_at')->label('Diperbarui')->dateTime('d M Y H:i'),
                        ])
                        ->columnSpan(1),
                ]),
            ])
            ->columns(1);
    }
}
