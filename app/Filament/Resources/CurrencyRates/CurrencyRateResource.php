<?php

namespace App\Filament\Resources\CurrencyRates;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Filament\Resources\CurrencyRates\Pages\ManageCurrencyRates;
use App\Models\CurrencyRate;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
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
                                ->options(self::currencyOptions())
                                ->disabled()
                                ->required()
                                ->default(config('currencies.base'))
                                ->prefixIcon('lucide-globe')
                                ->native(false),
                            Select::make('to_currency')
                                ->label('Ke Mata Uang')
                                ->options(self::currencyOptions(includeBase: false))
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

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('to_currency')
            ->columns([
                TextColumn::make('from_currency')
                    ->label('Mata Uang Asal')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('to_currency')
                    ->label('Mata Uang Tujuan')
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                TextColumn::make('rate')
                    ->label('Nilai Kurs')
                    ->numeric(decimalPlaces: 8)
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('provider')
                    ->label('Sumber Data')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === 'manual' ? 'Manual' : 'ExchangeRate-API')
                    ->color(fn (?string $state): string => $state === 'manual' ? 'warning' : 'info'),
                TextColumn::make('source_updated_at')
                    ->label('Waktu Data')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Input Manual')
                    ->sortable(),
                TextColumn::make('fetched_at')
                    ->label('Pembaruan Terakhir')
                    ->dateTime('d M Y H:i')
                    ->badge()
                    ->color(fn (CurrencyRate $record): string => $record->isStale() ? 'danger' : 'success')
                    ->formatStateUsing(fn (?string $state, CurrencyRate $record): string => $record->isStale()
                        ? (($record->fetched_at?->format('d M Y H:i') ?? 'Belum pernah').' - kedaluwarsa')
                        : ($record->fetched_at?->format('d M Y H:i') ?? 'Belum pernah'))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Ubah')
                    ->icon('lucide-pencil')
                    ->color('primary')
                    ->mutateDataUsing(fn (array $data): array => [
                        ...$data,
                        'provider' => 'manual',
                        'source_updated_at' => null,
                        'fetched_at' => now(),
                    ]),
            ])
            ->emptyStateHeading('Belum Ada Kurs Mata Uang')
            ->emptyStateDescription('Kurs mata uang asing belum terdaftar. Silakan klik tombol "Sinkronkan Kurs" untuk memuat data terbaru.')
            ->emptyStateIcon('lucide-banknote');
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
