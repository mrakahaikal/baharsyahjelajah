<?php

declare(strict_types=1);

use App\Filament\Resources\OAuthClients\Pages\ManageOAuthClients;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('manage oauth clients page can be rendered', function () {
    $user = User::factory()->create(['email' => 'admin@baharsyahjelajah.com']);

    Livewire::actingAs($user)
        ->test(ManageOAuthClients::class)
        ->assertOk();
});

test('oauth client can be created via filament manage page', function () {
    $user = User::factory()->create(['email' => 'admin@baharsyahjelajah.com']);

    Livewire::actingAs($user)
        ->test(ManageOAuthClients::class)
        ->callAction('create', [
            'name' => 'ChatGPT Content Agent',
            'redirect' => 'https://chatgpt.com/aip/g-98765/oauth/callback',
            'confidential' => true,
        ])
        ->assertHasNoActionErrors();

    $client = Client::where('name', 'ChatGPT Content Agent')->first();
    expect($client)->not->toBeNull()
        ->and($client->redirect_uris)->toContain('https://chatgpt.com/aip/g-98765/oauth/callback')
        ->and($client->confidential())->toBeTrue()
        ->and($client->revoked)->toBeFalse();
});

test('oauth client status can be toggled to revoked and reactivated', function () {
    $user = User::factory()->create(['email' => 'admin@baharsyahjelajah.com']);
    $clientRepository = app(ClientRepository::class);
    $client = $clientRepository->createAuthorizationCodeGrantClient(
        name: 'Temporary Bot',
        redirectUris: ['https://bot.example.com/callback'],
        confidential: true
    );

    expect($client->revoked)->toBeFalse();

    // Toggle revoked -> true
    Livewire::actingAs($user)
        ->test(ManageOAuthClients::class)
        ->callTableAction('toggleRevoked', $client);

    expect($client->fresh()->revoked)->toBeTrue();

    // Toggle revoked -> false (reactivate)
    Livewire::actingAs($user)
        ->test(ManageOAuthClients::class)
        ->callTableAction('toggleRevoked', $client);

    expect($client->fresh()->revoked)->toBeFalse();
});

test('oauth client can be updated via edit table action', function () {
    $user = User::factory()->create(['email' => 'admin@baharsyahjelajah.com']);
    $clientRepository = app(ClientRepository::class);
    $client = $clientRepository->createAuthorizationCodeGrantClient(
        name: 'Old Client Name',
        redirectUris: ['https://old.example.com/callback'],
        confidential: true
    );

    Livewire::actingAs($user)
        ->test(ManageOAuthClients::class)
        ->callTableAction('edit', $client, [
            'name' => 'Updated Client Name',
            'redirect' => 'https://new.example.com/callback',
        ])
        ->assertHasNoTableActionErrors();

    expect($client->fresh()->name)->toBe('Updated Client Name')
        ->and($client->fresh()->redirect_uris)->toContain('https://new.example.com/callback');
});

test('oauth client can be deleted via delete table action', function () {
    $user = User::factory()->create(['email' => 'admin@baharsyahjelajah.com']);
    $clientRepository = app(ClientRepository::class);
    $client = $clientRepository->createAuthorizationCodeGrantClient(
        name: 'To Be Deleted',
        redirectUris: ['https://delete.example.com/callback'],
        confidential: true
    );

    Livewire::actingAs($user)
        ->test(ManageOAuthClients::class)
        ->callTableAction('delete', $client)
        ->assertHasNoTableActionErrors();

    expect(Client::find($client->id))->toBeNull();
});
