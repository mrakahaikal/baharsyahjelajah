<?php

namespace App\Models;

use Database\Factories\UmrahDepartureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'package_id', 'departure_date', 'return_date', 'quota_total',
    'quota_booked', 'status', 'price_override_idr',
])]
class UmrahDeparture extends Model
{
    /** @use HasFactory<UmrahDepartureFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (UmrahDeparture $departure): void {
            if ($departure->status !== 'closed') {
                $departure->status = $departure->calculatedStatus();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'return_date' => 'date',
            'quota_total' => 'integer',
            'quota_booked' => 'integer',
            'price_override_idr' => 'integer',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(UmrahPackage::class, 'package_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(UmrahDeparturePrice::class);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('departure_date', '>=', now())
            ->where('status', '!=', 'closed')
            ->orderBy('departure_date');
    }

    public function scopeOpen(Builder $query): Builder
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
        if ($this->quota_total <= 0) {
            return 0;
        }

        return (int) round(($this->quota_booked / $this->quota_total) * 100);
    }

    /**
     * Update status otomatis berdasarkan sisa kuota.
     * Dipanggil setiap kali admin update quota_booked.
     */
    public function syncStatus(): void
    {
        $this->save();
    }

    public function calculatedStatus(): string
    {
        return match (true) {
            $this->quota_percentage >= 100 => 'full',
            $this->quota_percentage >= 80 => 'nearly_full',
            default => 'open',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Tersedia',
            'nearly_full' => 'Hampir Penuh',
            'full' => 'Penuh',
            'closed' => 'Ditutup',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open' => 'success',
            'nearly_full' => 'warning',
            'full' => 'danger',
            default => 'gray',
        };
    }
}
