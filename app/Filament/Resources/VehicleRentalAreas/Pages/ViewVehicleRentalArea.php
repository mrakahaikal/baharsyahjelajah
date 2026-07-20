<?php

namespace App\Filament\Resources\VehicleRentalAreas\Pages;

use App\Filament\Resources\VehicleRentalAreas\VehicleRentalAreaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewVehicleRentalArea extends ViewRecord
{
    protected static string $resource = VehicleRentalAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
