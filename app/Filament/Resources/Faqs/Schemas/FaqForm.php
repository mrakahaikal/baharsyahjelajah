<?php

namespace App\Filament\Resources\Faqs\Schemas;

use App\Enums\FaqCategory;
use App\Enums\FaqContext;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pertanyaan & Jawaban')
                    ->description('Kelola daftar tanya jawab (FAQ) berdasarkan kategori, konteks penayangan, pertanyaan, dan jawaban.')
                    ->icon('lucide-help-circle')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('category')
                                ->label('Kategori FAQ')
                                ->options(FaqCategory::class)
                                ->placeholder('Pilih kategori')
                                ->helperText('Pilih pengelompokan topik FAQ.')
                                ->prefixIcon('lucide-tag')
                                ->native(false)
                                ->required(),
                            Select::make('contexts')
                                ->label('Konteks Penayangan')
                                ->options(FaqContext::class)
                                ->multiple()
                                ->preload()
                                ->placeholder('Pilih konteks')
                                ->helperText('Pilih halaman/konteks penayangan FAQ.')
                                ->prefixIcon('lucide-layout')
                                ->native(false)
                                ->required(),
                            TextInput::make('sort_order')
                                ->label('Urutan Tampilan')
                                ->placeholder('Contoh: 0')
                                ->numeric()
                                ->default(0)
                                ->prefixIcon('lucide-sort-asc')
                                ->helperText('Urutan prioritas penampilan FAQ.'),
                        ]),
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                TextInput::make('question')
                                    ->label('Pertanyaan')
                                    ->placeholder('Masukkan teks pertanyaan...')
                                    ->required($locale === 'id')
                                    ->maxLength(500)
                                    ->prefixIcon('lucide-help-circle')
                                    ->columnSpanFull(),
                                TextInput::make('answer')
                                    ->label('Jawaban')
                                    ->placeholder('Masukkan teks jawaban lengkap...')
                                    ->required($locale === 'id')
                                    ->maxLength(2000)
                                    ->prefixIcon('lucide-message-square')
                                    ->columnSpanFull(),
                            ]),
                        Toggle::make('is_active')
                            ->label('Tampilkan di Website')
                            ->default(true),
                    ]),
            ]);
    }
}
