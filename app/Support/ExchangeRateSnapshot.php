<?php

namespace App\Support;

use Carbon\CarbonImmutable;

final readonly class ExchangeRateSnapshot
{
    /**
     * @param  array<string, float>  $rates
     */
    public function __construct(
        public string $provider,
        public string $baseCurrency,
        public array $rates,
        public CarbonImmutable $sourceUpdatedAt,
        public CarbonImmutable $fetchedAt,
    ) {}
}
