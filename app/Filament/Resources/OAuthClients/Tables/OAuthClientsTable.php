<?php

declare(strict_types=1);

namespace App\Filament\Resources\OAuthClients\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Laravel\Passport\Client;

class OAuthClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum Ada Klien AI OAuth')
            ->emptyStateDescription('Daftarkan platform Chat AI eksternal (seperti ChatGPT atau Claude) untuk mengizinkan akses ke MCP.')
            ->emptyStateIcon('lucide-key')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Klien AI')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('id')
                    ->label('Client ID')
                    ->fontFamily(FontFamily::Mono)
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->copyMessage('Client ID disalin')
                    ->searchable(),

                TextColumn::make('redirect_uris')
                    ->label('Redirect URI')
                    ->fontFamily(FontFamily::Mono)
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : (string) $state)
                    ->limit(35)
                    ->copyable()
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('revoked')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Dicabut' : 'Aktif')
                    ->color(fn (bool $state): string => $state ? 'gray' : 'success'),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('revoked')
                    ->label('Status Akses')
                    ->placeholder('Semua Klien')
                    ->trueLabel('Hanya Dicabut')
                    ->falseLabel('Hanya Aktif'),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('guide')
                        ->label('Panduan Konfigurasi AI')
                        ->icon('lucide-circle-help')
                        ->modalHeading('Parameter Konfigurasi Chat AI')
                        ->modalDescription('Salin parameter berikut ke halaman pengaturan OAuth di ChatGPT Custom Actions atau Claude:')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Tutup')
                        ->form(fn (Client $record): array => [
                            TextInput::make('client_id')
                                ->label('Client ID')
                                ->default($record->id)
                                ->disabled(),
                            TextInput::make('auth_url')
                                ->label('Authorization URL')
                                ->default(url('/oauth/authorize'))
                                ->disabled(),
                            TextInput::make('token_url')
                                ->label('Token URL')
                                ->default(url('/oauth/token'))
                                ->disabled(),
                            TextInput::make('scopes')
                                ->label('Scopes yang Direkomendasikan')
                                ->default('mcp:read mcp:write')
                                ->disabled(),
                            TextInput::make('registered_callback')
                                ->label('Redirect URI Terdaftar')
                                ->default(implode(', ', $record->redirect_uris ?? []))
                                ->disabled(),
                        ]),

                    EditAction::make()
                        ->mutateFormDataUsing(function (array $data): array {
                            $redirects = array_filter(array_map('trim', explode(',', (string) ($data['redirect'] ?? ''))));

                            return [
                                'name' => $data['name'],
                                'redirect_uris' => $redirects,
                            ];
                        }),

                    Action::make('toggleRevoked')
                        ->label(fn (Client $record): string => $record->revoked ? 'Aktifkan Kembali' : 'Cabut Akses (Revoke)')
                        ->icon(fn (Client $record): string => $record->revoked ? 'lucide-refresh-cw' : 'lucide-ban')
                        ->color(fn (Client $record): string => $record->revoked ? 'success' : 'danger')
                        ->requiresConfirmation()
                        ->modalHeading(fn (Client $record): string => $record->revoked ? 'Aktifkan Akses Klien' : 'Cabut Akses Klien Ini?')
                        ->modalDescription(fn (Client $record): string => $record->revoked
                            ? 'Klien ini akan dapat kembali meminta token otorisasi OAuth.'
                            : 'Mencabut akses akan membatalkan seluruh access token aktif milik klien ini.')
                        ->action(function (Client $record): void {
                            $newStatus = ! $record->revoked;
                            $record->update(['revoked' => $newStatus]);

                            if ($newStatus) {
                                $record->tokens()->update(['revoked' => true]);
                            }

                            Notification::make()
                                ->title($newStatus ? 'Akses Klien Dicabut' : 'Akses Klien Diaktifkan')
                                ->icon($newStatus ? 'lucide-ban' : 'lucide-circle-check')
                                ->color($newStatus ? 'danger' : 'success')
                                ->send();
                        }),

                    DeleteAction::make()
                        ->before(function (Client $record): void {
                            $record->tokens()->delete();
                        }),
                ]),
            ]);
    }
}

