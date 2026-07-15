<?php

namespace App\Models;

use App\Helpers\LocaleHelper;
use App\Services\CurrencyService;
use Database\Factories\UmrahDeparturePriceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['umrah_departure_id', 'umrah_package_price_id', 'price_idr'])]
class UmrahDeparturePrice extends Model
{
    /** @use HasFactory<UmrahDeparturePriceFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['price_idr' => 'integer'];
    }

    public function departure(): BelongsTo
    {
        return $this->belongsTo(UmrahDeparture::class, 'umrah_departure_id');
    }

    public function packagePrice(): BelongsTo
    {
        return $this->belongsTo(UmrahPackagePrice::class, 'umrah_package_price_id');
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
