<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it generates sanctum token for first user when no email argument is provided', function () {
    $user = User::factory()->create(['email' => 'agent-user@example.com']);

    $this->artisan('mcp:token')
        ->expectsOutputToContain('Sanctum API Token successfully generated for User [agent-user@example.com]')
        ->assertSuccessful();

    expect($user->tokens)->toHaveCount(1);
});

test('it generates sanctum token for specified email', function () {
    $user1 = User::factory()->create(['email' => 'user1@example.com']);
    $user2 = User::factory()->create(['email' => 'user2@example.com']);

    $this->artisan('mcp:token user2@example.com --name=custom-agent')
        ->expectsOutputToContain('Sanctum API Token successfully generated for User [user2@example.com]')
        ->assertSuccessful();

    expect($user2->fresh()->tokens)->toHaveCount(1);
    expect($user2->tokens->first()->name)->toBe('custom-agent');
});

test('it fails when specified user email does not exist', function () {
    $this->artisan('mcp:token nonexisting@example.com')
        ->expectsOutputToContain('User with email [nonexisting@example.com] not found.')
        ->assertFailed();
});
