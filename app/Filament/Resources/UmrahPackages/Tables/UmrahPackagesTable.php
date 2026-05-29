<?php

namespace App\Filament\Resources\UmrahPackages\Tables;

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

class UmrahPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Foto')
                    ->square()
                    ->size(60),
                TextColumn::make('name')
                    ->label('Nama Paket')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),
                TextColumn::make('package_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'regular' => 'Regular',
                        'plus' => 'Plus',
                        'vip' => 'VIP',
                        'ramadan' => 'Ramadan',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'regular' => 'gray',
                        'plus' => 'info',
                        'vip' => 'warning',
                        'ramadan' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('duration_days')
                    ->label('Durasi')
                    ->formatStateUsing(fn (int $state): string => "{$state} Hari")
                    ->sortable(),
                TextColumn::make('price_idr')
                    ->label('Harga Dasar')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('airline')
                    ->label('Maskapai')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('visa_included')
                    ->label('Visa')
                    ->boolean()
                    ->alignCenter(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('package_type')
                    ->label('Tipe Paket')
                    ->options([
                        'regular' => 'Regular',
                        'plus' => 'Plus',
                        'vip' => 'VIP',
                        'ramadan' => 'Ramadan',
                    ]),
                SelectFilter::make('is_active')
                    ->label('Status Aktif')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Non-Aktif',
                    ]),
                TrashedFilter::make()
                    ->label('Sampah'),
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
