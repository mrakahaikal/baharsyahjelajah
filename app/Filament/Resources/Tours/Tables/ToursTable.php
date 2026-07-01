<?php

namespace App\Filament\Resources\Tours\Tables;

use App\Services\CurrencyService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ToursTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Foto')
                    ->square(),
                TextColumn::make('name')
                    ->label('Nama Tur')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tour_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'Open Trip',
                        'private' => 'Private Trip',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'info',
                        'private' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('price')
                    ->label('Harga')
                    ->formatStateUsing(fn ($record): string => app(CurrencyService::class)->convert($record->price, $record->currency, $record->currency))
                    ->sortable(),
                TextColumn::make('duration_days')
                    ->label('Durasi')
                    ->state(fn ($record): string => "{$record->duration_days}H {$record->duration_nights}M")
                    ->sortable(['duration_days', 'duration_nights']),
                TextColumn::make('difficulty')
                    ->label('Kesulitan')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'easy' => 'Mudah',
                        'moderate' => 'Sedang',
                        'hard' => 'Sulit',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'easy' => 'success',
                        'moderate' => 'warning',
                        'hard' => 'danger',
                        default => 'gray',
                    }),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tour_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('tour_type')
                    ->label('Tipe Tur')
                    ->options([
                        'open' => 'Open Trip',
                        'private' => 'Private Trip',
                    ]),
                SelectFilter::make('difficulty')
                    ->label('Tingkat Kesulitan')
                    ->options([
                        'easy' => 'Mudah',
                        'moderate' => 'Sedang',
                        'hard' => 'Sulit',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat'),
                EditAction::make()
                    ->label('Ubah'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ]);
    }
}
