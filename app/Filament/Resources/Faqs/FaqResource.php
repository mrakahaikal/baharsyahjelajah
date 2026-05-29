<?php

namespace App\Filament\Resources\Faqs;

use App\Filament\Resources\Faqs\Pages\ManageFaqs;
use App\Models\Faq;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
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
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

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
                    ->icon(Heroicon::OutlinedQuestionMarkCircle)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('category')
                                ->label('Kategori')
                                ->options([
                                    'general' => 'Umum',
                                    'tour' => 'Tur',
                                    'umrah' => 'Umrah',
                                    'vehicle' => 'Sewa Kendaraan',
                                    'payment' => 'Pembayaran',
                                ])
                                ->searchable(),
                            TextInput::make('sort_order')
                                ->label('Urutan')
                                ->numeric()
                                ->default(0),
                        ]),
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                TextInput::make('question')
                                    ->label('Pertanyaan')
                                    ->placeholder('Masukkan pertanyaan...')
                                    ->required($locale === 'id')
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                                TextInput::make('answer')
                                    ->label('Jawaban')
                                    ->placeholder('Masukkan jawaban...')
                                    ->required($locale === 'id')
                                    ->maxLength(2000)
                                    ->columnSpanFull(),
                            ]),
                        Toggle::make('is_active')
                            ->label('Aktif')
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
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'general' => 'Umum',
                        'tour' => 'Tur',
                        'umrah' => 'Umrah',
                        'vehicle' => 'Sewa Kendaraan',
                        'payment' => 'Pembayaran',
                        default => $state ?? '-',
                    }),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'general' => 'Umum',
                        'tour' => 'Tur',
                        'umrah' => 'Umrah',
                        'vehicle' => 'Sewa Kendaraan',
                        'payment' => 'Pembayaran',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Ubah'),
                DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus Terpilih'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFaqs::route('/'),
        ];
    }
}
