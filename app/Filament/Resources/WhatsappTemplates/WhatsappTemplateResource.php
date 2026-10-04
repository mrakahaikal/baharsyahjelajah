<?php

namespace App\Filament\Resources\WhatsappTemplates;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Filament\Resources\WhatsappTemplates\Pages\ManageWhatsappTemplates;
use App\Filament\Resources\WhatsappTemplates\Schemas\WhatsappTemplateForm;
use App\Filament\Resources\WhatsappTemplates\Tables\WhatsappTemplatesTable;
use App\Models\WhatsappTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class WhatsappTemplateResource extends Resource
{
    protected static ?string $model = WhatsappTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = 'lucide-message-square';

    protected static ?string $navigationLabel = 'Template WhatsApp';

    protected static ?string $modelLabel = 'Template WhatsApp';

    protected static ?string $pluralModelLabel = 'Template WhatsApp';

    protected static ?string $recordTitleAttribute = 'product_type';

    protected static ?int $navigationSort = 5;

    protected static ?string $cluster = SettingsCluster::class;

    public static function form(Schema $schema): Schema
    {
        return WhatsappTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WhatsappTemplatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageWhatsappTemplates::route('/'),
        ];
    }
}
