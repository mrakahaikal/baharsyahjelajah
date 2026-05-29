<?php

namespace App\Filament\Resources\CurrencyRates;

use App\Filament\Resources\CurrencyRates\Pages\ManageCurrencyRates;
use App\Models\CurrencyRate;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class CurrencyRateResource extends Resource
{
    protected static ?string $model = CurrencyRate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|null|\UnitEnum $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Kurs Mata Uang';

    protected static ?string $modelLabel = 'Kurs Mata Uang';

    protected static ?string $pluralModelLabel = 'Kurs Mata Uang';

    protected static ?string $recordTitleAttribute = 'to_currency';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Detail Kurs Mata Uang')
                    ->description('Tentukan rasio konversi antar mata uang.')
                    ->icon(Heroicon::OutlinedCurrencyDollar)
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('from_currency')
                                ->label('Dari Mata Uang')
                                ->options([
                                    'IDR' => 'IDR - Indonesian Rupiah',
                                    'MYR' => 'MYR - Malaysian Ringgit',
                                    'SGD' => 'SGD - Singapore Dollar',
                                ])
                                ->required()
                                ->default('IDR'),
                            Select::make('to_currency')
                                ->label('Ke Mata Uang')
                                ->options([
                                    'IDR' => 'IDR - Indonesian Rupiah',
                                    'MYR' => 'MYR - Malaysian Ringgit',
                                    'SGD' => 'SGD - Singapore Dollar',
                                ])
                                ->required(),
                        ]),
                        TextInput::make('rate')
                            ->label('Nilai Tukar (Rate)')
                            ->placeholder('Misal: 0.00029')
                            ->helperText('Gunakan titik (.) sebagai pemisah desimal.')
                            ->required()
                            ->numeric()
                            ->step(0.00000001),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('to_currency')
            ->columns([
                TextColumn::make('from_currency')
                    ->label('Dari')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('to_currency')
                    ->label('Ke')
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                TextColumn::make('rate')
                    ->label('Nilai Kurs')
                    ->numeric(decimalPlaces: 8)
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Ubah'),
                DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCurrencyRates::route('/'),
        ];
    }
}
