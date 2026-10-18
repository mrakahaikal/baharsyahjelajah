<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Models\CurrencyRate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('currency_rates')]
#[Title('Nilai Tukar Kurs Mata Uang')]
#[Description('Nilai tukar mata uang terkini terhadap mata uang dasar (IDR) untuk seluruh mata uang yang didukung (SAR, USD, MYR, EUR, SGD, JPY, dll.).')]
#[Uri('currencies://rates')]
#[MimeType('application/json')]
class CurrencyRatesResource extends Resource
{
    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $baseCurrency = (string) config('currencies.base', 'IDR');
        $supported = config('currencies.supported', []);
        $rates = CurrencyRate::getCached();

        // Pastikan base currency bernilai 1.0
        if (! isset($rates[$baseCurrency])) {
            $rates[$baseCurrency] = 1.0;
        }

        $formattedSupported = [];
        foreach ($supported as $code => $meta) {
            $formattedSupported[$code] = [
                'name' => $meta['name'] ?? $code,
                'symbol' => $meta['symbol'] ?? $code,
                'decimals' => $meta['decimals'] ?? 2,
                'rate_against_base' => isset($rates[$code]) ? (float) $rates[$code] : null,
            ];
        }

        $lastUpdated = CurrencyRate::query()->latest('fetched_at')->value('fetched_at');

        return Response::json([
            'resource' => 'currencies://rates',
            'base_currency' => $baseCurrency,
            'last_fetched_at' => $lastUpdated?->toIso8601String(),
            'supported_currencies' => $formattedSupported,
            'rates' => $rates,
        ]);
    }
}
