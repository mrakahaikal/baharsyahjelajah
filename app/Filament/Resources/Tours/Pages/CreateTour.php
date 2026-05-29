<?php

namespace App\Filament\Resources\Tours\Pages;

use App\Filament\Resources\Tours\TourResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTour extends CreateRecord
{
    protected static string $resource = TourResource::class;

    protected ?string $heading = 'Buat Paket Tur Baru';

    protected ?string $subheading = 'Lengkapi formulir di bawah untuk menambahkan paket tur ke sistem.';
}
