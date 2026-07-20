<?php

namespace App\Filament\Resources\VehicleRentalAreas\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VehicleRentalAreaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Wilayah Sewa')->icon('lucide-map-pinned')->columnSpanFull()->schema([
                    TextEntry::make('name')->label('Nama')->weight('bold'),
                    TextEntry::make('slug')->label('Slug'),
                    TextEntry::make('description')->label('Deskripsi')->columnSpanFull(),
                    TextEntry::make('minimum_rental_days')->label('Minimum Sewa')->suffix(' hari'),
                    TextEntry::make('rates_count')->label('Jumlah Tarif')->state(fn ($record): int => $record->rates()->count()),
                    IconEntry::make('is_active')->label('Aktif')->boolean(),
                ])->columns(3),
            ]);
    }
}
