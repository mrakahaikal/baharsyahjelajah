<?php

namespace App\Filament\Resources\VisaServices\Pages;

use App\Filament\Resources\VisaServices\VisaServiceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewVisaService extends ViewRecord
{
    protected static string $resource = VisaServiceResource::class;

    protected ?string $heading = 'Detail Layanan Visa';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label('Ubah Detail')->icon('lucide-pencil'),
        ];
    }
}
