<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum BannerCtaType: string implements HasLabel
{
    case Route = 'route';
    case Url = 'url';
    case Whatsapp = 'whatsapp';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Route => 'Halaman Website',
            self::Url => 'URL Eksternal',
            self::Whatsapp => 'WhatsApp',
        };
    }
}
