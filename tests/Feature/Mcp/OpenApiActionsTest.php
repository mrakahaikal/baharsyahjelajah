<?php

declare(strict_types=1);

use App\Models\PackageTier;
use App\Models\PostCategory;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\TourPackage;
use App\Models\TourPriceTier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;

uses(RefreshDatabase::class);

beforeEach(function () {
    $clientRepository = app(ClientRepository::class);
    $clientRepository->createPersonalAccessGrantClient('Test Personal Client', 'users');
});

test('openapi specification endpoint returns valid openapi 3.1 schema', function () {
    $response = $this->getJson('/openapi.json');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/json; charset=utf-8');

    $data = $response->json();
    expect($data['openapi'])->toBe('3.1.0')
        ->and($data['info']['title'])->toContain('Baharsyah Jelajah')
        ->and($data['servers'])->not->toBeEmpty()
        ->and($data['components']['securitySchemes']['OAuth2'])->not->toBeEmpty()
        ->and($data['paths'])->toBeArray();

    // Default limit should be 30 actions for OpenAI Custom GPT compatibility
    expect(count($data['paths']))->toBeLessThanOrEqual(30)
        ->and(isset($data['paths']['/api/actions/list_articles']))->toBeTrue();
});

test('get request to mcp admin and mcp endpoints returns openapi specification', function () {
    $adminResponse = $this->getJson('/mcp/admin');
    $adminResponse->assertOk();
    expect($adminResponse->json('openapi'))->toBe('3.1.0');

    $mcpResponse = $this->getJson('/mcp');
    $mcpResponse->assertOk();
    expect($mcpResponse->json('openapi'))->toBe('3.1.0');

    $mcpOpenApiResponse = $this->getJson('/mcp/openapi.json');
    $mcpOpenApiResponse->assertOk();
    expect($mcpOpenApiResponse->json('openapi'))->toBe('3.1.0');
});

test('openapi endpoint supports domain filtering', function () {
    $tourResponse = $this->getJson('/openapi.json?domain=tour');
    $tourResponse->assertOk();
    $paths = array_keys($tourResponse->json('paths'));

    expect($paths)->not->toBeEmpty();
    foreach ($paths as $path) {
        expect(str_contains($path, 'tour') || str_contains($path, 'package'))->toBeTrue();
    }

    $umrahResponse = $this->getJson('/openapi.json?domain=umrah');
    $umrahResponse->assertOk();
    $umrahPaths = array_keys($umrahResponse->json('paths'));

    expect($umrahPaths)->not->toBeEmpty();
    foreach ($umrahPaths as $path) {
        expect(str_contains($path, 'umrah'))->toBeTrue();
    }
});

test('openapi endpoint supports limit all parameter to return all registered tools', function () {
    $response = $this->getJson('/openapi.json?limit=all');
    $response->assertOk();

    $paths = $response->json('paths');
    // We have over 70 tools registered across domains
    expect(count($paths))->toBeGreaterThan(60);
});

test('unauthenticated call to action dispatch endpoint returns 401', function () {
    $response = $this->postJson('/api/actions/list_articles', [
        'per_page' => 5,
    ]);

    $response->assertStatus(401);
    expect($response->json('error'))->toContain('Unauthorized');
});

test('call to action dispatch endpoint with invalid token returns 401', function () {
    $response = $this->withToken('completely-invalid-oauth-token')
        ->postJson('/api/actions/list_articles', [
            'per_page' => 5,
        ]);

    $response->assertStatus(401);
    expect($response->json('error'))->toContain('Unauthorized');
});

test('call to non-existent action tool returns 404', function () {
    $user = User::factory()->create();
    $token = $user->createToken('Test Agent', ['mcp:use'])->accessToken;

    $response = $this->withToken($token)
        ->postJson('/api/actions/unknown_super_action', []);

    $response->assertStatus(404);
    expect($response->json('error'))->toContain('not found');
});

