<?php

namespace App\Filament\Resources\Countries\Pages;

use App\Filament\Resources\Countries\CountryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCountries extends ListRecords
{
    protected static string $resource = CountryResource::class;

    protected ?string $heading = 'Negara Tujuan Visa';

    protected ?string $subheading = 'Kelola daftar negara yang tersedia untuk layanan pengurusan Visa.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Negara')->icon('lucide-plus'),
        ];
    }
}
