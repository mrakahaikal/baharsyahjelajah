<?php

namespace App\Filament\Resources\TourCategories;

use App\Filament\Resources\TourCategories\Pages\ManageTourCategories;
use App\Models\TourCategory;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class TourCategoryResource extends Resource
{
    protected static ?string $model = TourCategory::class;

    protected static string|BackedEnum|null $navigationIcon = 'lucide-tags';

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

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('sort_order', 'asc')
            ->columns([
                IconColumn::make('icon')
                    ->label('Ikon')
                    ->icon(fn (string $state): string => $state ?: 'lucide-square-dashed')
                    ->color('primary'),
                TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug URL')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            ->emptyStateHeading('Belum Ada Kategori Tur')
            ->emptyStateDescription('Buat kategori baru untuk mulai mengelompokkan paket perjalanan wisata Anda.')
            ->emptyStateIcon('lucide-tag')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Tambah Kategori')
                    ->icon('lucide-plus'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTourCategories::route('/'),
        ];
    }
}
