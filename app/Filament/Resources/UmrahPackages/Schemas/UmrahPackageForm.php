<?php

namespace App\Filament\Resources\UmrahPackages\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class UmrahPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Informasi Paket')
                        ->description('Nama, tipe, dan deskripsi paket umrah.')
                        ->icon(Heroicon::OutlinedSparkles)
                        ->schema([
                            Section::make()
                                ->schema([
                                    Select::make('package_type')
                                        ->label('Tipe Paket')
                                        ->options([
                                            'regular' => 'Regular',
                                            'plus' => 'Plus',
                                            'vip' => 'VIP',
                                            'ramadan' => 'Ramadan',
                                        ])
                                        ->required()
                                        ->columnSpanFull(),
                                    Translate::make()
                                        ->locales(['id', 'en', 'ms'])
                                        ->schema(fn (string $locale) => [
                                            TextInput::make('name')
                                                ->label('Nama Paket')
                                                ->placeholder('Misal: Umrah Reguler Awal Musim')
                                                ->required($locale === 'id')
                                                ->maxLength(255)
                                                ->columnSpanFull(),
                                            RichEditor::make('description')
                                                ->label('Deskripsi Paket')
                                                ->placeholder('Tuliskan detail informasi paket umrah ini...')
                                                ->required($locale === 'id')
                                                ->columnSpanFull(),
                                        ]),
                                ]),
                        ]),

                    Step::make('Fasilitas & Hotel')
                        ->description('Informasi maskapai, hotel Makkah dan Madinah.')
                        ->icon(Heroicon::OutlinedBuildingOffice)
                        ->schema([
                            Section::make()
                                ->schema([
                                    Grid::make(2)->schema([
                                        TextInput::make('airline')
                                            ->label('Maskapai Penerbangan')
                                            ->placeholder('Misal: Saudi Arabian Airlines')
                                            ->maxLength(100),
                                        Select::make('room_type')
                                            ->label('Tipe Kamar')
                                            ->options([
                                                'quad' => 'Quad (4 orang)',
                                                'triple' => 'Triple (3 orang)',
                                                'double' => 'Double (2 orang)',
                                                'single' => 'Single (1 orang)',
                                            ])
                                            ->required(),
                                    ]),
                                    Grid::make(2)->schema([
                                        TextInput::make('hotel_makkah')
                                            ->label('Hotel Makkah')
                                            ->placeholder('Misal: Pullman Zamzam Makkah')
                                            ->maxLength(255),
                                        Select::make('hotel_makkah_stars')
                                            ->label('Bintang Hotel Makkah')
                                            ->options([
                                                3 => '★★★ 3 Bintang',
                                                4 => '★★★★ 4 Bintang',
                                                5 => '★★★★★ 5 Bintang',
                                            ]),
                                    ]),
                                    Grid::make(2)->schema([
                                        TextInput::make('hotel_madinah')
                                            ->label('Hotel Madinah')
                                            ->placeholder('Misal: Shaza Al Madinah')
                                            ->maxLength(255),
                                        Select::make('hotel_madinah_stars')
                                            ->label('Bintang Hotel Madinah')
                                            ->options([
                                                3 => '★★★ 3 Bintang',
                                                4 => '★★★★ 4 Bintang',
                                                5 => '★★★★★ 5 Bintang',
                                            ]),
                                    ]),
                                ]),
                        ]),

                    Step::make('Harga & Media')
                        ->description('Durasi, harga, dan foto utama paket.')
                        ->icon(Heroicon::OutlinedCurrencyDollar)
                        ->schema([
                            Section::make()
                                ->schema([
                                    Grid::make(2)->schema([
                                        TextInput::make('duration_days')
                                            ->label('Durasi (Hari)')
                                            ->placeholder('9')
                                            ->numeric()
                                            ->suffix('Hari')
                                            ->required(),
                                        TextInput::make('price_idr')
                                            ->label('Harga Dasar (IDR)')
                                            ->placeholder('0')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->required(),
                                    ]),
                                    Grid::make(2)->schema([
                                        Toggle::make('visa_included')
                                            ->label('Sudah Termasuk Visa')
                                            ->default(true)
                                            ->inline(false),
                                        Toggle::make('handling_included')
                                            ->label('Sudah Termasuk Handling')
                                            ->default(true)
                                            ->inline(false),
                                    ]),
                                    FileUpload::make('thumbnail')
                                        ->label('Foto Utama Paket')
                                        ->image()
                                        ->directory('umrah/thumbnails')
                                        ->visibility('public')
                                        ->imageEditor()
                                        ->columnSpanFull(),
                                    Toggle::make('is_active')
                                        ->label('Paket Aktif / Dapat Dipesan')
                                        ->default(true)
                                        ->inline(false),
                                ]),
                        ]),
                ])
                ->skippable()
                ->columnSpanFull(),
            ]);
    }
}
