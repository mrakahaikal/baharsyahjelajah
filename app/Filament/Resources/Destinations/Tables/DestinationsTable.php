<?php

namespace App\Filament\Resources\Destinations\Tables;

use App\Models\Country;
use App\Models\Destination;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class DestinationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('created_at', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('gallery')
                    ->label('Foto')
                    ->collection(Destination::MEDIA_COLLECTION_GALLERY)
                    ->square()
                    ->size(52),
                TextColumn::make('name')
                    ->label('Nama Destinasi')
                    ->searchable()
                    ->weight('bold')
                    ->wrap(),
                TextColumn::make('slug')
                    ->label('Slug URL')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('location')
                    ->label('Lokasi')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('tour_packages_count')
                    ->label('Paket Tur')
                    ->counts('tourPackages')
                    ->badge()
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('itineraries_count')
                    ->label('Itinerary')
                    ->counts('itineraries')
                    ->badge()
                    ->alignCenter()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean(),
                TextColumn::make('map_url')
                    ->label('Peta')
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? 'Buka Peta' : '-')
                    ->url(fn (Destination $record): ?string => $record->map_url)
                    ->openUrlInNewTab()
                    ->icon('lucide-external-link'),
                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Pembaruan Terakhir')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
                TernaryFilter::make('is_featured')
                    ->label('Destinasi Unggulan'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Ubah')
                    ->icon('lucide-pencil')
                    ->slideOver()
                    ->color('primary'),
                DeleteAction::make()
                    ->label('Hapus')
                    ->icon('lucide-trash')
                    ->color('danger'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('attachCountries')
                        ->label('Kaitkan Negara')
                        ->icon('lucide-globe')
                        ->modalHeading('Kaitkan Negara Ke Destinasi Terpilih')
                        ->modalDescription('Pilih satu atau beberapa negara yang ingin dikaitkan secara massal ke destinasi yang dipilih.')
                        ->form([
                            Select::make('countries')
                                ->label('Pilih Negara')
                                ->options(fn () => Country::query()->pluck('name', 'id'))
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $countryIds = $data['countries'] ?? [];
                            foreach ($records as $record) {
                                $record->countries()->syncWithoutDetaching($countryIds);
                            }

                            Notification::make()
                                ->title('Berhasil Mengaitkan Negara')
                                ->body(count($records).' destinasi berhasil dikaitkan dengan negara pilihan.')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Destinasi Wisata')
            ->emptyStateDescription('Buat destinasi wisata baru untuk mulai menghubungkannya ke itinerary paket tur.')
            ->emptyStateIcon('lucide-map-pin')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Tambah Destinasi')
                    ->icon('lucide-plus')
                    ->slideOver(),
            ]);
    }
}
