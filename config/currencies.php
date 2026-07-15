<?php

return [
    'base' => 'IDR',

    'supported' => [
        'IDR' => ['name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'decimals' => 0],
        'MYR' => ['name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'decimals' => 2],
        'SGD' => ['name' => 'Singapore Dollar', 'symbol' => 'S$', 'decimals' => 2],
        'USD' => ['name' => 'US Dollar', 'symbol' => '$', 'decimals' => 2],
        'EUR' => ['name' => 'Euro', 'symbol' => '€', 'decimals' => 2],
        'EGP' => ['name' => 'Egyptian Pound', 'symbol' => 'E£', 'decimals' => 2],
        'SAR' => ['name' => 'Saudi Riyal', 'symbol' => 'SAR', 'decimals' => 2],
        'GBP' => ['name' => 'British Pound', 'symbol' => '£', 'decimals' => 2],
        'AED' => ['name' => 'UAE Dirham', 'symbol' => 'AED', 'decimals' => 2],
        'JPY' => ['name' => 'Japanese Yen', 'symbol' => '¥', 'decimals' => 0],
        'THB' => ['name' => 'Thai Baht', 'symbol' => '฿', 'decimals' => 2],
    ],

    'cache_ttl' => (int) env('CURRENCY_CACHE_TTL', 3600),
    'stale_after_hours' => (int) env('CURRENCY_STALE_AFTER_HOURS', 48),

    'provider' => [
        'name' => 'exchange-rate-api',
        'base_url' => env('EXCHANGE_RATE_API_URL', 'https://v6.exchangerate-api.com/v6'),
        'key' => env('EXCHANGE_RATE_API_KEY'),
        'connect_timeout' => (int) env('EXCHANGE_RATE_API_CONNECT_TIMEOUT', 5),
        'timeout' => (int) env('EXCHANGE_RATE_API_TIMEOUT', 10),
    ],
];
