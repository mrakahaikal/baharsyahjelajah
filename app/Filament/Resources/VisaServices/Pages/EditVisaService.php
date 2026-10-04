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

    /** @param array<string, mixed> $data */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['currency'] ??= 'IDR';

        if (! isset($data['price']) && isset($data['price_idr'])) {
            $data['price'] = $data['price_idr'];
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (array_key_exists('price_idr', $data) && ! array_key_exists('price', $data)) {
            $data['price'] = $data['price_idr'];
            $data['currency'] ??= 'IDR';
        }

        return $data;
    }
}
