<?php

namespace App\Filament\Resources\Tours\Tables;

use App\Enums\TourType;
use App\Models\PackageTier;
use App\Models\Tour;
use App\Models\TourPackage;
use App\Models\TourPriceTier;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ToursTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'packages.media',
                'packages.tiers.priceTiers',
            ]))
            ->columns([
                ImageColumn::make('cover')
                    ->label('Foto')
                    ->state(fn (Tour $record): ?string => $record->packages
                        ->first()
                        ?->getFirstMediaUrl(TourPackage::MEDIA_COLLECTION_COVER) ?: null)
                    ->square()
                    ->size(56),
                TextColumn::make('name')
                    ->label('Nama Tur')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tour_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (TourType $state): string => match ($state) {
                        TourType::Domestic => 'Domestik',
                        TourType::International => 'Internasional',
                    })
                    ->color(fn (TourType $state): string => match ($state) {
                        TourType::Domestic => 'success',
                        TourType::International => 'info',
                    })
                    ->sortable(),
                TextColumn::make('packages_count')
                    ->label('Paket')
                    ->counts('packages')
                    ->badge()
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('duration_summary')
                    ->label('Durasi')
                    ->state(fn (Tour $record): string => $record->packages
                        ->map(fn (TourPackage $package): string => $package->duration_label)
                        ->unique()
                        ->implode(', ') ?: '-'),
                TextColumn::make('starting_price')
                    ->label('Harga Mulai')
                    ->state(function (Tour $record): string {
                        /** @var TourPriceTier|null $priceTier */
                        $priceTier = $record->packages
                            ->flatMap(fn (TourPackage $package) => $package->tiers)
                            ->flatMap(fn (PackageTier $tier) => $tier->priceTiers)
                            ->sortBy(fn (TourPriceTier $price): float => (float) $price->price)
                            ->first();

                        return $priceTier?->formatted_price ?? '-';
                    }),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->alignCenter()
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean()
                    ->alignCenter()
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
                        TourType::Domestic->value => 'Domestik',
                        TourType::International->value => 'Internasional',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
                TernaryFilter::make('is_featured')
                    ->label('Status Unggulan')
                    ->trueLabel('Unggulan')
                    ->falseLabel('Biasa'),
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
            ])
            ->defaultSort('created_at', 'desc');
    }
}
