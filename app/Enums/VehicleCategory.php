<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum VehicleCategory: string implements HasLabel
{
    case Car = 'car';
    case Minibus = 'minibus';
    case Bus = 'bus';

    public function getLabel(): string
    {
        return match ($this) {
            self::Car => 'Mobil',
            self::Minibus => 'Minibus',
            self::Bus => 'Bus',
        };
    }
}
