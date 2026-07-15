<?php

namespace App\Filament\Resources\Banners\Pages;

use App\Filament\Resources\Banners\BannerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageBanners extends ManageRecords
{
    protected static string $resource = BannerResource::class;

    protected ?string $heading = 'Daftar Banner Promosi';

    protected ?string $subheading = 'Kelola gambar slide banner promosi utama pada beranda website.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Banner')
                ->icon('lucide-plus')
                ->slideOver(),
        ];
    }
}
