<?php

use App\Actions\SyncCurrencyRates;
use App\Filament\Resources\CurrencyRates\Pages\ManageCurrencyRates;
use App\Models\CurrencyRate;
use App\Models\User;
use App\Services\CurrencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'currencies.provider.key' => 'test-api-key',
        'currencies.provider.base_url' => 'https://rates.test/v6',
    ]);
});

it('converts and formats the configured currencies through the IDR base rate', function () {
    CurrencyRate::updateRate('USD', 0.00006250);
    CurrencyRate::updateRate('EUR', 0.00005700);
    CurrencyRate::updateRate('JPY', 0.00950000);

    $service = app(CurrencyService::class);

    expect($service->convert(16_000_000, 'EUR'))->toBe('€ 912.00')
        ->and($service->convert(1_000, 'EUR', 'USD'))->toBe('€ 912.00')
        ->and($service->convert(16_000_000, 'JPY'))->toBe('¥ 152,000');
});

it('falls back to the source currency instead of mislabelling a missing rate', function () {
    expect(app(CurrencyService::class)->convert(16_000_000, 'SAR'))
        ->toBe('Rp 16.000.000');
});

it('synchronizes every configured target currency atomically', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://rates.test/v6/test-api-key/latest/IDR' => Http::response(currencyProviderPayload()),
    ]);

    $snapshot = app(SyncCurrencyRates::class)->handle();

    expect($snapshot->rates)->toHaveCount(10)
        ->and(CurrencyRate::query()->count())->toBe(10)
        ->and(CurrencyRate::query()->where('to_currency', 'SAR')->value('provider'))->toBe('exchange-rate-api')
        ->and(CurrencyRate::query()->whereNotNull('fetched_at')->count())->toBe(10);

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://rates.test/v6/test-api-key/latest/IDR');
});

it('preserves the last valid rates when the provider response is incomplete', function () {
    CurrencyRate::updateRate('USD', 0.00006250);

    $payload = currencyProviderPayload();
    unset($payload['conversion_rates']['SAR']);

    Http::preventStrayRequests();
    Http::fake([
        'https://rates.test/v6/test-api-key/latest/IDR' => Http::response($payload),
    ]);

    expect(fn () => app(SyncCurrencyRates::class)->handle())
        ->toThrow(UnexpectedValueException::class, 'SAR');

    expect(CurrencyRate::query()->count())->toBe(1)
        ->and(CurrencyRate::query()->where('to_currency', 'USD')->value('rate'))->toBe('0.00006250');
});

it('preserves rates and does not expose the API key when the provider fails', function () {
    CurrencyRate::updateRate('USD', 0.00006250);

    Http::fake([
        'https://rates.test/v6/test-api-key/latest/IDR' => Http::response([], 500),
    ]);

    $caughtException = null;

    try {
        app(SyncCurrencyRates::class)->handle();
    } catch (RuntimeException $exception) {
        $caughtException = $exception;
    }

    expect($caughtException)->toBeInstanceOf(RuntimeException::class)
        ->and($caughtException?->getMessage())->toBe('Provider kurs merespons HTTP 500.')
        ->not->toContain('test-api-key')
        ->and(CurrencyRate::query()->count())->toBe(1)
        ->and(CurrencyRate::query()->where('to_currency', 'USD')->value('rate'))->toBe('0.00006250');
});

it('runs synchronization from the artisan command', function () {
    Http::fake([
        'https://rates.test/v6/test-api-key/latest/IDR' => Http::response(currencyProviderPayload()),
    ]);

    $this->artisan('currency-rates:sync')
        ->expectsOutputToContain('10 kurs berhasil disinkronkan')
        ->assertSuccessful();
});

it('allows an administrator to synchronize rates from Filament', function () {
    $this->actingAs(User::factory()->create(['email' => 'admin@baharsyahjelajah.com']));

    Http::fake([
        'https://rates.test/v6/test-api-key/latest/IDR' => Http::response(currencyProviderPayload()),
    ]);

    Livewire::test(ManageCurrencyRates::class)
        ->callAction('syncCurrencyRates')
        ->assertNotified('Kurs berhasil disinkronkan');

    expect(CurrencyRate::query()->count())->toBe(10);
});

/** @return array<string, mixed> */
function currencyProviderPayload(): array
{
    $rates = collect(array_keys(config('currencies.supported')))
        ->mapWithKeys(fn (string $currency, int $index): array => [
            $currency => $currency === 'IDR' ? 1 : ($index + 1) / 100_000,
        ])
        ->all();

    return [
        'result' => 'success',
        'base_code' => 'IDR',
        'time_last_update_unix' => 1_752_537_600,
        'conversion_rates' => $rates,
    ];
}
