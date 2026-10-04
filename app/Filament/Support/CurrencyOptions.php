<?php

namespace App\Filament\Support;

class CurrencyOptions
{
    /**
     * Get associative array of supported currencies formatted as "CODE (Symbol)".
     *
     * @return array<string, string>
     */
    public static function selectOptions(): array
    {
        return collect(config('currencies.supported', []))
            ->mapWithKeys(fn (array $metadata, string $code): array => [
                $code => "{$code} ({$metadata['symbol']})",
            ])
            ->all();
    }

    /**
     * Get associative array of supported currencies formatted as "CODE (Symbol) - Name".
     *
     * @return array<string, string>
     */
    public static function detailedOptions(): array
    {
        return collect(config('currencies.supported', []))
            ->mapWithKeys(fn (array $metadata, string $code): array => [
                $code => "{$code} ({$metadata['symbol']}) - {$metadata['name']}",
            ])
            ->all();
    }
}

