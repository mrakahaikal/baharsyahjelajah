<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum BannerPlacement: string implements HasLabel
{
    case HomeHero = 'home_hero';
    case HomePromo = 'home_promo';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::HomeHero => 'Hero Beranda',
            self::HomePromo => 'Promosi Beranda',
        };
    }
}
