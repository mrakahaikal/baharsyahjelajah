<?php

namespace App\Filament\Resources\UmrahPackages;

use App\Filament\Resources\UmrahPackages\Pages\CreateUmrahPackage;
use App\Filament\Resources\UmrahPackages\Pages\EditUmrahPackage;
use App\Filament\Resources\UmrahPackages\Pages\ListUmrahPackages;
use App\Filament\Resources\UmrahPackages\Pages\ViewUmrahPackage;
use App\Filament\Resources\UmrahPackages\Schemas\UmrahPackageForm;
use App\Filament\Resources\UmrahPackages\Schemas\UmrahPackageInfolist;
use App\Filament\Resources\UmrahPackages\Tables\UmrahPackagesTable;
use App\Models\UmrahPackage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UmrahPackageResource extends Resource
{
    protected static ?string $model = UmrahPackage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|null|\UnitEnum $navigationGroup = 'Layanan Umrah';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Paket Umrah';

    protected static ?string $modelLabel = 'Paket Umrah';

    protected static ?string $pluralModelLabel = 'Paket Umrah';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return UmrahPackageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UmrahPackageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UmrahPackagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DeparturesRelationManager::class,
            RelationManagers\IncludesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUmrahPackages::route('/'),
            'create' => CreateUmrahPackage::route('/create'),
            'view' => ViewUmrahPackage::route('/{record}'),
            'edit' => EditUmrahPackage::route('/{record}/edit'),
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
