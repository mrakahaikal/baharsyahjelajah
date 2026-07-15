<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum UmrahItemType: string implements HasLabel
{
    case Include = 'include';
    case Exclude = 'exclude';
    case Requirement = 'requirement';
    case Note = 'note';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Include => 'Termasuk',
            self::Exclude => 'Tidak termasuk',
            self::Requirement => 'Persyaratan',
            self::Note => 'Catatan penting',
        };
    }
}
