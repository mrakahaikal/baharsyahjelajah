<?php

namespace App\Actions;

use App\Contracts\ExchangeRateProvider;
use App\Models\CurrencyRate;
use App\Support\ExchangeRateSnapshot;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use UnexpectedValueException;

class SyncCurrencyRates
{
    public function __construct(private ExchangeRateProvider $provider) {}

    public function handle(): ExchangeRateSnapshot
    {
        return Cache::lock('currency-rates:sync', 30)->block(5, function (): ExchangeRateSnapshot {
            $baseCurrency = (string) config('currencies.base');
            $currencies = array_values(array_diff(
                array_keys(config('currencies.supported', [])),
                [$baseCurrency],
            ));
            $snapshot = $this->provider->latest($baseCurrency, $currencies);

            if ($snapshot->baseCurrency !== $baseCurrency) {
                throw new UnexpectedValueException('Base currency hasil sinkronisasi tidak sesuai konfigurasi.');
            }

            foreach ($currencies as $currency) {
                $rate = $snapshot->rates[$currency] ?? null;

                if (! is_numeric($rate) || ! is_finite((float) $rate) || (float) $rate <= 0) {
                    throw new UnexpectedValueException("Kurs {$currency} tidak lengkap atau tidak valid.");
                }
            }

            $now = now();

            DB::transaction(function () use ($snapshot, $now): void {
                $rows = collect($snapshot->rates)
                    ->map(fn (float $rate, string $currency): array => [
                        'from_currency' => $snapshot->baseCurrency,
                        'to_currency' => $currency,
                        'rate' => $rate,
                        'provider' => $snapshot->provider,
                        'source_updated_at' => $snapshot->sourceUpdatedAt,
                        'fetched_at' => $snapshot->fetchedAt,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])
                    ->values()
                    ->all();

                CurrencyRate::query()->upsert(
                    $rows,
                    ['from_currency', 'to_currency'],
                    ['rate', 'provider', 'source_updated_at', 'fetched_at', 'updated_at'],
                );
            });

            CurrencyRate::forgetCache();

            return $snapshot;
        });
    }
}
