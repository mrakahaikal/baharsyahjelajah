<?php

namespace App\Filament\Resources\UmrahPackages\Pages;

use App\Filament\Resources\UmrahPackages\UmrahPackageResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUmrahPackage extends EditRecord
{
    protected static string $resource = UmrahPackageResource::class;

    protected ?string $heading = 'Ubah Paket Umrah';

    protected ?string $subheading = 'Perbarui informasi fasilitas, hotel, atau harga paket umrah.';

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Lihat Detail'),
            DeleteAction::make()
                ->label('Hapus'),
            ForceDeleteAction::make()
                ->label('Hapus Permanen'),
            RestoreAction::make()
                ->label('Pulihkan'),
        ];
    }
}
