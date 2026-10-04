<?php

namespace App\Filament\Resources\CurrencyRates;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Filament\Resources\CurrencyRates\Pages\ManageCurrencyRates;
use App\Filament\Resources\CurrencyRates\Schemas\CurrencyRateForm;
use App\Filament\Resources\CurrencyRates\Tables\CurrencyRatesTable;
use App\Models\CurrencyRate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CurrencyRateResource extends Resource
{
    protected static ?string $model = CurrencyRate::class;

    protected static string|BackedEnum|null $navigationIcon = 'lucide-banknote';

    protected static ?string $navigationLabel = 'Kurs Mata Uang';

    protected static ?string $modelLabel = 'Kurs Mata Uang';

    protected static ?string $pluralModelLabel = 'Kurs Mata Uang';

    protected static ?string $recordTitleAttribute = 'to_currency';

    protected static ?int $navigationSort = 5;

    protected static ?string $cluster = SettingsCluster::class;

    public static function form(Schema $schema): Schema
    {
        return CurrencyRateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CurrencyRatesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $baseCurrency = (string) config('currencies.base');

        return parent::getEloquentQuery()
            ->where('from_currency', $baseCurrency)
            ->whereIn('to_currency', array_keys(self::currencyOptions(includeBase: false)));
    }

    /**
     * @return array<string, string>
     */
    public static function currencyOptions(bool $includeBase = true): array
    {
        $baseCurrency = (string) config('currencies.base');

        return collect(config('currencies.supported', []))
            ->reject(fn (array $metadata, string $code): bool => ! $includeBase && $code === $baseCurrency)
            ->mapWithKeys(fn (array $metadata, string $code): array => [
                $code => "{$code} - {$metadata['name']}",
            ])
            ->all();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCurrencyRates::route('/'),
        ];
    }
}
