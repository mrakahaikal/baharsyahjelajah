<?php

namespace App\Filament\Resources\VehicleRentalAreas;

use App\Filament\Resources\VehicleRentalAreas\Pages\CreateVehicleRentalArea;
use App\Filament\Resources\VehicleRentalAreas\Pages\EditVehicleRentalArea;
use App\Filament\Resources\VehicleRentalAreas\Pages\ListVehicleRentalAreas;
use App\Filament\Resources\VehicleRentalAreas\Pages\ViewVehicleRentalArea;
use App\Filament\Resources\VehicleRentalAreas\Schemas\VehicleRentalAreaForm;
use App\Filament\Resources\VehicleRentalAreas\Schemas\VehicleRentalAreaInfolist;
use App\Filament\Resources\VehicleRentalAreas\Tables\VehicleRentalAreasTable;
use App\Models\VehicleRentalArea;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VehicleRentalAreaResource extends Resource
{
    protected static ?string $model = VehicleRentalArea::class;

    protected static string|BackedEnum|null $navigationIcon = 'lucide-map-pinned';

    protected static string|null|\UnitEnum $navigationGroup = 'Layanan Transportasi';

    protected static ?string $navigationLabel = 'Wilayah Sewa';

    protected static ?string $modelLabel = 'Wilayah Sewa';

    protected static ?string $pluralModelLabel = 'Wilayah Sewa';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return VehicleRentalAreaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return VehicleRentalAreaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VehicleRentalAreasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVehicleRentalAreas::route('/'),
            'create' => CreateVehicleRentalArea::route('/create'),
            'view' => ViewVehicleRentalArea::route('/{record}'),
            'edit' => EditVehicleRentalArea::route('/{record}/edit'),
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
