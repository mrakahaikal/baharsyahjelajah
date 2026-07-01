<?php

namespace App\Filament\Resources\Tours\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class TourForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Informasi Dasar')
                        ->description('Nama, kategori, dan deskripsi tur.')
                        ->icon(Heroicon::OutlinedInformationCircle)
                        ->schema([
                            Section::make()
                                ->schema([
                                    Select::make('tour_category_id')
                                        ->label('Kategori')
                                        ->relationship('category', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->columnSpanFull(),
                                    Translate::make()
                                        ->locales(['id', 'en', 'ms'])
                                        ->schema(fn (string $locale) => [
                                            Grid::make(2)
                                                ->schema([
                                                    TextInput::make('name')
                                                        ->label('Nama Tur')
                                                        ->placeholder('Masukkan nama paket tur...')
                                                        ->required($locale == 'id')
                                                        ->live(onBlur: true)
                                                        ->afterStateUpdated(fn ($state, Set $set) => $set("slug.{$locale}", Str::slug($state)))
                                                        ->maxLength(255),
                                                    TextInput::make('slug')
                                                        ->label('Slug')
                                                        ->placeholder('slug-tur-anda')
                                                        ->required($locale == 'id')
                                                        ->maxLength(255),
                                                ]),
                                            RichEditor::make('description')
                                                ->label('Deskripsi')
                                                ->placeholder('Tuliskan deskripsi lengkap mengenai tur ini...')
                                                ->required($locale === 'id')
                                                ->columnSpanFull(),
                                            RichEditor::make('highlights')
                                                ->label('Highlight / Unggulan')
                                                ->placeholder('Tuliskan poin-poin unggulan dari tur ini...')
                                                ->required($locale === 'id')
                                                ->columnSpanFull(),
                                        ]),
                                ]),
                        ]),

                    Step::make('Detail Paket')
                        ->description('Tipe tur, durasi, harga, dan kapasitas.')
                        ->icon(Heroicon::OutlinedBriefcase)
                        ->schema([
                            Section::make()
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Select::make('tour_type')
                                                ->label('Tipe Tur')
                                                ->options([
                                                    'open' => 'Open Trip',
                                                    'private' => 'Private Trip',
                                                ])
                                                ->required(),
                                            Select::make('difficulty')
                                                ->label('Tingkat Kesulitan')
                                                ->options([
                                                    'easy' => 'Mudah',
                                                    'moderate' => 'Sedang',
                                                    'hard' => 'Sulit',
                                                ])
                                                ->required(),
                                            TextInput::make('duration_days')
                                                ->label('Durasi (Hari)')
                                                ->placeholder('0')
                                                ->numeric()
                                                ->required(),
                                            TextInput::make('duration_nights')
                                                ->label('Durasi (Malam)')
                                                ->placeholder('0')
                                                ->numeric()
                                                ->required(),
                                            TextInput::make('price')
                                                ->label('Harga')
                                                ->placeholder('0')
                                                ->numeric()
                                                ->required(),
                                            Select::make('currency')
                                                ->label('Mata Uang')
                                                ->options([
                                                    'IDR' => 'IDR (Rp)',
                                                    'USD' => 'USD ($)',
                                                    'MYR' => 'MYR (RM)',
                                                    'SGD' => 'SGD (S$)',
                                                ])
                                                ->default('IDR')
                                                ->required(),
                                            TextInput::make('max_pax')
                                                ->label('Kapasitas Maksimal')
                                                ->placeholder('0')
                                                ->suffix('Orang')
                                                ->numeric()
                                                ->required(),
                                        ]),
                                ]),
                        ]),

                    Step::make('Pengaturan & Media')
                        ->description('Thumbnail dan status publikasi.')
                        ->icon(Heroicon::OutlinedPhoto)
                        ->schema([
                            Section::make()
                                ->schema([
                                    FileUpload::make('thumbnail')
                                        ->label('Foto Utama')
                                        ->image()
                                        ->directory('tours/thumbnails')
                                        ->columnSpanFull(),
                                    Grid::make(2)
                                        ->schema([
                                            Toggle::make('is_active')
                                                ->label('Aktif')
                                                ->default(true)
                                                ->required(),
                                            Toggle::make('is_featured')
                                                ->label('Unggulan')
                                                ->default(false)
                                                ->required(),
                                        ]),
                                ]),
                        ]),
                ])
                    ->skippable()
                    ->columnSpanFull(),
            ]);
    }
}
