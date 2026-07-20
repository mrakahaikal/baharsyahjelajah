<?php

namespace App\Filament\Resources\VehicleRentalTerms\Pages;

use App\Filament\Resources\VehicleRentalTerms\VehicleRentalTermResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVehicleRentalTerms extends ListRecords
{
    protected static string $resource = VehicleRentalTermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
