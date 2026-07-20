<?php

namespace App\Filament\Resources\VehicleRentalTerms\Pages;

use App\Filament\Resources\VehicleRentalTerms\VehicleRentalTermResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditVehicleRentalTerm extends EditRecord
{
    protected static string $resource = VehicleRentalTermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
