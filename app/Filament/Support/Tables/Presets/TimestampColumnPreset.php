<?php

namespace App\Filament\Support\Tables\Presets;

use Filament\Tables\Columns\TextColumn;

class TimestampColumnPreset
{
    public static function createdAt(string $label = 'Tanggal Dibuat', string $format = 'd M Y H:i'): TextColumn
    {
        return TextColumn::make('created_at')
            ->label($label)
            ->dateTime($format)
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    public static function updatedAt(string $label = 'Pembaruan Terakhir', string $format = 'd M Y H:i'): TextColumn
    {
        return TextColumn::make('updated_at')
            ->label($label)
            ->dateTime($format)
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }
}
