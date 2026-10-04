<?php

namespace App\Filament\Resources\TourCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class TourCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kategori')
                    ->description('Kelola detail nama, slug, ikon, dan urutan prioritas kategori tur.')
                    ->icon('lucide-tag')
                    ->columnSpanFull()
                    ->schema([
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nama Kategori')
                                            ->placeholder('Contoh: Wisata Alam')
                                            ->required($locale === 'id')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, Set $set) => $set("slug.{$locale}", Str::slug($state)))
                                            ->maxLength(255)
                                            ->prefixIcon('lucide-type'),
                                        TextInput::make('slug')
                                            ->label('Slug URL')
                                            ->placeholder('wisata-alam')
                                            ->required($locale === 'id')
                                            ->maxLength(255)
                                            ->prefixIcon('lucide-link-2'),
                                    ]),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('icon')
                                    ->label('Ikon Kategori (Lucide)')
                                    ->placeholder('Contoh: lucide-compass')
                                    ->maxLength(255)
                                    ->helperText('Masukkan kode nama ikon Lucide (misal: lucide-tag, lucide-compass).')
                                    ->prefixIcon('lucide-image'),
                                TextInput::make('sort_order')
                                    ->label('Urutan Tampilan')
                                    ->placeholder('Contoh: 0')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Prioritas urutan penampilan kategori pada website.')
                                    ->prefixIcon('lucide-sort-asc'),
                            ]),
                    ]),
            ]);
    }
}
