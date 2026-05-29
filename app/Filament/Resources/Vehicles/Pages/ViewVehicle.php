<?php

namespace App\Filament\Resources\Vehicles\Pages;

use App\Filament\Resources\Vehicles\VehicleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewVehicle extends ViewRecord
{
    protected static string $resource = VehicleResource::class;

    protected ?string $heading = 'Detail Kendaraan';

    protected ?string $subheading = 'Lihat spesifikasi teknis dan informasi lengkap kendaraan.';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Ubah Detail'),
        ];
    }
}
