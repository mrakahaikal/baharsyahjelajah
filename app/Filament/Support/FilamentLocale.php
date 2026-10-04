<?php

namespace App\Filament\Support;

class FilamentLocale
{
    /**
     * @var array<int, string>
     */
    public const DEFAULT_LOCALES = ['id', 'en', 'ms'];

    public const PRIMARY_LOCALE = 'id';

    /**
     * @return array<int, string>
     */
    public static function supported(): array
    {
        return self::DEFAULT_LOCALES;
    }

    /**
     * @return array<int, string>
     */
    public static function locales(): array
    {
        return self::supported();
    }

    public static function primary(): string
    {
        return self::PRIMARY_LOCALE;
    }
}
