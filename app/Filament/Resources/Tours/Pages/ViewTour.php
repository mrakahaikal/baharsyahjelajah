<?php

namespace App\Filament\Resources\Tours\Pages;

use App\Filament\Resources\Tours\TourResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTour extends ViewRecord
{
    protected static string $resource = TourResource::class;

    protected ?string $heading = 'Detail Paket Tur';

    protected ?string $subheading = 'Lihat semua informasi lengkap mengenai paket perjalanan ini.';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Ubah Detail'),
        ];
    }
}
