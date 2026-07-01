<?php

namespace App\Services;

use App\Models\CurrencyRate;

class CurrencyService
{
    private array $symbols = [
        'IDR' => 'Rp',
        'MYR' => 'RM',
        'SGD' => 'S$',
        'USD' => '$',
    ];

    /**
     * Convert amount dari currency asal ke currency target.
     * Rate diambil dari DB dengan cache 1 jam.
     */
    public function convert(int|float $amount, string $toCurrency, ?string $fromCurrency = null): string
    {
        $fromCurrency = $fromCurrency ?? 'IDR';

        if ($fromCurrency === $toCurrency) {
            return $this->format($amount, $toCurrency);
        }

        $converted = $this->convertRaw($amount, $toCurrency, $fromCurrency);

        return $this->format($converted, $toCurrency);
    }

    /**
     * Hanya mengembalikan angka tanpa simbol — berguna untuk meta/schema.
     */
    public function convertRaw(int|float $amount, string $toCurrency, ?string $fromCurrency = null): float
    {
        $fromCurrency = $fromCurrency ?? 'IDR';

        if ($fromCurrency === $toCurrency) {
            return (float) $amount;
        }

        $rates = CurrencyRate::getCached();

        // 1. Convert source currency to IDR
        $amountInIdr = (float) $amount;
        if ($fromCurrency !== 'IDR') {
            $fromRate = (float) ($rates[$fromCurrency] ?? 0);
            if ($fromRate > 0) {
                $amountInIdr = $amount / $fromRate;
            }
        }

        // 2. Convert IDR to target currency
        if ($toCurrency === 'IDR') {
            return round($amountInIdr, 2);
        }

        $toRate = (float) ($rates[$toCurrency] ?? 0);
        if ($toRate > 0) {
            return round($amountInIdr * $toRate, 2);
        }

        return round($amountInIdr, 2);
    }

    private function format(int|float $amount, string $currency): string
    {
        $symbol = $this->symbols[$currency] ?? $currency;

        return match ($currency) {
            'IDR' => $symbol.' '.number_format($amount, 0, ',', '.'),
            default => $symbol.' '.number_format($amount, 2),
        };
    }
}
