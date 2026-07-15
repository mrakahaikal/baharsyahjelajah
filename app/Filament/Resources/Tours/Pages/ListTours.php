<?php

namespace App\Filament\Resources\Tours\Pages;

use App\Filament\Resources\Tours\TourResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTours extends ListRecords
{
    protected static string $resource = TourResource::class;

    protected ?string $heading = 'Daftar Paket Tur';

    protected ?string $subheading = 'Kelola semua paket perjalanan wisata yang tersedia.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Paket Tur')
                ->icon('lucide-plus'),
        ];
    }
}
