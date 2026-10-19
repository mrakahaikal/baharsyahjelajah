<?php

use App\Enums\TourType;
use App\Mcp\Resources\TourCategoriesResource;
use App\Mcp\Servers\BaharsyahServer;
use App\Mcp\Tools\Tours\Categories\CreateTourCategoryTool;
use App\Mcp\Tools\Tours\Categories\DeleteTourCategoryTool;
use App\Mcp\Tools\Tours\Categories\GetTourCategoryTool;
use App\Mcp\Tools\Tours\Categories\ListTourCategoriesTool;
use App\Mcp\Tools\Tours\Categories\UpdateTourCategoryTool;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Mcp\Server\Transport\FakeTransporter;
use Laravel\Passport\ClientRepository;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-static-agent-token-tour-categories');

    $clientRepository = app(ClientRepository::class);
    $clientRepository->createPersonalAccessGrantClient('Test Personal Client', 'users');

    $this->categoryDomestic = TourCategory::create([
        'name' => ['id' => 'Wisata Bahari', 'en' => 'Marine Tourism', 'ms' => 'Pelancongan Marin'],
        'slug' => ['id' => 'wisata-bahari', 'en' => 'marine-tourism', 'ms' => 'pelancongan-marin'],
        'icon' => 'lucide-anchor',
        'sort_order' => 1,
    ]);

    $this->categoryIntl = TourCategory::create([
        'name' => ['id' => 'Wisata Internasional', 'en' => 'International Tour', 'ms' => 'Pelancongan Antarabangsa'],
        'slug' => ['id' => 'wisata-internasional', 'en' => 'international-tour', 'ms' => 'pelancongan-antarabangsa'],
        'icon' => 'lucide-globe',
        'sort_order' => 2,
    ]);
});

test('tour category tools and resource are registered in baharsyah server', function () {
    $server = app(BaharsyahServer::class, ['transport' => new FakeTransporter]);
    $reflection = new ReflectionClass($server);

    $tools = $reflection->getProperty('tools')->getValue($server);
    expect($tools)->toContain(
        ListTourCategoriesTool::class,
        GetTourCategoryTool::class,
        CreateTourCategoryTool::class,
        UpdateTourCategoryTool::class,
        DeleteTourCategoryTool::class,
    );

    $resources = $reflection->getProperty('resources')->getValue($server);
    expect($resources)->toContain(TourCategoriesResource::class);
});

test('list tour categories returns ordered categories with tour counts and search filter', function () {
    Tour::create([
        'tour_category_id' => $this->categoryDomestic->id,
        'name' => ['id' => 'Eksplorasi Derawan'],
        'slug' => ['id' => 'eksplorasi-derawan'],
        'short_description' => ['id' => 'Wisata laut Derawan'],
        'description' => ['id' => '<p>Detail wisata Derawan</p>'],
        'tour_type' => TourType::Domestic,
        'currency' => 'IDR',
        'is_active' => true,
        'is_featured' => true,
    ]);

    BaharsyahServer::tool(ListTourCategoriesTool::class, [])
        ->assertSee('Wisata Bahari')
        ->assertSee('Wisata Internasional');

    BaharsyahServer::tool(ListTourCategoriesTool::class, ['search' => 'Marine'])
        ->assertSee('Wisata Bahari')
        ->assertDontSee('Wisata Internasional');
});

test('get tour category retrieves details by id or slug with associated tours', function () {
    Tour::create([
        'tour_category_id' => $this->categoryDomestic->id,
        'name' => ['id' => 'Pesona Maratua'],
        'slug' => ['id' => 'pesona-maratua'],
        'short_description' => ['id' => 'Pulau Maratua'],
        'description' => ['id' => '<p>Keindahan Maratua</p>'],
        'tour_type' => TourType::Domestic,
        'currency' => 'IDR',
        'is_active' => true,
    ]);

    BaharsyahServer::tool(GetTourCategoryTool::class, ['slug' => 'wisata-bahari'])
        ->assertSee('Wisata Bahari')
        ->assertSee('Pesona Maratua');

    BaharsyahServer::tool(GetTourCategoryTool::class, ['id' => 9999])
        ->assertSee('Tour category with ID 9999 not found.');
});