test('call to read action succeeds with read scope token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('Test Agent', ['mcp:read'])->accessToken;

    PostCategory::create([
        'name' => ['id' => 'Wisata Bahari', 'en' => 'Marine Tourism', 'ms' => 'Wisata Bahari'],
        'slug' => ['id' => 'wisata-bahari', 'en' => 'marine-tourism', 'ms' => 'wisata-bahari'],
    ]);

    $response = $this->withToken($token)
        ->postJson('/api/actions/list_article_categories', [
            'per_page' => 5,
        ]);

    $response->assertOk();
    expect($response->json('data'))->toBeArray()
        ->and($response->json('data.0.name'))->toBe('Wisata Bahari');
});

test('calling write action with only mcp:read scope returns 403', function () {
    $user = User::factory()->create();
    $token = $user->createToken('Test Reader', ['mcp:read'])->accessToken;

    $response = $this->withToken($token)
        ->postJson('/api/actions/create_article_category', [
            'name' => 'Kategori Baru',
        ]);

    $response->assertStatus(403);
    expect($response->json('error'))->toContain("lacks required scope 'mcp:write'");
});

test('calling write action with mcp:write scope succeeds and creates data', function () {
    $user = User::factory()->create();
    $token = $user->createToken('Test Writer', ['mcp:write'])->accessToken;

    $response = $this->withToken($token)
        ->postJson('/api/actions/create_article_category', [
            'name' => 'Wisata Sejarah',
        ]);

    $response->assertOk();
    expect($response->json('message'))->toContain('Category created successfully')
        ->and($response->json('category.name'))->toBe('Wisata Sejarah');

    expect(PostCategory::whereJsonContains('slug->id', 'wisata-sejarah')->exists())->toBeTrue();
});

test('calling calculate tour price action via dispatch endpoint succeeds', function () {
    $user = User::factory()->create();
    $token = $user->createToken('ChatGPT Agent', ['mcp:use'])->accessToken;

    $category = TourCategory::create([
        'name' => ['id' => 'Petualangan', 'en' => 'Adventure', 'ms' => 'Petualangan'],
        'slug' => ['id' => 'petualangan', 'en' => 'adventure', 'ms' => 'petualangan'],
        'is_active' => true,
    ]);

    $tour = Tour::create([
        'name' => ['id' => 'Paket Komodo', 'en' => 'Komodo Tour', 'ms' => 'Paket Komodo'],
        'slug' => ['id' => 'paket-komodo', 'en' => 'komodo-tour', 'ms' => 'paket-komodo'],
        'tour_category_id' => $category->id,
        'tour_type' => 'domestic',
        'currency' => 'IDR',
        'is_active' => true,
    ]);

    $package = TourPackage::create([
        'tour_id' => $tour->id,
        'name' => ['id' => 'VIP Speedboat', 'en' => 'VIP Speedboat', 'ms' => 'VIP Speedboat'],
        'slug' => ['id' => 'vip-speedboat', 'en' => 'vip-speedboat', 'ms' => 'vip-speedboat'],
        'duration_days' => 3,
        'duration_nights' => 2,
    ]);

    $tier = PackageTier::create([
        'tour_package_id' => $package->id,
        'name' => ['id' => 'Deluxe Tier', 'en' => 'Deluxe Tier', 'ms' => 'Deluxe Tier'],
        'hotel_stars' => 4,
    ]);

    TourPriceTier::create([
        'package_tier_id' => $tier->id,
        'min_pax' => 2,
        'max_pax' => 4,
        'price' => 2500000,
        'currency' => 'IDR',
    ]);

    $response = $this->withToken($token)
        ->postJson('/api/actions/calculate_tour_price', [
            'tour_package_id' => $package->id,
            'pax' => 3,
        ]);

    $response->assertOk();
    expect($response->json('tour_package.package_name'))->toBe('VIP Speedboat')
        ->and($response->json('estimates.0.total_price'))->toBe(7500000)
        ->and($response->json('estimates.0.price_per_pax'))->toBe(2500000);
});
