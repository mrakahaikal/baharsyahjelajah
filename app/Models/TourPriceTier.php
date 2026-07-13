<?php

namespace App\Models;

use App\Helpers\LocaleHelper;
use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['package_tier_id', 'min_pax', 'max_pax', 'price', 'currency'])]
class TourPriceTier extends Model
{
    protected function casts(): array
    {
        return [
            'min_pax' => 'integer',
            'max_pax' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    public function packageTier(): BelongsTo
    {
        return $this->belongsTo(PackageTier::class);
    }

    /**
     * Harga sudah diformat sesuai currency aktif di session.
     * Contoh output: "Rp 1.500.000" atau "RM 438.00"
     */
    public function getFormattedPriceAttribute(): string
    {
        $currency = LocaleHelper::currency();

        return app(CurrencyService::class)->convert((float) $this->price, $currency, $this->currency);
    }

    public function formattedTotalForPax(int $pax): string
    {
        $currency = LocaleHelper::currency();

        return app(CurrencyService::class)->convert(
            (float) $this->price * $pax,
            $currency,
            $this->currency,
        );
    }
}
