<?php

namespace App\Filament\Resources\Destinations\Pages;

use App\Filament\Resources\Destinations\DestinationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDestinations extends ManageRecords
{
    protected static string $resource = DestinationResource::class;

    protected ?string $heading = 'Daftar Destinasi Wisata';

    protected ?string $subheading = 'Kelola destinasi wisata yang dapat dihubungkan ke itinerary paket tur.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Destinasi')
                ->icon('lucide-plus')
                ->slideOver(),
        ];
    }
}
