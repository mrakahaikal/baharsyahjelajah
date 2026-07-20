<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum VehicleRentalTermType: string implements HasLabel
{
    case UsageArea = 'usage_area';
    case OperatingHours = 'operating_hours';
    case Included = 'included';
    case Excluded = 'excluded';
    case Booking = 'booking';
    case Reschedule = 'reschedule';

    public function getLabel(): string
    {
        return match ($this) {
            self::UsageArea => 'Wilayah Penggunaan',
            self::OperatingHours => 'Waktu Operasional',
            self::Included => 'Harga Termasuk',
            self::Excluded => 'Harga Tidak Termasuk',
            self::Booking => 'Pemesanan',
            self::Reschedule => 'Perubahan Jadwal',
        };
    }
}
