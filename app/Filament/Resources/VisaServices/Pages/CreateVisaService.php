<?php

namespace App\Filament\Resources\VisaServices\Pages;

use App\Filament\Resources\VisaServices\VisaServiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVisaService extends CreateRecord
{
    protected static string $resource = VisaServiceResource::class;

    protected ?string $heading = 'Tambah Layanan Visa';

    protected ?string $subheading = 'Daftarkan satu jenis layanan Visa untuk negara tujuan tertentu.';

    /** @param array<string, mixed> $data */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (array_key_exists('price_idr', $data) && ! array_key_exists('price', $data)) {
            $data['price'] = $data['price_idr'];
            $data['currency'] ??= 'IDR';
        }

        return $data;
    }
}
