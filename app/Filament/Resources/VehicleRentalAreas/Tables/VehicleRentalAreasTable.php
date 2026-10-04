<?php

namespace App\Filament\Resources\VehicleRentalAreas\Tables;

use App\Filament\Support\Tables\Presets\StatusColumnPreset;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class VehicleRentalAreasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Wilayah')->searchable()->sortable()->weight('bold'),
                TextColumn::make('minimum_rental_days')->label('Minimum')->suffix(' hari')->sortable(),
                TextColumn::make('rates_count')->label('Jumlah Tarif')->counts('rates')->sortable(),
                StatusColumnPreset::active('Aktif'),
                TextColumn::make('sort_order')->label('Urutan')->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}

