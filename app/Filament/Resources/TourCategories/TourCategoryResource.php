<?php

namespace App\Filament\Resources\TourCategories;

use App\Filament\Resources\TourCategories\Pages\ManageTourCategories;
use App\Models\TourCategory;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class TourCategoryResource extends Resource
{
    protected static ?string $model = TourCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static string|null|\UnitEnum $navigationGroup = 'Manajemen Tur';

    protected static ?string $navigationLabel = 'Kategori Tur';

    protected static ?string $modelLabel = 'Kategori Tur';

    protected static ?string $pluralModelLabel = 'Kategori Tur';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kategori')
                    ->description('Kelola detail informasi dan tampilan kategori tur.')
                    ->icon(Heroicon::OutlinedTag)
                    ->columnSpanFull()
                    ->schema([
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nama')
                                            ->placeholder('Masukkan nama kategori...')
                                            ->required($locale === 'id')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, Set $set) => $set('slug', Str::slug($state)))
                                            ->maxLength(255),
                                        TextInput::make('slug')
                                            ->label('Slug')
                                            ->placeholder('slug-kategori')
                                            ->required($locale === 'id')
                                            ->maxLength(255),
                                    ]),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('icon')
                                    ->label('Ikon')
                                    ->placeholder('heroicon-o-tag')
                                    ->maxLength(255),
                                TextInput::make('sort_order')
                                    ->label('Urutan')
                                    ->placeholder('0')
                                    ->numeric()
                                    ->default(0),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('sort_order', 'asc')
            ->columns([
                IconColumn::make('icon')
                    ->label('Ikon')
                    ->icon(fn (string $state): string|Heroicon => $state ?: Heroicon::OutlinedRectangleStack)
                    ->color('primary'),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Ubah'),
                DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTourCategories::route('/'),
        ];
    }
}
