<?php

namespace App\Filament\Resources\CurrencyRates\Pages;

use App\Actions\SyncCurrencyRates;
use App\Filament\Resources\CurrencyRates\CurrencyRateResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Throwable;

class ManageCurrencyRates extends ManageRecords
{
    protected static string $resource = CurrencyRateResource::class;

    protected ?string $heading = 'Daftar Kurs Mata Uang';

    protected ?string $subheading = 'Kelola rasio nilai tukar mata uang untuk perhitungan harga otomatis.';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncCurrencyRates')
                ->label('Sinkronkan Kurs')
                ->icon('lucide-refresh-cw')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Sinkronkan seluruh kurs?')
                ->modalDescription('Kurs terakhir tetap dipertahankan apabila provider gagal atau mengirim data yang tidak lengkap.')
                ->modalSubmitActionLabel('Sinkronkan')
                ->action(function (SyncCurrencyRates $syncCurrencyRates): void {
                    try {
                        $snapshot = $syncCurrencyRates->handle();
                    } catch (Throwable $exception) {
                        report($exception);

                        Notification::make()
                            ->danger()
                            ->title('Sinkronisasi kurs gagal')
                            ->body($exception->getMessage())
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->success()
                        ->title('Kurs berhasil disinkronkan')
                        ->body(sprintf(
                            '%d kurs diperbarui dari data %s.',
                            count($snapshot->rates),
                            $snapshot->sourceUpdatedAt->timezone('Asia/Jakarta')->format('d M Y H:i'),
                        ))
                        ->send();
                }),
        ];
    }
}
