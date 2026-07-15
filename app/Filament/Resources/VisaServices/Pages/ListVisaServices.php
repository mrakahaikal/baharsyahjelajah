<?php

namespace App\Filament\Resources\VisaServices\Pages;

use App\Filament\Resources\VisaServices\VisaServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVisaServices extends ListRecords
{
    protected static string $resource = VisaServiceResource::class;

    protected ?string $heading = 'Layanan Visa';

    protected ?string $subheading = 'Kelola produk pengurusan Visa untuk pemegang paspor Indonesia.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Layanan Visa')->icon('lucide-plus'),
        ];
    }
}
