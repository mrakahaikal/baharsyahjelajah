<?php

namespace App\Filament\Resources\VisaServices\Tables;

use App\Enums\VisaEntryType;
use App\Filament\Support\CurrencyOptions;
use App\Filament\Support\Tables\Presets\StatusColumnPreset;
use App\Filament\Support\Tables\Presets\TimestampColumnPreset;
use App\Models\VisaService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class VisaServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')
                    ->label('Cover')
                    ->collection(VisaService::MEDIA_COLLECTION_COVER)
                    ->square()
                    ->size(52),
                TextColumn::make('name')
                    ->label('Layanan')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (VisaService $record): string => $record->visa_type)
                    ->wrap(),
                TextColumn::make('country.name')
                    ->label('Negara Tujuan')
                    ->badge()
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Harga')
                    ->state(fn (VisaService $record) => $record->price ?? $record->price_idr)
                    ->money(fn (VisaService $record): string => $record->currency ?? 'IDR', locale: 'id')
                    ->placeholder('Hubungi admin')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy(DB::raw('COALESCE(price, price_idr)'), $direction)),
                TextColumn::make('processing_time')
                    ->label('Estimasi Proses')
                    ->state(fn (VisaService $record): string => match (true) {
                        $record->processing_days_min && $record->processing_days_max => $record->processing_days_min.'–'.$record->processing_days_max.' hari',
                        (bool) $record->processing_days_min => 'Mulai '.$record->processing_days_min.' hari',
                        (bool) $record->processing_days_max => 'Maks. '.$record->processing_days_max.' hari',
                        default => '-',
                    }),
                TextColumn::make('entry_type')
                    ->label('Tipe Masuk')
                    ->badge()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                StatusColumnPreset::featured('Unggulan'),
                StatusColumnPreset::active('Aktif'),
                TextColumn::make('sort_order')->label('Urutan')->numeric()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TimestampColumnPreset::updatedAt('d M Y H:i'),
            ])
            ->filters([
                SelectFilter::make('country')
                    ->label('Negara Tujuan')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
                SelectFilter::make('currency')
                    ->label('Mata Uang')
                    ->options(CurrencyOptions::selectOptions())
                    ->native(false),
                SelectFilter::make('entry_type')
                    ->label('Tipe Masuk')
                    ->options(VisaEntryType::class)
                    ->native(false),
                StatusColumnPreset::filterActive('Status Aktif'),
                StatusColumnPreset::filterFeatured('Layanan Unggulan'),
                TrashedFilter::make()->label('Sampah'),
            ])
            ->recordActions([
                ViewAction::make()->label('Lihat')->icon('lucide-eye'),
                EditAction::make()->label('Ubah')->icon('lucide-pencil'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Layanan Visa')
            ->emptyStateDescription('Tambahkan layanan Visa pertama untuk negara tujuan yang tersedia.')
            ->emptyStateIcon('lucide-stamp');
    }
}

