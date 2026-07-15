<?php

namespace App\Filament\Resources\WhatsappTemplates\Pages;

use App\Filament\Resources\WhatsappTemplates\WhatsappTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageWhatsappTemplates extends ManageRecords
{
    protected static string $resource = WhatsappTemplateResource::class;

    protected ?string $heading = 'Daftar Template WhatsApp';

    protected ?string $subheading = 'Kelola pesan otomatis WhatsApp yang dikirimkan saat pengguna memesan paket atau menyewa mobil.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Template')
                ->icon('lucide-plus')
                ->slideOver(),
        ];
    }
}
