<?php

namespace App\Filament\Support\Forms\Presets;

use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;

class StatusTogglePreset
{
    public static function active(
        string $label = 'Status Aktif',
        string $name = 'is_active',
        ?string $helperText = null
    ): Toggle {
        $toggle = Toggle::make($name)
            ->label($label)
            ->default(true);

        if ($helperText !== null) {
            $toggle->helperText($helperText);
        }

        return $toggle;
    }

    public static function featured(
        string $label = 'Unggulan',
        string $name = 'is_featured',
        ?string $helperText = null
    ): Toggle {
        $toggle = Toggle::make($name)
            ->label($label)
            ->default(false);

        if ($helperText !== null) {
            $toggle->helperText($helperText);
        }

        return $toggle;
    }

    public static function group(
        string $activeLabel = 'Status Aktif',
        string $featuredLabel = 'Unggulan',
        string $activeName = 'is_active',
        string $featuredName = 'is_featured',
        ?string $activeHelperText = null,
        ?string $featuredHelperText = null,
        int $columns = 2
    ): Grid {
        return Grid::make($columns)
            ->schema([
                self::active($activeLabel, $activeName, $activeHelperText),
                self::featured($featuredLabel, $featuredName, $featuredHelperText),
            ]);
    }
}

