<?php

namespace App\Services;

use App\Exceptions\MissingCurrencyRateException;
use App\Models\CurrencyRate;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    public function convert(int|float $amount, string $toCurrency, ?string $fromCurrency = null): string
    {
        $fromCurrency = strtoupper($fromCurrency ?? (string) config('currencies.base'));
        $toCurrency = strtoupper($toCurrency);

        try {
            $converted = $this->convertRaw($amount, $toCurrency, $fromCurrency);
        } catch (MissingCurrencyRateException $exception) {
            if (Cache::add('currency-rate-warning:'.$fromCurrency.':'.$toCurrency, true, now()->addHour())) {
                logger()->warning($exception->getMessage(), [
                    'from_currency' => $fromCurrency,
                    'to_currency' => $toCurrency,
                ]);
            }

            return $this->format($amount, $fromCurrency);
        }

        return $this->format($converted, $toCurrency);
    }

    public function convertRaw(int|float $amount, string $toCurrency, ?string $fromCurrency = null): float
    {
        $baseCurrency = (string) config('currencies.base');
        $fromCurrency = strtoupper($fromCurrency ?? $baseCurrency);
        $toCurrency = strtoupper($toCurrency);

        if ($fromCurrency === $toCurrency) {
            return (float) $amount;
        }

        $rates = CurrencyRate::getCached();
        $amountInBaseCurrency = (float) $amount;

        if ($fromCurrency !== $baseCurrency) {
            $fromRate = (float) ($rates[$fromCurrency] ?? 0);

            if ($fromRate <= 0) {
                throw MissingCurrencyRateException::between($fromCurrency, $baseCurrency);
            }

            $amountInBaseCurrency = $amount / $fromRate;
        }

        if ($toCurrency === $baseCurrency) {
            return round($amountInBaseCurrency, 2);
        }

        $toRate = (float) ($rates[$toCurrency] ?? 0);

        if ($toRate <= 0) {
            throw MissingCurrencyRateException::between($baseCurrency, $toCurrency);
        }

        return round($amountInBaseCurrency * $toRate, 2);
    }

    private function format(int|float $amount, string $currency): string
    {
        $metadata = config("currencies.supported.{$currency}", []);
        $symbol = $metadata['symbol'] ?? $currency;
        $decimals = (int) ($metadata['decimals'] ?? 2);

        if ($currency === config('currencies.base')) {
            return $symbol.' '.number_format($amount, $decimals, ',', '.');
        }

        return $symbol.' '.number_format($amount, $decimals, '.', ',');
    }
}
