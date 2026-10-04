<?php

namespace App\Filament\Support\Tables\Presets;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;

class StatusColumnPreset
{
    public static function active(string $label = 'Aktif', string $name = 'is_active'): IconColumn
    {
        return IconColumn::make($name)
            ->label($label)
            ->boolean()
            ->alignCenter();
    }

    public static function featured(string $label = 'Unggulan', string $name = 'is_featured'): IconColumn
    {
        return IconColumn::make($name)
            ->label($label)
            ->boolean()
            ->alignCenter();
    }

    public static function filterActive(string $label = 'Status Aktif', string $name = 'is_active'): TernaryFilter
    {
        return TernaryFilter::make($name)
            ->label($label)
            ->native(false);
    }

    public static function filterFeatured(string $label = 'Destinasi Unggulan', string $name = 'is_featured'): TernaryFilter
    {
        return TernaryFilter::make($name)
            ->label($label)
            ->native(false);
    }
}