test('create and update tour category manage translations and auto slugs', function () {
    $createResponse = BaharsyahServer::tool(CreateTourCategoryTool::class, [
        'name' => [
            'id' => 'Wisata Budaya & Sejarah',
            'en' => 'Culture & Heritage Tour',
        ],
        'icon' => 'lucide-landmark',
        'sort_order' => 5,
    ]);

    $createResponse->assertSee('Kategori tur berhasil dibuat.')
        ->assertSee('wisata-budaya-sejarah')
        ->assertSee('culture-heritage-tour');

    $created = TourCategory::where('icon', 'lucide-landmark')->firstOrFail();

    BaharsyahServer::tool(UpdateTourCategoryTool::class, [
        'id' => $created->id,
        'name' => ['id' => 'Wisata Sejarah Nusantara'],
        'sort_order' => 10,
    ])->assertSee('Kategori tur berhasil diperbarui.')
        ->assertSee('Wisata Sejarah Nusantara');

    expect($created->fresh()->sort_order)->toBe(10);
});

test('delete tour category prevents deletion when category has associated tours', function () {
    $tour = Tour::create([
        'tour_category_id' => $this->categoryDomestic->id,
        'name' => ['id' => 'Tur Derawan 3H2M'],
        'slug' => ['id' => 'tur-derawan-3h2m'],
        'short_description' => ['id' => 'Derawan'],
        'description' => ['id' => '<p>Derawan</p>'],
        'tour_type' => TourType::Domestic,
        'currency' => 'IDR',
        'is_active' => true,
    ]);

    BaharsyahServer::tool(DeleteTourCategoryTool::class, [
        'id' => $this->categoryDomestic->id,
    ])->assertSee('Cannot delete tour category');

    $this->assertDatabaseHas('tour_categories', ['id' => $this->categoryDomestic->id]);

    // Delete empty category succeeds
    BaharsyahServer::tool(DeleteTourCategoryTool::class, [
        'id' => $this->categoryIntl->id,
    ])->assertSee('Kategori tur berhasil dihapus.');

    $this->assertDatabaseMissing('tour_categories', ['id' => $this->categoryIntl->id]);
});

test('tour category resource returns structured category overview', function () {
    BaharsyahServer::resource(TourCategoriesResource::class)
        ->assertSee('tours://categories')
        ->assertSee('Wisata Bahari')
        ->assertSee('Wisata Internasional');
});

test('tour category tools enforce granular oauth scopes', function () {
    $user = User::factory()->create();
    $readToken = $user->createToken('Read Token', ['mcp:read'])->accessToken;
    $writeToken = $user->createToken('Write Token', ['mcp:write'])->accessToken;
    $adminToken = $user->createToken('Admin Token', ['mcp:admin'])->accessToken;

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => ['name' => 'list_tour_categories', 'arguments' => []],
    ], ['Authorization' => "Bearer {$readToken}"])->assertOk();

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/call',
        'params' => ['name' => 'create_tour_category', 'arguments' => ['name' => 'Kategori Baru']],
    ], ['Authorization' => "Bearer {$readToken}"])->assertStatus(403);

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'tools/call',
        'params' => ['name' => 'create_tour_category', 'arguments' => ['name' => 'Kategori Baru']],
    ], ['Authorization' => "Bearer {$writeToken}"])->assertOk();

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 4,
        'method' => 'tools/call',
        'params' => ['name' => 'delete_tour_category', 'arguments' => ['id' => $this->categoryIntl->id]],
    ], ['Authorization' => "Bearer {$writeToken}"])->assertStatus(403);

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 5,
        'method' => 'tools/call',
        'params' => ['name' => 'delete_tour_category', 'arguments' => ['id' => $this->categoryIntl->id]],
    ], ['Authorization' => "Bearer {$adminToken}"])->assertOk();
});
