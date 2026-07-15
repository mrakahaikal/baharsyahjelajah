<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPost extends ViewRecord
{
    protected static string $resource = PostResource::class;

    protected ?string $heading = 'Detail Artikel';

    protected ?string $subheading = 'Lihat pratinjau lengkap isi konten, status, dan riwayat publikasi artikel blog ini.';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Ubah Detail')
                ->icon('lucide-pencil')
                ->color('primary'),
        ];
    }
}
