<?php

namespace App\Filament\Resources\PostCategories;

use App\Filament\Resources\PostCategories\Pages\ManagePostCategories;
use App\Models\PostCategory;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class PostCategoryResource extends Resource
{
    protected static ?string $model = PostCategory::class;

    protected static string|BackedEnum|null $navigationIcon = 'lucide-tag';

    protected static string|null|\UnitEnum $navigationGroup = 'Manajemen Blog';

    protected static ?string $navigationLabel = 'Kategori Blog';

    protected static ?string $modelLabel = 'Kategori Blog';

    protected static ?string $pluralModelLabel = 'Kategori Blog';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
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

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug URL')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('posts_count')
                    ->label('Jumlah Artikel')
                    ->counts('posts')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
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
            ->emptyStateHeading('Belum Ada Kategori Artikel')
            ->emptyStateDescription('Buat kategori blog baru untuk mulai mengelompokkan penulisan artikel Anda.')
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
            'index' => ManagePostCategories::route('/'),
        ];
    }
}
