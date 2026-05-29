<?php

namespace App\Filament\Resources\UmrahPackages\Schemas;

use App\Models\UmrahPackage;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class UmrahPackageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Section::make('Informasi Utama')
                                    ->schema([
                                        ImageEntry::make('thumbnail')
                                            ->hiddenLabel()
                                            ->square()
                                            ->width('100%')
                                            ->height(350),
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('name')
                                                    ->label('Nama Paket Umrah')
                                                    ->weight(FontWeight::Bold)
                                                    ->size(TextSize::Large),
                                                TextEntry::make('package_type')
                                                    ->label('Tipe Paket')
                                                    ->badge()
                                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                                        'regular' => 'Regular',
                                                        'plus' => 'Plus',
                                                        'vip' => 'VIP',
                                                        'ramadan' => 'Ramadan',
                                                        default => ucfirst($state),
                                                    })
                                                    ->color('info'),
                                            ]),
                                        TextEntry::make('description')
                                            ->label('Deskripsi Lengkap')
                                            ->html()
                                            ->prose(),
                                    ]),

                                Section::make('Akomodasi & Transportasi')
                                    ->icon(Heroicon::OutlinedBuildingOffice)
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextEntry::make('airline')
                                                ->label('Maskapai Penerbangan')
                                                ->icon(Heroicon::OutlinedPaperAirplane),
                                            TextEntry::make('room_type')
                                                ->label('Tipe Kamar')
                                                ->badge()
                                                ->color('info'),
                                        ]),
                                        Grid::make(2)->schema([
                                            TextEntry::make('hotel_makkah')
                                                ->label('Hotel Makkah')
                                                ->suffix(fn ($record) => $record->hotel_makkah_stars ? " (★{$record->hotel_makkah_stars})" : ''),
                                            TextEntry::make('hotel_madinah')
                                                ->label('Hotel Madinah')
                                                ->suffix(fn ($record) => $record->hotel_madinah_stars ? " (★{$record->hotel_madinah_stars})" : ''),
                                        ]),
                                    ]),
                            ])
                            ->columnSpan(2),

                        Grid::make(1)
                            ->schema([
                                Section::make('Informasi Harga')
                                    ->icon(Heroicon::OutlinedCurrencyDollar)
                                    ->schema([
                                        TextEntry::make('price_idr')
                                            ->label('Harga Dasar')
                                            ->money('IDR', locale: 'id')
                                            ->weight(FontWeight::Bold)
                                            ->size(TextSize::Large)
                                            ->color('success'),
                                        TextEntry::make('duration_days')
                                            ->label('Durasi Perjalanan')
                                            ->icon(Heroicon::OutlinedClock)
                                            ->suffix(' Hari'),
                                        Grid::make(2)->schema([
                                            IconEntry::make('visa_included')
                                                ->label('Visa')
                                                ->boolean(),
                                            IconEntry::make('handling_included')
                                                ->label('Handling')
                                                ->boolean(),
                                        ]),
                                    ]),

                                Section::make('Status & Metadata')
                                    ->schema([
                                        IconEntry::make('is_active')
                                            ->label('Status Aktif')
                                            ->boolean(),
                                        TextEntry::make('created_at')
                                            ->label('Dibuat Pada')
                                            ->dateTime('d M Y H:i'),
                                        TextEntry::make('updated_at')
                                            ->label('Terakhir Diperbarui')
                                            ->dateTime('d M Y H:i'),
                                        TextEntry::make('deleted_at')
                                            ->label('Dihapus Pada')
                                            ->dateTime('d M Y H:i')
                                            ->visible(fn (UmrahPackage $record): bool => $record->trashed()),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
