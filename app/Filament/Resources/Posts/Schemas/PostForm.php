<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Artikel')
                    ->icon(Heroicon::OutlinedNewspaper)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('post_category_id')
                                ->label('Kategori')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'draft' => 'Draft',
                                    'published' => 'Dipublikasikan',
                                    'archived' => 'Diarsipkan',
                                ])
                                ->default('draft')
                                ->required(),
                        ]),
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                Grid::make(2)->schema([
                                    TextInput::make('title')
                                        ->label('Judul')
                                        ->placeholder('Masukkan judul artikel...')
                                        ->required($locale === 'id')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn ($state, Set $set) => $set('slug', Str::slug($state)))
                                        ->maxLength(255),
                                    TextInput::make('slug')
                                        ->label('Slug')
                                        ->placeholder('judul-artikel')
                                        ->required($locale === 'id')
                                        ->maxLength(255),
                                ]),
                                TextInput::make('excerpt')
                                    ->label('Ringkasan')
                                    ->placeholder('Ringkasan singkat artikel (maks. 300 karakter)...')
                                    ->maxLength(300)
                                    ->columnSpanFull(),
                                RichEditor::make('content')
                                    ->label('Konten')
                                    ->placeholder('Tulis konten artikel di sini...')
                                    ->required($locale === 'id')
                                    ->columnSpanFull(),
                            ]),
                    ]),
                Section::make('Media & Publikasi')
                    ->icon(Heroicon::OutlinedPhoto)
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('cover_image')
                            ->label('Foto Cover')
                            ->image()
                            ->directory('posts/covers')
                            ->visibility('public')
                            ->columnSpanFull(),
                        DateTimePicker::make('published_at')
                            ->label('Tanggal Publikasi')
                            ->native(false),
                        Hidden::make('user_id')
                            ->default(fn () => auth()->id()),
                    ]),
            ]);
    }
}
