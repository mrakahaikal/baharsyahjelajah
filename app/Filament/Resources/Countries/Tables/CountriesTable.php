<?php

namespace App\Filament\Resources\Countries\Tables;

use App\Filament\Support\Tables\Presets\StatusColumnPreset;
use App\Filament\Support\Tables\Presets\TimestampColumnPreset;
use App\Models\Country;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CountriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                SpatieMediaLibraryImageColumn::make('flag')
                    ->label('Bendera')
                    ->collection(Country::MEDIA_COLLECTION_FLAG)
                    ->size(40),
                SpatieMediaLibraryImageColumn::make('cover')
                    ->label('Sampul')
                    ->collection(Country::MEDIA_COLLECTION_COVER)
                    ->height(40)
                    ->width(70),
                TextColumn::make('name')
                    ->label('Negara')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('iso_alpha_2')
                    ->label('ISO-2')
                    ->badge()
                    ->searchable(),
                TextColumn::make('iso_alpha_3')
                    ->label('ISO-3')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('visa_services_count')
                    ->label('Layanan Visa')
                    ->counts('visaServices')
                    ->badge()
                    ->alignCenter(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                StatusColumnPreset::featured(label: 'Featured'),
                StatusColumnPreset::active(label: 'Aktif'),
                TimestampColumnPreset::updatedAt('d M Y H:i'),
            ])
            ->filters([
                StatusColumnPreset::filterFeatured('Tampil di Beranda'),
                StatusColumnPreset::filterActive('Status Aktif'),
                TrashedFilter::make()->label('Sampah'),
            ])
            ->recordActions([
                ViewAction::make()->label('Lihat')->icon('lucide-eye'),
                EditAction::make()->label('Ubah')->icon('lucide-pencil'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Negara Tujuan')
            ->emptyStateDescription('Tambahkan negara tujuan sebelum membuat layanan Visa.')
            ->emptyStateIcon('lucide-globe-2');
    }
}

