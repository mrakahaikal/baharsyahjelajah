<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum VisaEntryType: string implements HasLabel
{
    case Single = 'single';
    case Multiple = 'multiple';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Single => 'Sekali Masuk',
            self::Multiple => 'Beberapa Kali Masuk',
        };
    }
}
