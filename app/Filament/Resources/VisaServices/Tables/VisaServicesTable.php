<?php

namespace App\Filament\Resources\VisaServices\Tables;

use App\Enums\VisaEntryType;
use App\Models\VisaService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

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
                TextColumn::make('price_idr')
                    ->label('Harga')
                    ->money('IDR', locale: 'id')
                    ->placeholder('Hubungi admin')
                    ->sortable(),
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
                IconColumn::make('is_featured')->label('Unggulan')->boolean()->alignCenter(),
                IconColumn::make('is_active')->label('Aktif')->boolean()->alignCenter(),
                TextColumn::make('sort_order')->label('Urutan')->numeric()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Diperbarui')->dateTime('d M Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('country')
                    ->label('Negara Tujuan')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
                SelectFilter::make('entry_type')
                    ->label('Tipe Masuk')
                    ->options(VisaEntryType::class)
                    ->native(false),
                TernaryFilter::make('is_active')->label('Status Aktif'),
                TernaryFilter::make('is_featured')->label('Layanan Unggulan'),
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
