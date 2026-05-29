<?php

namespace App\Filament\Resources\UmrahPackages\Pages;

use App\Filament\Resources\UmrahPackages\UmrahPackageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUmrahPackage extends CreateRecord
{
    protected static string $resource = UmrahPackageResource::class;

    protected ?string $heading = 'Buat Paket Umrah Baru';

    protected ?string $subheading = 'Lengkapi detail penawaran paket umrah baru di bawah ini.';
}
