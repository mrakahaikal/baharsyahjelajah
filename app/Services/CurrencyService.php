<?php

namespace App\Services;

use App\Models\CurrencyRate;

class CurrencyService
{
    private array $symbols = [
        'IDR' => 'Rp',
        'MYR' => 'RM',
        'SGD' => 'S$',
    ];

    /**
     * Convert amount dari IDR ke currency target.
     * Rate diambil dari DB dengan cache 1 jam.
     */
    public function convert(int $amountIdr, string $toCurrency): string
    {
        if ($toCurrency === 'IDR') {
            return $this->format($amountIdr, 'IDR');
        }

        $rates = CurrencyRate::getCached();
        $rate  = (float) ($rates[$toCurrency] ?? 0);

        if ($rate <= 0) {
            // Fallback: tampilkan IDR kalau rate tidak ada
            return $this->format($amountIdr, 'IDR');
        }

        return $this->format((int) round($amountIdr * $rate), $toCurrency);
    }

    /**
     * Hanya mengembalikan angka tanpa simbol — berguna untuk meta/schema.
     */
    public function convertRaw(int $amountIdr, string $toCurrency): float
    {
        if ($toCurrency === 'IDR') {
            return $amountIdr;
        }

        $rates = CurrencyRate::getCached();
        $rate  = (float) ($rates[$toCurrency] ?? 0);

        return round($amountIdr * $rate, 2);
    }

    private function format(int|float $amount, string $currency): string
    {
        $symbol = $this->symbols[$currency] ?? $currency;

        return match ($currency) {
            'IDR'   => $symbol . ' ' . number_format($amount, 0, ',', '.'),
            default => $symbol . ' ' . number_format($amount, 2),
        };
    }
}
