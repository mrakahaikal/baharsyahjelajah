<?php

namespace App\Helpers;

use App\Services\CurrencyService;

class LocaleHelper
{
    /**
     * Get the current application locale.
     */
    public static function current(): string
    {
        return app()->getLocale();
    }

    /**
     * Get the active currency from session.
     */
    public static function currency(): string
    {
        return session('app_currency', 'IDR');
    }

    /**
     * Convert and format price from IDR.
     */
    public static function formatPrice(int $amountIdr): string
    {
        return app(CurrencyService::class)->convert($amountIdr, static::currency());
    }
}