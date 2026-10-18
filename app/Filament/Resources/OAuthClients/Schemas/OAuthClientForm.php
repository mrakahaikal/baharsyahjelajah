<?php

declare(strict_types=1);

namespace App\Filament\Resources\OAuthClients\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Laravel\Passport\Client;

class OAuthClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Klien AI')
                    ->placeholder('misal: ChatGPT Content Assistant')
                    ->required()
                    ->maxLength(255),

                TextInput::make('redirect')
                    ->label('Redirect URI / Callback URL')
                    ->placeholder('https://chatgpt.com/aip/g-xxxx/oauth/callback')
                    ->helperText('URL callback yang diberikan oleh ChatGPT Builder atau aplikasi OAuth.')
                    ->required()
                    ->formatStateUsing(fn ($state, ?Client $record) => $record ? implode(', ', $record->redirect_uris ?? []) : $state)
                    ->maxLength(1000),

                Toggle::make('confidential')
                    ->label('Klien Rahasia (Confidential)')
                    ->helperText('Aktifkan untuk Chat AI yang dapat menyimpan Client Secret dengan aman.')
                    ->default(true),
            ]);
    }
}
