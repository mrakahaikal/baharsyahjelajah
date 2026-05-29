<?php

namespace App\Filament\Resources\TourCategories\Pages;

use App\Filament\Resources\TourCategories\TourCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTourCategories extends ManageRecords
{
    protected static string $resource = TourCategoryResource::class;

    protected ?string $heading = 'Daftar Kategori Tur';

    protected ?string $subheading = 'Kelola pengelompokan paket tur berdasarkan kategori.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Kategori'),
        ];
    }
}
