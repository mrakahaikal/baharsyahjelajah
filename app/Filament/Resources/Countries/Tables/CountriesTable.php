<?php

namespace App\Filament\Resources\Countries\Tables;

use App\Models\Country;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
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
                    ->size(48),
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
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Status Aktif'),
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
