<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum VisaItemType: string implements HasLabel
{
    case Requirement = 'requirement';
    case Term = 'term';
    case Included = 'included';
    case Excluded = 'excluded';
    case Note = 'note';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Requirement => 'Persyaratan Berkas',
            self::Term => 'Ketentuan',
            self::Included => 'Termasuk Layanan',
            self::Excluded => 'Tidak Termasuk',
            self::Note => 'Catatan',
        };
    }
}
