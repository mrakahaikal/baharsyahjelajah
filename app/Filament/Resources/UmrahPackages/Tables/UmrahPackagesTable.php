<?php

namespace App\Filament\Resources\UmrahPackages\Tables;

use App\Enums\UmrahPackageType;
use App\Models\Country;
use App\Models\UmrahPackage;
use App\Models\Vehicle;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class UmrahPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')
                    ->label('Cover')
                    ->collection(UmrahPackage::MEDIA_COLLECTION_COVER)
                    ->square()
                    ->size(52),
                TextColumn::make('name')
                    ->label('Nama Paket')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (UmrahPackage $record): string => $record->duration_days.' hari')
                    ->wrap(),
                TextColumn::make('package_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => UmrahPackageType::tryFrom($state)?->getLabel() ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'plus' => 'info',
                        'vip' => 'warning',
                        'ramadan' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('prices_min_price_idr')
                    ->label('Mulai dari')
                    ->state(fn (UmrahPackage $record): int => (int) ($record->prices_min_price_idr ?? $record->price_idr ?? 0))
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('next_departure_date')
                    ->label('Keberangkatan Terdekat')
                    ->date('d M Y')
                    ->placeholder('Belum ada jadwal')
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean()
                    ->alignCenter(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('airline')
                    ->label('Maskapai')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('package_type')
                    ->label('Tipe Paket')
                    ->options(UmrahPackageType::class)
                    ->native(false),
                SelectFilter::make('is_active')
                    ->label('Status Aktif')
                    ->options(['1' => 'Aktif', '0' => 'Nonaktif'])
                    ->native(false),
                SelectFilter::make('is_featured')
                    ->label('Paket Unggulan')
                    ->options(['1' => 'Unggulan', '0' => 'Biasa'])
                    ->native(false),
                TrashedFilter::make()
                    ->label('Sampah')
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
                    BulkAction::make('attachCountries')
                        ->label('Kaitkan Negara Destinasi')
                        ->icon('lucide-globe')
                        ->modalHeading('Kaitkan Negara Destinasi Ke Paket Umrah Terpilih')
                        ->modalDescription('Pilih satu atau beberapa negara yang ingin dikaitkan secara massal ke paket umrah yang dipilih.')
                        ->form([
                            Select::make('countries')
                                ->label('Pilih Negara Destinasi')
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
                                ->title('Berhasil Mengaitkan Negara Destinasi')
                                ->body(count($records).' paket umrah berhasil dikaitkan dengan negara pilihan.')
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('attachVehicles')
                        ->label('Kaitkan Armada Kendaraan')
                        ->icon('lucide-bus')
                        ->modalHeading('Kaitkan Armada Kendaraan Ke Paket Umrah Terpilih')
                        ->modalDescription('Pilih satu atau beberapa armada kendaraan yang ingin dikaitkan secara massal ke paket umrah yang dipilih.')
                        ->form([
                            Select::make('vehicles')
                                ->label('Pilih Armada Kendaraan')
                                ->options(fn () => Vehicle::query()->pluck('name', 'id'))
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $vehicleIds = $data['vehicles'] ?? [];
                            foreach ($records as $record) {
                                $record->vehicles()->syncWithoutDetaching($vehicleIds);
                            }

                            Notification::make()
                                ->title('Berhasil Mengaitkan Armada Kendaraan')
                                ->body(count($records).' paket umrah berhasil dikaitkan dengan armada kendaraan pilihan.')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make()->label('Hapus Terpilih'),
                    ForceDeleteBulkAction::make()->label('Hapus Permanen'),
                    RestoreBulkAction::make()->label('Pulihkan'),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Paket Umrah')
            ->emptyStateDescription('Buat paket umrah baru untuk mulai menawarkan program perjalanan ibadah umrah.')
            ->emptyStateIcon('lucide-sparkles')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Tambah Paket Umrah')
                    ->icon('lucide-plus'),
            ]);
    }
}
