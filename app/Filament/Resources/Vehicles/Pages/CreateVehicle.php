<?php

namespace App\Filament\Resources\Vehicles\Pages;

use App\Filament\Resources\Vehicles\VehicleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVehicle extends CreateRecord
{
    protected static string $resource = VehicleResource::class;

    protected ?string $heading = 'Tambah Kendaraan Baru';

    protected ?string $subheading = 'Daftarkan unit kendaraan baru ke dalam sistem.';
}
