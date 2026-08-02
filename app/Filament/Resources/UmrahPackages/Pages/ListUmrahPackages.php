<?php

namespace App\Filament\Resources\UmrahPackages\Pages;

use App\Filament\Imports\UmrahPackageImporter;
use App\Filament\Resources\UmrahPackages\UmrahPackageResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListUmrahPackages extends ListRecords
{
    protected static string $resource = UmrahPackageResource::class;

    protected ?string $heading = 'Daftar Paket Umrah';

    protected ?string $subheading = 'Kelola semua penawaran paket perjalanan umrah.';

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->label('Import CSV')
                ->importer(UmrahPackageImporter::class)
                ->color('gray'),
            CreateAction::make()
                ->label('Tambah Paket Umrah')
                ->icon('lucide-plus'),
        ];
    }
}
