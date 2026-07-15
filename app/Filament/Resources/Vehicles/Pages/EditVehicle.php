<?php

namespace App\Filament\Resources\Vehicles\Pages;

use App\Filament\Resources\Vehicles\VehicleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditVehicle extends EditRecord
{
    protected static string $resource = VehicleResource::class;

    protected ?string $heading = 'Ubah Detail Kendaraan';

    protected ?string $subheading = 'Perbarui spesifikasi, harga, atau ketersediaan unit kendaraan.';

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Lihat Detail')
                ->icon('lucide-eye'),
            DeleteAction::make()
                ->label('Hapus Unit')
                ->icon('lucide-trash')
                ->color('danger'),
            ForceDeleteAction::make()
                ->label('Hapus Permanen')
                ->icon('lucide-trash-2')
                ->color('danger'),
            RestoreAction::make()
                ->label('Pulihkan')
                ->icon('lucide-rotate-ccw')
                ->color('success'),
        ];
    }
}
