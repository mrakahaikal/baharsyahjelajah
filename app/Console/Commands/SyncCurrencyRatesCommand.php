<?php

namespace App\Console\Commands;

use App\Actions\SyncCurrencyRates;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('currency-rates:sync')]
#[Description('Synchronize configured currency rates from the exchange-rate provider')]
class SyncCurrencyRatesCommand extends Command
{
    public function handle(SyncCurrencyRates $syncCurrencyRates): int
    {
        try {
            $snapshot = $syncCurrencyRates->handle();
        } catch (\Throwable $exception) {
            report($exception);
            $this->error('Sinkronisasi kurs gagal: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            '%d kurs berhasil disinkronkan dari %s (data %s).',
            count($snapshot->rates),
            $snapshot->provider,
            $snapshot->sourceUpdatedAt->toDateTimeString(),
        ));

        return self::SUCCESS;
    }
}
