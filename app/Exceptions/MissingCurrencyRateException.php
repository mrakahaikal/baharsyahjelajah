<?php

namespace App\Exceptions;

use RuntimeException;

class MissingCurrencyRateException extends RuntimeException
{
    public static function between(string $fromCurrency, string $toCurrency): self
    {
        return new self("Kurs {$fromCurrency} ke {$toCurrency} tidak tersedia.");
    }
}
