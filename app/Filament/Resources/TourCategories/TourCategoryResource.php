<?php

namespace App\Filament\Resources\TourCategories;

use App\Filament\Resources\TourCategories\Pages\ManageTourCategories;
use App\Filament\Resources\TourCategories\Schemas\TourCategoryForm;
use App\Filament\Resources\TourCategories\Tables\TourCategoriesTable;
use App\Models\TourCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

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
        return TourCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TourCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTourCategories::route('/'),
        ];
    }
}
