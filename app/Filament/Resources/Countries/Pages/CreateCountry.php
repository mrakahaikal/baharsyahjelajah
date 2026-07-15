<?php

namespace App\Filament\Resources\Countries\Pages;

use App\Filament\Resources\Countries\CountryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCountry extends CreateRecord
{
    protected static string $resource = CountryResource::class;

    protected ?string $heading = 'Tambah Negara Tujuan';

    protected ?string $subheading = 'Daftarkan negara baru agar dapat dipilih pada layanan Visa.';
}
