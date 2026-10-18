<?php

declare(strict_types=1);

use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\ClientRepository;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-static-agent-token-999');

    $clientRepository = app(ClientRepository::class);
    $clientRepository->createPersonalAccessGrantClient('Test Personal Client', 'users');
});

test('unauthenticated request without token to mcp endpoint returns 401', function () {
    $response = $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'method' => 'tools/list',
        'id' => 1,
    ]);

    $response->assertStatus(401);
    expect($response->json('error.code'))->toBe(-32000);
});

test('request with static mcp bearer token succeeds via fallback authentication', function () {
    $response = $this->withToken('test-static-agent-token-999')
        ->postJson('/mcp/admin', [
            'jsonrpc' => '2.0',
            'method' => 'tools/list',
            'id' => 1,
        ]);

    $response->assertOk();
    expect($response->json('jsonrpc'))->toBe('2.0');
});

test('request with invalid token returns 401 unauthorized', function () {
    $response = $this->withToken('completely-invalid-bearer-token')
        ->postJson('/mcp/admin', [
            'jsonrpc' => '2.0',
            'method' => 'tools/list',
            'id' => 1,
        ]);

    $response->assertStatus(401);
    expect($response->json('error.message'))->toContain('Invalid or missing MCP bearer token');
});

test('oauth token with mcp:read scope can list tools and call read tools', function () {
    $user = User::factory()->create(['email' => 'reader@baharsyahjelajah.com']);
    $token = $user->createToken('ChatGPT Reader', ['mcp:read'])->accessToken;

    $response = $this->withToken($token)
        ->postJson('/mcp/admin', [
            'jsonrpc' => '2.0',
            'method' => 'tools/list',
            'id' => 2,
        ]);

    $response->assertOk();

    // Call read tool 'list_article_categories'
    $readResponse = $this->withToken($token)
        ->postJson('/mcp/admin', [
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_article_categories',
                'arguments' => ['per_page' => 5],
            ],
            'id' => 3,
        ]);

    $readResponse->assertOk();
});

test('oauth token with only mcp:read scope cannot call create_article_category tool', function () {
    $user = User::factory()->create(['email' => 'reader@baharsyahjelajah.com']);
    $token = $user->createToken('ChatGPT ReadOnly', ['mcp:read'])->accessToken;

    $response = $this->withToken($token)
        ->postJson('/mcp/admin', [
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => 'create_article_category',
                'arguments' => [
                    'name' => 'Kategori Baru',
                ],
            ],
            'id' => 4,
        ]);

    $response->assertStatus(403);
    expect($response->json('error.code'))->toBe(-32003)
        ->and($response->json('error.message'))->toContain("lacks required scope 'mcp:write'");
});

test('oauth token with mcp:write scope can call create_article_category tool', function () {
    $user = User::factory()->create(['email' => 'writer@baharsyahjelajah.com']);
    $token = $user->createToken('ChatGPT Writer', ['mcp:write'])->accessToken;

    $response = $this->withToken($token)
        ->postJson('/mcp/admin', [
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => 'create_article_category',
                'arguments' => [
                    'name' => 'Kategori Wisata Bahari',
                ],
            ],
            'id' => 5,
        ]);

    $response->assertOk();
    expect(PostCategory::whereJsonContains('slug->id', 'kategori-wisata-bahari')->exists())->toBeTrue();
});

test('oauth token with only mcp:write scope cannot call delete_article_category tool', function () {
    $user = User::factory()->create(['email' => 'writer@baharsyahjelajah.com']);
    $token = $user->createToken('ChatGPT Writer', ['mcp:write'])->accessToken;

    $cat = PostCategory::create([
        'name' => ['id' => 'Kategori Uji Hapus', 'en' => 'Test Delete Category', 'ms' => 'Kategori Uji Hapus'],
        'slug' => ['id' => 'kategori-uji-hapus', 'en' => 'test-delete-category', 'ms' => 'kategori-uji-hapus'],
    ]);

    $response = $this->withToken($token)
        ->postJson('/mcp/admin', [
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => 'delete_article_category',
                'arguments' => ['id' => $cat->id],
            ],
            'id' => 6,
        ]);

    $response->assertStatus(403);
    expect($response->json('error.code'))->toBe(-32003)
        ->and($response->json('error.message'))->toContain("lacks required scope 'mcp:admin'");
});

test('oauth token with mcp:admin scope can call delete_article_category tool', function () {
    $user = User::factory()->create(['email' => 'admin@baharsyahjelajah.com']);
    $token = $user->createToken('ChatGPT Admin', ['mcp:admin'])->accessToken;

    $cat = PostCategory::create([
        'name' => ['id' => 'Kategori Siap Hapus', 'en' => 'Ready Delete Category', 'ms' => 'Kategori Siap Hapus'],
        'slug' => ['id' => 'kategori-siap-hapus', 'en' => 'ready-delete-category', 'ms' => 'kategori-siap-hapus'],
    ]);

    $response = $this->withToken($token)
        ->postJson('/mcp/admin', [
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => 'delete_article_category',
                'arguments' => ['id' => $cat->id],
            ],
            'id' => 7,
        ]);

    $response->assertOk();
    expect(PostCategory::find($cat->id))->toBeNull();
});

test('dynamic client registration creates new client and deduplicates identical clients', function () {
    // 1. Initial registration
    $response = $this->postJson('/oauth/register', [
        'client_name' => 'Claude Desktop App',
        'redirect_uris' => ['http://localhost:3000/callback'],
    ]);

    $response->assertStatus(201);
    $clientId = $response->json('client_id');
    expect($clientId)->not->toBeEmpty();

    // 2. Second registration with same name and redirect URI should reuse existing client
    $duplicateResponse = $this->postJson('/oauth/register', [
        'client_name' => 'Claude Desktop App',
        'redirect_uris' => ['http://localhost:3000/callback'],
    ]);

    $duplicateResponse->assertStatus(200);
    expect($duplicateResponse->json('client_id'))->toBe($clientId);
});
