<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum UmrahDepartureStatus: string implements HasLabel
{
    case Open = 'open';
    case NearlyFull = 'nearly_full';
    case Full = 'full';
    case Closed = 'closed';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Open => 'Tersedia',
            self::NearlyFull => 'Hampir penuh',
            self::Full => 'Penuh',
            self::Closed => 'Ditutup',
        };
    }
}
