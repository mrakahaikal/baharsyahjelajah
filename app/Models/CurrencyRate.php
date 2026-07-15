<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['from_currency', 'to_currency', 'rate', 'provider', 'source_updated_at', 'fetched_at'])]
class CurrencyRate extends Model
{
    protected function casts(): array
    {
        return [
            'rate' => 'decimal:8',
            'source_updated_at' => 'immutable_datetime',
            'fetched_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(static fn (): bool => static::forgetCache());
        static::deleted(static fn (): bool => static::forgetCache());
    }

    /**
     * Ambil semua rate (as simple array) dan cache selama 1 jam.
     */
    public static function getCached(): array
    {
        return cache()->remember('currency_rates_array', (int) config('currencies.cache_ttl', 3600), function (): array {
            return static::query()
                ->where('from_currency', config('currencies.base'))
                ->whereIn('to_currency', array_keys(config('currencies.supported', [])))
                ->pluck('rate', 'to_currency')
                ->all();
        });
    }

    /**
     * Update rate dan bust cache sekaligus.
     */
    public static function updateRate(string $toCurrency, float $rate): void
    {
        $baseCurrency = (string) config('currencies.base');

        static::updateOrCreate(
            ['from_currency' => $baseCurrency, 'to_currency' => $toCurrency],
            [
                'rate' => $rate,
                'provider' => 'manual',
                'source_updated_at' => null,
                'fetched_at' => now(),
            ]
        );
    }

    public static function forgetCache(): bool
    {
        return cache()->forget('currency_rates_array');
    }

    public function isStale(): bool
    {
        return $this->fetched_at === null
            || $this->fetched_at->lt(now()->subHours((int) config('currencies.stale_after_hours', 48)));
    }
}
