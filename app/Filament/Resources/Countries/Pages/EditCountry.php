<?php

namespace App\Filament\Resources\Countries\Pages;

use App\Filament\Resources\Countries\CountryResource;
use App\Models\Country;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCountry extends EditRecord
{
    protected static string $resource = CountryResource::class;

    protected ?string $heading = 'Ubah Negara Tujuan';

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->label('Lihat Detail')->icon('lucide-eye'),
            DeleteAction::make()->label('Hapus')->icon('lucide-trash'),
            ForceDeleteAction::make()
                ->label('Hapus Permanen')
                ->icon('lucide-trash-2')
                ->disabled(fn (Country $record): bool => $record->visaServices()->withTrashed()->exists())
                ->tooltip('Negara yang masih dipakai layanan Visa tidak dapat dihapus permanen.'),
            RestoreAction::make()->label('Pulihkan')->icon('lucide-rotate-ccw'),
        ];
    }
}
