<?php

namespace App\Filament\Resources\VehicleRentalTerms;

use App\Filament\Resources\VehicleRentalTerms\Pages\CreateVehicleRentalTerm;
use App\Filament\Resources\VehicleRentalTerms\Pages\EditVehicleRentalTerm;
use App\Filament\Resources\VehicleRentalTerms\Pages\ListVehicleRentalTerms;
use App\Filament\Resources\VehicleRentalTerms\Pages\ViewVehicleRentalTerm;
use App\Filament\Resources\VehicleRentalTerms\Schemas\VehicleRentalTermForm;
use App\Filament\Resources\VehicleRentalTerms\Schemas\VehicleRentalTermInfolist;
use App\Filament\Resources\VehicleRentalTerms\Tables\VehicleRentalTermsTable;
use App\Models\VehicleRentalTerm;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class VehicleRentalTermResource extends Resource
{
    protected static ?string $model = VehicleRentalTerm::class;

    protected static string|BackedEnum|null $navigationIcon = 'lucide-clipboard-check';

    protected static string|null|\UnitEnum $navigationGroup = 'Layanan Transportasi';

    protected static ?string $navigationLabel = 'Ketentuan Sewa';

    protected static ?string $modelLabel = 'Ketentuan Sewa';

    protected static ?string $pluralModelLabel = 'Ketentuan Sewa';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return VehicleRentalTermForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return VehicleRentalTermInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VehicleRentalTermsTable::configure($table);
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
            'index' => ListVehicleRentalTerms::route('/'),
            'create' => CreateVehicleRentalTerm::route('/create'),
            'view' => ViewVehicleRentalTerm::route('/{record}'),
            'edit' => EditVehicleRentalTerm::route('/{record}/edit'),
        ];
    }
}
