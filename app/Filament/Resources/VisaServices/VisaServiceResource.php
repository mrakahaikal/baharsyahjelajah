<?php

namespace App\Filament\Resources\VisaServices;

use App\Filament\Resources\VisaServices\Pages\CreateVisaService;
use App\Filament\Resources\VisaServices\Pages\EditVisaService;
use App\Filament\Resources\VisaServices\Pages\ListVisaServices;
use App\Filament\Resources\VisaServices\Pages\ViewVisaService;
use App\Filament\Resources\VisaServices\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\VisaServices\Schemas\VisaServiceForm;
use App\Filament\Resources\VisaServices\Schemas\VisaServiceInfolist;
use App\Filament\Resources\VisaServices\Tables\VisaServicesTable;
use App\Models\VisaService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VisaServiceResource extends Resource
{
    protected static ?string $model = VisaService::class;

    protected static string|BackedEnum|null $navigationIcon = 'lucide-stamp';

    protected static string|null|\UnitEnum $navigationGroup = 'Layanan Visa';

    protected static ?string $navigationLabel = 'Layanan Visa';

    protected static ?string $modelLabel = 'Layanan Visa';

    protected static ?string $pluralModelLabel = 'Layanan Visa';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return VisaServiceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return VisaServiceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VisaServicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVisaServices::route('/'),
            'create' => CreateVisaService::route('/create'),
            'view' => ViewVisaService::route('/{record}'),
            'edit' => EditVisaService::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
