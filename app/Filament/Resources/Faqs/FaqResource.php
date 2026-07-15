<?php

namespace App\Filament\Resources\Faqs;

use App\Enums\FaqCategory;
use App\Enums\FaqContext;
use App\Filament\Resources\Faqs\Pages\ManageFaqs;
use App\Models\Faq;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static string|BackedEnum|null $navigationIcon = 'lucide-help-circle';

    protected static string|null|\UnitEnum $navigationGroup = 'Konten Website';

    protected static ?string $navigationLabel = 'FAQ';

    protected static ?string $modelLabel = 'FAQ';

    protected static ?string $pluralModelLabel = 'FAQ';

    protected static ?string $recordTitleAttribute = 'question';

    public static function form(Schema $schema): Schema
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

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question')
            ->defaultSort('sort_order', 'asc')
            ->columns([
                TextColumn::make('question')
                    ->label('Pertanyaan')
                    ->searchable()
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge(),
                TextColumn::make('contexts')
                    ->label('Konteks Penayangan')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => FaqContext::tryFrom($state)?->getLabel() ?? $state),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(FaqCategory::class)
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Ubah')
                    ->icon('lucide-pencil')
                    ->color('primary'),
                DeleteAction::make()
                    ->label('Hapus')
                    ->icon('lucide-trash')
                    ->color('danger'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateHeading('Belum Ada FAQ')
            ->emptyStateDescription('Buat FAQ baru untuk memandu pengguna dengan pertanyaan yang sering diajukan.')
            ->emptyStateIcon('lucide-help-circle')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Tambah FAQ')
                    ->icon('lucide-plus'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFaqs::route('/'),
        ];
    }
}
