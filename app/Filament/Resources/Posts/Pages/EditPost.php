<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected ?string $heading = 'Ubah Artikel';

    protected ?string $subheading = 'Perbarui detail informasi, isi, atau status publikasi artikel blog.';

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Lihat Detail')
                ->icon('lucide-eye'),
            DeleteAction::make()
                ->label('Hapus Artikel')
                ->icon('lucide-trash')
                ->color('danger'),
        ];
    }
}
