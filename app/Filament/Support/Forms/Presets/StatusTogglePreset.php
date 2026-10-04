<?php

namespace App\Filament\Support\Forms\Presets;

use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;

class StatusTogglePreset
{
    public static function active(string $label = 'Status Aktif', string $name = 'is_active'): Toggle
    {
        return Toggle::make($name)
            ->label($label)
            ->default(true);
    }

    public static function featured(string $label = 'Unggulan', string $name = 'is_featured'): Toggle
    {
        return Toggle::make($name)
            ->label($label)
            ->default(false);
    }

    public static function group(
        string $activeLabel = 'Status Aktif',
        string $featuredLabel = 'Unggulan',
        string $activeName = 'is_active',
        string $featuredName = 'is_featured',
        int $columns = 2
    ): Grid {
        return Grid::make($columns)
            ->schema([
                self::active($activeLabel, $activeName),
                self::featured($featuredLabel, $featuredName),
            ]);
    }
}
