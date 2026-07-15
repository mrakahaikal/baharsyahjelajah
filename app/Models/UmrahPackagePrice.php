<?php

namespace App\Models;

use App\Helpers\LocaleHelper;
use App\Services\CurrencyService;
use Database\Factories\UmrahPackagePriceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['umrah_package_id', 'room_type', 'price_idr'])]
class UmrahPackagePrice extends Model
{
    /** @use HasFactory<UmrahPackagePriceFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['price_idr' => 'integer'];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(UmrahPackage::class, 'umrah_package_id');
    }

    public function departurePrices(): HasMany
    {
        return $this->hasMany(UmrahDeparturePrice::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return app(CurrencyService::class)->convert($this->price_idr, LocaleHelper::currency());
    }

    public function formattedTotal(int $pax): string
    {
        return app(CurrencyService::class)->convert($this->price_idr * $pax, LocaleHelper::currency());
    }
}
