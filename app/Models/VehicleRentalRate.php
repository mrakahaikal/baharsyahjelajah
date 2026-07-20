<?php

namespace App\Models;

use App\Helpers\LocaleHelper;
use App\Services\CurrencyService;
use Carbon\CarbonInterface;
use Database\Factories\VehicleRentalRateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

#[Fillable([
    'vehicle_id', 'vehicle_rental_area_id', 'price_per_day_idr',
    'valid_from', 'valid_until', 'is_active',
])]
class VehicleRentalRate extends Model
{
    /** @use HasFactory<VehicleRentalRateFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (VehicleRentalRate $rate): void {
            if ($rate->valid_until && $rate->valid_from && $rate->valid_until->lt($rate->valid_from)) {
                throw ValidationException::withMessages([
                    'valid_until' => 'Tanggal akhir tarif tidak boleh sebelum tanggal mulai.',
                ]);
            }

            if (! $rate->is_active || ! $rate->vehicle_id || ! $rate->vehicle_rental_area_id || ! $rate->valid_from) {
                return;
            }

            $hasOverlap = static::query()
                ->whereKeyNot($rate->getKey())
                ->where('vehicle_id', $rate->vehicle_id)
                ->where('vehicle_rental_area_id', $rate->vehicle_rental_area_id)
                ->active()
                ->where(function (Builder $query) use ($rate): void {
                    $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', $rate->valid_from);
                })
                ->when($rate->valid_until, fn (Builder $query) => $query->whereDate('valid_from', '<=', $rate->valid_until))
                ->exists();

            if ($hasOverlap) {
                throw ValidationException::withMessages([
                    'valid_from' => 'Periode tarif bertumpang tindih dengan tarif aktif lain untuk wilayah ini.',
                ]);
            }
        });
    }

    protected $attributes = [
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'price_per_day_idr' => 'integer',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(VehicleRentalArea::class, 'vehicle_rental_area_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeEffectiveOn(Builder $query, CarbonInterface|string $date): Builder
    {
        return $query
            ->whereDate('valid_from', '<=', $date)
            ->where(function (Builder $query) use ($date): void {
                $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', $date);
            });
    }

    public function scopeForArea(Builder $query, VehicleRentalArea|int $area): Builder
    {
        return $query->where('vehicle_rental_area_id', $area instanceof VehicleRentalArea ? $area->id : $area);
    }

    public function getFormattedPriceAttribute(): string
    {
        return app(CurrencyService::class)->convert($this->price_per_day_idr, LocaleHelper::currency());
    }
}
