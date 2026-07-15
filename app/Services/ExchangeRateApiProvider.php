<?php

namespace App\Services;

use App\Contracts\ExchangeRateProvider;
use App\Support\ExchangeRateSnapshot;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;
use UnexpectedValueException;

class ExchangeRateApiProvider implements ExchangeRateProvider
{
    public function latest(string $baseCurrency, array $currencies): ExchangeRateSnapshot
    {
        $apiKey = config('currencies.provider.key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('EXCHANGE_RATE_API_KEY belum dikonfigurasi.');
        }

        try {
            $response = Http::acceptJson()
                ->connectTimeout((int) config('currencies.provider.connect_timeout', 5))
                ->timeout((int) config('currencies.provider.timeout', 10))
                ->retry(
                    [250, 750],
                    when: static fn (Throwable $exception): bool => $exception instanceof ConnectionException
                        || ($exception instanceof RequestException && $exception->response->serverError()),
                    throw: false,
                )
                ->get(sprintf(
                    '%s/%s/latest/%s',
                    rtrim((string) config('currencies.provider.base_url'), '/'),
                    $apiKey,
                    $baseCurrency,
                ));
        } catch (ConnectionException) {
            throw new RuntimeException('Tidak dapat terhubung ke provider kurs.');
        }

        if ($response->failed()) {
            throw new RuntimeException("Provider kurs merespons HTTP {$response->status()}.");
        }

        if ($response->json('result') !== 'success') {
            throw new UnexpectedValueException('Provider kurs menolak permintaan: '.($response->json('error-type') ?? 'unknown-error'));
        }

        if ($response->json('base_code') !== $baseCurrency) {
            throw new UnexpectedValueException('Base currency dari provider tidak sesuai.');
        }

        $providerRates = $response->json('conversion_rates');

        if (! is_array($providerRates)) {
            throw new UnexpectedValueException('Provider tidak mengembalikan daftar kurs yang valid.');
        }

        $rates = [];

        foreach ($currencies as $currency) {
            $rate = $providerRates[$currency] ?? null;

            if (! is_numeric($rate) || ! is_finite((float) $rate) || (float) $rate <= 0) {
                throw new UnexpectedValueException("Kurs {$currency} tidak tersedia atau tidak valid.");
            }

            $rates[$currency] = (float) $rate;
        }

        $sourceUpdatedAt = $response->json('time_last_update_unix');

        if (! is_numeric($sourceUpdatedAt)) {
            throw new UnexpectedValueException('Waktu pembaruan provider tidak tersedia.');
        }

        return new ExchangeRateSnapshot(
            provider: (string) config('currencies.provider.name'),
            baseCurrency: $baseCurrency,
            rates: $rates,
            sourceUpdatedAt: CarbonImmutable::createFromTimestampUTC((int) $sourceUpdatedAt),
            fetchedAt: CarbonImmutable::now(),
        );
    }
}
