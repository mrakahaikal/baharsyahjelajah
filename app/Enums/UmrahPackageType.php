<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum UmrahPackageType: string implements HasLabel
{
    case Regular = 'regular';
    case Plus = 'plus';
    case Vip = 'vip';
    case Ramadan = 'ramadan';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Regular => 'Reguler',
            self::Plus => 'Plus',
            self::Vip => 'VIP',
            self::Ramadan => 'Ramadan',
        };
    }
}
