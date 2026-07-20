<?php

namespace App\Filament\Resources\VehicleRentalAreas\Pages;

use App\Filament\Resources\VehicleRentalAreas\VehicleRentalAreaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVehicleRentalAreas extends ListRecords
{
    protected static string $resource = VehicleRentalAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
