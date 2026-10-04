<?php

namespace App\Filament\Resources\PostCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class PostCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kategori')
                    ->description('Kelola nama, slug URL, dan deskripsi kategori artikel blog.')
                    ->icon('lucide-tag')
                    ->columnSpanFull()
                    ->schema([
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                Grid::make(2)->schema([
                                    TextInput::make('name')
                                        ->label('Nama Kategori')
                                        ->placeholder('Contoh: Tips Perjalanan')
                                        ->required($locale === 'id')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn ($state, Set $set) => $set("slug.{$locale}", Str::slug($state)))
                                        ->maxLength(255)
                                        ->prefixIcon('lucide-type'),
                                    TextInput::make('slug')
                                        ->label('Slug URL')
                                        ->placeholder('tips-perjalanan')
                                        ->required($locale === 'id')
                                        ->maxLength(255)
                                        ->prefixIcon('lucide-link-2'),
                                ]),
                                TextInput::make('description')
                                    ->label('Deskripsi Kategori')
                                    ->placeholder('Tuliskan deskripsi singkat kategori ini...')
                                    ->maxLength(500)
                                    ->helperText('Penjelasan ringkas mengenai topik artikel dalam kategori ini.')
                                    ->prefixIcon('lucide-file-text')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
