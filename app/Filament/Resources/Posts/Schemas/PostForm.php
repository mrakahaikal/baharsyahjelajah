<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Support\FilamentLocale;
use App\Filament\Support\Forms\Components\TranslatableNameSlug;
use App\Filament\Support\Forms\Presets\MediaUploadPreset;
use App\Models\Destination;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Artikel')
                    ->description('Kelola judul, slug URL, kategori, status penulisan, dan isi konten artikel.')
                    ->icon('lucide-newspaper')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('post_category_id')
                                ->label('Kategori Artikel')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->placeholder('Pilih kategori')
                                ->helperText('Pilih kategori pengelompokan untuk artikel ini.')
                                ->prefixIcon('lucide-tag')
                                ->native(false),
                            Select::make('status')
                                ->label('Status Publikasi')
                                ->options([
                                    'draft' => 'Draft',
                                    'published' => 'Dipublikasikan',
                                    'archived' => 'Diarsipkan',
                                ])
                                ->default('draft')
                                ->required()
                                ->placeholder('Pilih status')
                                ->helperText('Tentukan status penulisan artikel.')
                                ->prefixIcon('lucide-info')
                                ->native(false),
                        ]),
                        Translate::make()
                            ->locales(FilamentLocale::locales())
                            ->schema(fn (string $locale) => [
                                TranslatableNameSlug::make(
                                    locale: $locale,
                                    nameLabel: 'Judul Artikel',
                                    slugLabel: 'Slug URL',
                                    namePlaceholder: 'Masukkan judul artikel...',
                                    slugPlaceholder: 'judul-artikel',
                                    nameField: 'title',
                                    slugField: 'slug',
                                ),
                                TextInput::make('excerpt')
                                    ->label('Ringkasan Singkat')
                                    ->placeholder('Tuliskan ringkasan singkat artikel...')
                                    ->maxLength(300)
                                    ->helperText('Ringkasan 1-2 kalimat untuk pratinjau kartu blog (maksimal 300 karakter).')
                                    ->prefixIcon('lucide-file-text')
                                    ->columnSpanFull(),
                                RichEditor::make('content')
                                    ->label('Isi Konten Artikel')
                                    ->placeholder('Tulis isi konten artikel lengkap di sini...')
                                    ->required($locale === FilamentLocale::primary())
                                    ->helperText('Konten utama artikel.')
                                    ->columnSpanFull(),
                            ]),
                    ]),
                Section::make('Media & Publikasi')
                    ->description('Atur gambar sampul, jadwal penerbitan, dan metadata artikel.')
                    ->icon('lucide-image')
                    ->columnSpanFull()
                    ->schema([
                        MediaUploadPreset::publicImage(
                            name: 'cover_image',
                            directory: 'posts/covers',
                            label: 'Gambar Sampul (Cover)',
                            helperText: 'Format: JPG, PNG, WebP (Rasio ideal 16:9, maks 5MB).'
                        )->columnSpanFull(),
                        Select::make('destinations')
                            ->label('Destinasi Terkait')
                            ->relationship('destinations', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Destination $record): string => $record->name)
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText('Artikel akan tampil pada halaman destinasi yang dipilih.')
                            ->columnSpanFull(),
                        DateTimePicker::make('published_at')
                            ->label('Tanggal & Waktu Publikasi')
                            ->placeholder('Pilih tanggal & waktu')
                            ->helperText('Jadwalkan tanggal dan waktu penayangan artikel di website.')
                            ->prefixIcon('lucide-calendar')
                            ->native(false),
                        Hidden::make('user_id')
                            ->default(fn () => auth()->id()),
                    ]),
            ]);
    }
}

