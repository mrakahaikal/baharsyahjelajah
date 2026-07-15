<?php

namespace App\Helpers;

use App\Services\CurrencyService;
use App\Settings\GeneralSettings;
use Throwable;

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
        $currency = session('app_currency');

        if (is_string($currency) && array_key_exists($currency, config('currencies.supported', []))) {
            return $currency;
        }

        try {
            $defaultCurrency = app(GeneralSettings::class)->default_currency;
        } catch (Throwable) {
            $defaultCurrency = (string) config('currencies.base');
        }

        return array_key_exists($defaultCurrency, config('currencies.supported', []))
            ? $defaultCurrency
            : (string) config('currencies.base');
    }

    /**
     * Convert and format price from IDR.
     */
    public static function formatPrice(int $amountIdr): string
    {
        return app(CurrencyService::class)->convert($amountIdr, static::currency());
    }
}
