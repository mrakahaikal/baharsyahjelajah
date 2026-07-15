<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum FaqContext: string implements HasLabel
{
    case Home = 'home';
    case Tour = 'tour';
    case Umrah = 'umrah';
    case Vehicle = 'vehicle';
    case Booking = 'booking';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Home => 'Beranda',
            self::Tour => 'Tur',
            self::Umrah => 'Umrah',
            self::Vehicle => 'Sewa Kendaraan',
            self::Booking => 'Pemesanan',
        };
    }
}
