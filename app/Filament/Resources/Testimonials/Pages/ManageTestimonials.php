<?php

namespace App\Filament\Resources\Testimonials\Pages;

use App\Filament\Resources\Testimonials\TestimonialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTestimonials extends ManageRecords
{
    protected static string $resource = TestimonialResource::class;

    protected ?string $heading = 'Daftar Testimoni Pelanggan';

    protected ?string $subheading = 'Kelola ulasan, rating, dan testimoni pelanggan untuk ditampilkan di website.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Testimoni')
                ->icon('lucide-plus')
                ->slideOver(),
        ];
    }
}
