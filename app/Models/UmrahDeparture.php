<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'package_id', 'departure_date', 'return_date', 'quota_total',
    'quota_booked', 'status', 'price_override_idr',
])]
class UmrahDeparture extends Model
{
    protected function casts(): array
    {
        return [
            'departure_date'     => 'date',
            'return_date'        => 'date',
            'quota_total'        => 'integer',
            'quota_booked'       => 'integer',
            'price_override_idr' => 'integer',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(UmrahPackage::class, 'package_id');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('departure_date', '>=', now())
            ->where('status', '!=', 'closed')
            ->orderBy('departure_date');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function getQuotaSisaAttribute(): int
    {
        return max(0, $this->quota_total - $this->quota_booked);
    }

    public function getIsFullAttribute(): bool
    {
        return $this->quota_sisa <= 0;
    }

    /**
     * Persentase kuota yang sudah terisi — berguna untuk progress bar.
     */
    public function getQuotaPercentageAttribute(): int
    {
        if ($this->quota_total <= 0) return 0;

        return (int) round(($this->quota_booked / $this->quota_total) * 100);
    }

    /**
     * Update status otomatis berdasarkan sisa kuota.
     * Dipanggil setiap kali admin update quota_booked.
     */
    public function syncStatus(): void
    {
        $percentage = $this->quota_percentage;

        $this->status = match (true) {
            $percentage >= 100 => 'full',
            $percentage >= 80  => 'nearly_full',
            default            => 'open',
        };

        $this->save();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open'        => 'Tersedia',
            'nearly_full' => 'Hampir Penuh',
            'full'        => 'Penuh',
            'closed'      => 'Ditutup',
            default       => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open'        => 'success',
            'nearly_full' => 'warning',
            'full'        => 'danger',
            default       => 'gray',
        };
    }
}
