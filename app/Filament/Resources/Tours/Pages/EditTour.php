<?php

namespace App\Filament\Resources\Tours\Pages;

use App\Filament\Resources\Tours\TourResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTour extends EditRecord
{
    protected static string $resource = TourResource::class;

    protected ?string $heading = 'Ubah Paket Tur';

    protected ?string $subheading = 'Perbarui detail informasi paket tur yang sudah ada.';

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Lihat Detail')
                ->icon('lucide-eye'),
            DeleteAction::make()
                ->label('Hapus Paket')
                ->icon('lucide-trash')
                ->color('danger'),
        ];
    }
}
