<?php

namespace App\Filament\Resources\PostCategories\Pages;

use App\Filament\Resources\PostCategories\PostCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePostCategories extends ManageRecords
{
    protected static string $resource = PostCategoryResource::class;

    protected ?string $heading = 'Daftar Kategori Artikel';

    protected ?string $subheading = 'Kelola kategori pengelompokan artikel untuk blog website.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Kategori')
                ->icon('lucide-plus'),
        ];
    }
}
