<?php

namespace App\Filament\Resources\Vehicles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class VehiclesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Foto')
                    ->square()
                    ->imageSize(60),
                TextColumn::make('name')
                    ->label('Nama Unit')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('brand')
                    ->label('Merek')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('capacity_pax')
                    ->label('Kapasitas')
                    ->formatStateUsing(fn (int $state): string => "{$state} Penumpang")
                    ->sortable(),
                TextColumn::make('transmission')
                    ->label('Transmisi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'automatic' => 'Otomatis',
                        'manual' => 'Manual',
                        default => $state,
                    })
                    ->color('info'),
                TextColumn::make('price_per_day_idr')
                    ->label('Sewa / Hari')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                IconColumn::make('is_available')
                    ->label('Tersedia')
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label('Ditambahkan')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('transmission')
                    ->label('Transmisi')
                    ->options([
                        'automatic' => 'Otomatis',
                        'manual' => 'Manual',
                    ]),
                SelectFilter::make('is_available')
                    ->label('Status Ketersediaan')
                    ->options([
                        '1' => 'Tersedia',
                        '0' => 'Tidak Tersedia',
                    ]),
                TrashedFilter::make('deleted_at')
                    ->label('Data Terhapus')
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
                    ForceDeleteBulkAction::make()
                        ->label('Hapus Permanen'),
                    RestoreBulkAction::make()
                        ->label('Pulihkan'),
                ]),
            ]);
    }
}
