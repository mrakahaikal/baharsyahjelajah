<?php

namespace App\Filament\Resources\CurrencyRates\Schemas;

use App\Filament\Resources\CurrencyRates\CurrencyRateResource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CurrencyRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Detail Kurs Mata Uang')
                    ->description('Tentukan rasio konversi nilai tukar desimal antar mata uang.')
                    ->icon('lucide-banknote')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('from_currency')
                                ->label('Dari Mata Uang')
                                ->options(CurrencyRateResource::currencyOptions())
                                ->disabled()
                                ->required()
                                ->default(config('currencies.base'))
                                ->prefixIcon('lucide-globe')
                                ->native(false),
                            Select::make('to_currency')
                                ->label('Ke Mata Uang')
                                ->options(CurrencyRateResource::currencyOptions(includeBase: false))
                                ->disabled()
                                ->required()
                                ->prefixIcon('lucide-globe')
                                ->native(false),
                        ]),
                        TextInput::make('rate')
                            ->label('Nilai Kurs Konversi (Rate)')
                            ->placeholder('Contoh: 0.00026734')
                            ->helperText('Gunakan tanda titik (.) sebagai pemisah desimal.')
                            ->required()
                            ->numeric()
                            ->minValue(0.00000001)
                            ->step(0.00000001)
                            ->prefixIcon('lucide-calculator'),
                    ]),
            ]);
    }
}
