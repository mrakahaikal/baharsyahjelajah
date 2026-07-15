<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum FaqCategory: string implements HasLabel
{
    case General = 'general';
    case Tour = 'tour';
    case Umrah = 'umrah';
    case Vehicle = 'vehicle';
    case Payment = 'payment';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::General => 'Umum',
            self::Tour => 'Tur',
            self::Umrah => 'Umrah',
            self::Vehicle => 'Sewa Kendaraan',
            self::Payment => 'Pembayaran',
        };
    }
}
