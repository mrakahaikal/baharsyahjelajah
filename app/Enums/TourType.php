<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TourType: string implements HasLabel
{
    case Domestic = 'domestic';
    case International = 'international';

    public function getLabel(): string|null|Htmlable
    {
        return match ($this) {
            self::Domestic => 'Domestik',
            self::International => 'Internasional',
        };
    }
}
