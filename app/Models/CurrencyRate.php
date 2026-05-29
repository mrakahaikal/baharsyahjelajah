<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['from_currency', 'to_currency', 'rate',])]
class CurrencyRate extends Model
{
    protected function casts(): array
    {
        return [
            'rate' => 'decimal:8',
        ];
    }

    /**
     * Ambil semua rate (as simple array) dan cache selama 1 jam.
     */
    public static function getCached(): array
    {
        return cache()->remember('currency_rates_array', now()->addHour(), function () {
            return static::all()->pluck('rate', 'to_currency')->toArray();
        });
    }

    /**
     * Update rate dan bust cache sekaligus.
     */
    public static function updateRate(string $toCurrency, float $rate): void
    {
        static::updateOrCreate(
            ['from_currency' => 'IDR', 'to_currency' => $toCurrency],
            ['rate' => $rate]
        );

        cache()->forget('currency_rates_array');
    }
}
