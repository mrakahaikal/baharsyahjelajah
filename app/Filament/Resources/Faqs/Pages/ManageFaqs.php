<?php

namespace App\Filament\Resources\Faqs\Pages;

use App\Filament\Resources\Faqs\FaqResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFaqs extends ManageRecords
{
    protected static string $resource = FaqResource::class;

    protected ?string $heading = 'Daftar Pertanyaan (FAQ)';

    protected ?string $subheading = 'Kelola pertanyaan dan jawaban yang sering ditanyakan pelanggan.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah FAQ')
                ->icon('lucide-plus'),
        ];
    }
}
