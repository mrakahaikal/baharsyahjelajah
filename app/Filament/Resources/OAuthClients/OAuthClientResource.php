<?php

declare(strict_types=1);

namespace App\Filament\Resources\OAuthClients;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Filament\Resources\OAuthClients\Pages\ManageOAuthClients;
use App\Filament\Resources\OAuthClients\Schemas\OAuthClientForm;
use App\Filament\Resources\OAuthClients\Tables\OAuthClientsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Laravel\Passport\Client;

class OAuthClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static string|BackedEnum|null $navigationIcon = 'lucide-key';

    protected static ?string $cluster = SettingsCluster::class;

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Klien AI & OAuth';

    protected static ?string $modelLabel = 'Klien OAuth';

    protected static ?string $pluralModelLabel = 'Klien AI & OAuth';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return OAuthClientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OAuthClientsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOAuthClients::route('/'),
        ];
    }
}

