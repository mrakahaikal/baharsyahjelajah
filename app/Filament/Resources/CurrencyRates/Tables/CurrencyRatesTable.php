<?php

namespace App\Filament\Resources\CurrencyRates\Tables;

use App\Models\CurrencyRate;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CurrencyRatesTable
{
    public static function configure(Table $table): Table
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
}
