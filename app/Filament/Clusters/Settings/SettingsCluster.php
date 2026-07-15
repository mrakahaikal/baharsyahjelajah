<?php

namespace App\Filament\Clusters\Settings;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class SettingsCluster extends Cluster
{
    protected static string|null|BackedEnum $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|null|\UnitEnum $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Pengaturan';

    protected static ?string $title = 'Pengaturan';

    protected static ?int $navigationSort = 100;
}
