<?php

namespace App\Filament\Resources\UmrahPackages\Pages;

use App\Filament\Resources\UmrahPackages\UmrahPackageResource;
use App\Services\UmrahPackageImporter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListUmrahPackages extends ListRecords
{
    protected static string $resource = UmrahPackageResource::class;

    protected ?string $heading = 'Daftar Paket Umrah';

    protected ?string $subheading = 'Kelola semua penawaran paket perjalanan umrah.';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importCsv')
                ->label('Import CSV')
                ->icon('lucide-upload')
                ->color('gray')
                ->form([
                    FileUpload::make('csv_file')
                        ->label('File CSV')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'text/x-csv', 'application/vnd.ms-excel'])
                        ->required()
                        ->disk('local')
                        ->directory('temp-imports'),
                ])
                ->action(function (array $data, UmrahPackageImporter $importer) {
                    $path = storage_path('app/'.$data['csv_file']);

                    $result = $importer->import($path);

                    // Hapus file temp setelah di-import
                    if (file_exists($path)) {
                        unlink($path);
                    }

                    $notification = Notification::make();

                    if (empty($result['errors'])) {
                        $notification->title('Import Sukses')
                            ->body("Berhasil mengimpor {$result['success']} paket umrah.")
                            ->success();
                    } else {
                        $errorText = implode("\n", array_slice($result['errors'], 0, 5));
                        if (count($result['errors']) > 5) {
                            $errorText .= "\n...dan ".(count($result['errors']) - 5).' kesalahan lainnya.';
                        }

                        $notification->title('Import Selesai dengan Hambatan')
                            ->body("Berhasil: {$result['success']} | Gagal: {$result['failed']}\n\nDetail Kesalahan:\n{$errorText}")
                            ->warning();
                    }

                    $notification->persistent()->send();
                }),
            CreateAction::make()
                ->label('Tambah Paket Umrah')
                ->icon('lucide-plus'),
        ];
    }
}
