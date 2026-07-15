<?php

namespace App\Filament\Resources\VisaServices\Pages;

use App\Filament\Resources\VisaServices\VisaServiceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditVisaService extends EditRecord
{
    protected static string $resource = VisaServiceResource::class;

    protected ?string $heading = 'Ubah Layanan Visa';

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->label('Lihat Detail')->icon('lucide-eye'),
            DeleteAction::make()->label('Hapus')->icon('lucide-trash'),
            ForceDeleteAction::make()->label('Hapus Permanen')->icon('lucide-trash-2'),
            RestoreAction::make()->label('Pulihkan')->icon('lucide-rotate-ccw'),
        ];
    }
}
