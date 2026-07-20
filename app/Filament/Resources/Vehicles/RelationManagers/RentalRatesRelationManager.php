<?php

namespace App\Filament\Resources\Vehicles\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RentalRatesRelationManager extends RelationManager
{
    protected static string $relationship = 'rentalRates';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('vehicle_rental_area_id')
                    ->label('Wilayah')
                    ->relationship('area', 'name', modifyQueryUsing: fn ($query) => $query->active()->orderBy('sort_order'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false),
                TextInput::make('price_per_day_idr')
                    ->label('Tarif per Hari')
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(1)
                    ->required()
                    ->maxLength(14),
                DatePicker::make('valid_from')->label('Berlaku Mulai')->required()->native(false),
                DatePicker::make('valid_until')->label('Berlaku Sampai')->afterOrEqual('valid_from')->native(false),
                Toggle::make('is_active')->label('Tarif Aktif')->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('area.name')
            ->columns([
                TextColumn::make('area.name')->label('Wilayah')->searchable()->sortable()->weight('bold'),
                TextColumn::make('price_per_day_idr')->label('Tarif / Hari')->money('IDR', locale: 'id')->sortable(),
                TextColumn::make('area.minimum_rental_days')->label('Minimum')->suffix(' hari'),
                TextColumn::make('valid_from')->label('Mulai')->date('d M Y')->sortable(),
                TextColumn::make('valid_until')->label('Selesai')->date('d M Y')->placeholder('Tanpa batas'),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->filters([
                SelectFilter::make('vehicle_rental_area_id')->label('Wilayah')->relationship('area', 'name'),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Tarif'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
