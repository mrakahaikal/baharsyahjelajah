<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum UmrahRoomType: string implements HasLabel
{
    case Quad = 'quad';
    case Triple = 'triple';
    case Double = 'double';
    case Single = 'single';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Quad => 'Quad (4 orang)',
            self::Triple => 'Triple (3 orang)',
            self::Double => 'Double (2 orang)',
            self::Single => 'Single (1 orang)',
        };
    }
}
