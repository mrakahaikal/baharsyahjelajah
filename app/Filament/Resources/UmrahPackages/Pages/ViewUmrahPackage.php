<?php

namespace App\Filament\Resources\UmrahPackages\Pages;

use App\Filament\Resources\UmrahPackages\UmrahPackageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUmrahPackage extends ViewRecord
{
    protected static string $resource = UmrahPackageResource::class;

    protected ?string $heading = 'Detail Paket Umrah';

    protected ?string $subheading = 'Informasi lengkap mengenai fasilitas, hotel, dan harga paket.';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Ubah Detail')
                ->icon('lucide-pencil')
                ->color('primary'),
        ];
    }
}
