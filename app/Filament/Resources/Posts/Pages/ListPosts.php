<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected ?string $heading = 'Daftar Artikel Blog';

    protected ?string $subheading = 'Kelola penulisan dan publikasi artikel/blog website.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tulis Artikel Baru')
                ->icon('lucide-plus'),
        ];
    }
}
