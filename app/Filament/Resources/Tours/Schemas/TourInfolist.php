<?php

namespace App\Filament\Resources\Tours\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class TourInfolist
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
                                                    ->label('Nama Paket Tur')
                                                    ->weight(FontWeight::Bold)
                                                    ->size(TextSize::Large),
                                                TextEntry::make('category.name')
                                                    ->label('Kategori')
                                                    ->badge()
                                                    ->color('info'),
                                            ]),
                                    ]),

                                Section::make('Deskripsi & Highlight')
                                    ->schema([
                                        TextEntry::make('description')
                                            ->label('Deskripsi Lengkap')
                                            ->html()
                                            ->prose(),
                                        TextEntry::make('highlights')
                                            ->label('Highlight Perjalanan')
                                            ->html()
                                            ->prose(),
                                    ]),
                            ])
                            ->columnSpan(2),

                        Grid::make(1)
                            ->schema([
                                Section::make('Informasi Trip')
                                    ->icon(Heroicon::OutlinedBriefcase)
                                    ->schema([
                                        TextEntry::make('price_idr')
                                            ->label('Harga Mulai')
                                            ->money('IDR', locale: 'id')
                                            ->weight(FontWeight::Bold)
                                            ->size(TextSize::Large)
                                            ->color('success'),

                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('tour_type')
                                                    ->label('Tipe Tur')
                                                    ->badge()
                                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                                        'open' => 'Open Trip',
                                                        'private' => 'Private Trip',
                                                        default => $state,
                                                    })
                                                    ->color('info'),
                                                TextEntry::make('difficulty')
                                                    ->label('Kesulitan')
                                                    ->badge()
                                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                                        'easy' => 'Mudah',
                                                        'moderate' => 'Sedang',
                                                        'hard' => 'Sulit',
                                                        default => $state,
                                                    })
                                                    ->color(fn (string $state): string => match ($state) {
                                                        'easy' => 'success',
                                                        'moderate' => 'warning',
                                                        'hard' => 'danger',
                                                        default => 'gray',
                                                    }),
                                            ]),

                                        TextEntry::make('duration')
                                            ->label('Durasi Perjalanan')
                                            ->icon(Heroicon::OutlinedClock)
                                            ->state(fn ($record): string => "{$record->duration_days} Hari {$record->duration_nights} Malam"),

                                        TextEntry::make('max_pax')
                                            ->label('Kapasitas Maksimal')
                                            ->icon(Heroicon::OutlinedUsers)
                                            ->suffix(' Orang'),
                                    ]),

                                Section::make('Status & Metadata')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                IconEntry::make('is_active')
                                                    ->label('Aktif')
                                                    ->boolean(),
                                                IconEntry::make('is_featured')
                                                    ->label('Unggulan')
                                                    ->boolean(),
                                            ]),
                                        TextEntry::make('created_at')
                                            ->label('Dibuat Pada')
                                            ->dateTime('d M Y H:i'),
                                        TextEntry::make('updated_at')
                                            ->label('Terakhir Diperbarui')
                                            ->dateTime('d M Y H:i'),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ]),
            ])
            ->columns(1);
    }
}
