<?php

namespace App\Filament\Resources\Vehicles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
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
                SpatieMediaLibraryImageColumn::make('cover')
                    ->label('Foto Utama')
                    ->collection('cover')
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
                    ->label('Kapasitas Penumpang')
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
                    ->label('Tarif Sewa / Hari')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean()
                    ->alignCenter(),
                IconColumn::make('is_active')
                    ->label('Katalog')
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label('Tanggal Ditambahkan')
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
                    ])
                    ->native(false),
                SelectFilter::make('is_active')
                    ->label('Status Katalog')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Nonaktif',
                    ])
                    ->native(false),
                TrashedFilter::make('deleted_at')
                    ->label('Status Penghapusan')
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat')
                    ->icon('lucide-eye'),
                EditAction::make()
                    ->label('Ubah')
                    ->icon('lucide-pencil')
                    ->color('primary'),
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
            ])
            ->emptyStateHeading('Belum Ada Armada Kendaraan')
            ->emptyStateDescription('Daftarkan armada kendaraan baru untuk disewakan kepada pelanggan.')
            ->emptyStateIcon('lucide-truck')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Tambah Kendaraan')
                    ->icon('lucide-plus'),
            ]);
    }
}
