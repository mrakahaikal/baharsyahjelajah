<?php

namespace App\Contracts;

use App\Support\ExchangeRateSnapshot;

interface ExchangeRateProvider
{
    /**
     * @param  array<int, string>  $currencies
     */
    public function latest(string $baseCurrency, array $currencies): ExchangeRateSnapshot;
}
