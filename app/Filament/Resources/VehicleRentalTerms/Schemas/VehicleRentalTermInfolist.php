<?php

namespace App\Filament\Resources\VehicleRentalTerms\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VehicleRentalTermInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ketentuan Sewa')->icon('lucide-clipboard-check')->columnSpanFull()->schema([
                    TextEntry::make('title')->label('Judul')->weight('bold'),
                    TextEntry::make('code')->label('Kode'),
                    TextEntry::make('type')->label('Jenis')->badge(),
                    TextEntry::make('vehicle_category')->label('Kategori')->badge()->placeholder('Semua'),
                    TextEntry::make('content')->label('Isi')->html()->columnSpanFull(),
                    TextEntry::make('sort_order')->label('Urutan'),
                    IconEntry::make('is_active')->label('Aktif')->boolean(),
                ])->columns(3),
            ]);
    }
}
