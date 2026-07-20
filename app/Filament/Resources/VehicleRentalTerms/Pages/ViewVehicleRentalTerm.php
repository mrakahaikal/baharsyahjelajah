<?php

namespace App\Filament\Resources\VehicleRentalTerms\Pages;

use App\Filament\Resources\VehicleRentalTerms\VehicleRentalTermResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewVehicleRentalTerm extends ViewRecord
{
    protected static string $resource = VehicleRentalTermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
