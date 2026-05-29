<?php

namespace App\Filament\Resources\CurrencyRates\Pages;

use App\Filament\Resources\CurrencyRates\CurrencyRateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCurrencyRates extends ManageRecords
{
    protected static string $resource = CurrencyRateResource::class;

    protected ?string $heading = 'Daftar Kurs Mata Uang';

    protected ?string $subheading = 'Kelola rasio nilai tukar mata uang untuk perhitungan harga otomatis.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Kurs Baru'),
        ];
    }
}
