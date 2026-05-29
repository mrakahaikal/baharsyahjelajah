<?php

namespace App\Filament\Resources\Vehicles\Pages;

use App\Filament\Resources\Vehicles\VehicleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVehicles extends ListRecords
{
    protected static string $resource = VehicleResource::class;

    protected ?string $heading = 'Daftar Armada Kendaraan';

    protected ?string $subheading = 'Kelola semua unit kendaraan yang tersedia untuk disewakan.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Kendaraan'),
        ];
    }
}
