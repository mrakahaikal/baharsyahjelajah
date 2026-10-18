<?php

declare(strict_types=1);

namespace App\Filament\Resources\OAuthClients\Pages;

use App\Filament\Resources\OAuthClients\OAuthClientResource;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

class ManageOAuthClients extends ManageRecords
{
    protected static string $resource = OAuthClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Klien AI')
                ->icon('lucide-plus')
                ->using(function (array $data, ClientRepository $clients): Client {
                    $redirects = array_filter(array_map('trim', explode(',', (string) ($data['redirect'] ?? ''))));

                    $client = $clients->createAuthorizationCodeGrantClient(
                        name: $data['name'],
                        redirectUris: $redirects,
                        confidential: (bool) ($data['confidential'] ?? true),
                        enableDeviceFlow: false,
                    );

                    if ($client->plainSecret) {
                        Notification::make()
                            ->title('Klien AI Berhasil Dibuat!')
                            ->body("Client Secret: **{$client->plainSecret}**\n\n_Salin dan simpan secret ini sekarang. Secret tidak akan ditampilkan lagi demi keamanan._")
                            ->persistent()
                            ->icon('lucide-key')
                            ->color('warning')
                            ->send();
                    }

                    return $client;
                }),
        ];
    }
}
