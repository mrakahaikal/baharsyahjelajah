<?php

use App\Mcp\Servers\BaharsyahServer;
use App\Mcp\Tools\Countries\CreateCountryTool;
use App\Mcp\Tools\Countries\DeleteCountryTool;
use App\Mcp\Tools\Countries\GetCountryTool;
use App\Mcp\Tools\Countries\ListCountriesTool;
use App\Mcp\Tools\Countries\UpdateCountryTool;
use App\Models\Country;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use App\Models\VisaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Mcp\Server\Transport\FakeTransporter;
use Laravel\Passport\ClientRepository;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-static-agent-token-country');

    $clientRepository = app(ClientRepository::class);
    $clientRepository->createPersonalAccessGrantClient('Test Personal Client', 'users');

    $this->country = Country::create([
        'name' => ['id' => 'Arab Saudi', 'en' => 'Saudi Arabia', 'ms' => 'Arab Saudi'],
        'slug' => 'arab-saudi',
        'capital_city' => ['id' => 'Riyadh', 'en' => 'Riyadh'],
        'currency_code' => 'SAR',
        'iso_alpha_2' => 'SA',
        'iso_alpha_3' => 'SAU',
        'is_active' => true,
        'is_featured' => true,
    ]);
});

test('country tools are registered in baharsyah server', function () {
    $server = app(BaharsyahServer::class, ['transport' => new FakeTransporter]);
    $reflection = new ReflectionClass($server);
    $tools = $reflection->getProperty('tools')->getValue($server);

    expect($tools)->toContain(
        ListCountriesTool::class,
        GetCountryTool::class,
        CreateCountryTool::class,
        UpdateCountryTool::class,
        DeleteCountryTool::class,
    );
});

test('list countries tool returns countries with pagination, search, and locale formatting', function () {
    $c2 = Country::create([
        'name' => ['id' => 'Turki', 'en' => 'Turkey', 'ms' => 'Turki'],
        'slug' => 'turki',
        'capital_city' => ['id' => 'Ankara', 'en' => 'Ankara'],
        'currency_code' => 'TRY',
        'iso_alpha_2' => 'TR',
        'iso_alpha_3' => 'TUR',
        'is_active' => false,
        'is_featured' => false,
    ]);

    // 1. List active only in Indonesian
    BaharsyahServer::tool(ListCountriesTool::class, ['is_active' => true, 'locale' => 'id'])
        ->assertSee('Arab Saudi')
        ->assertSee('SAR')
        ->assertDontSee('Turki');

    // 2. List in English
    BaharsyahServer::tool(ListCountriesTool::class, ['is_active' => true, 'locale' => 'en'])
        ->assertSee('Saudi Arabia')
        ->assertDontSee('Arab Saudi');

    // 3. Search query
    BaharsyahServer::tool(ListCountriesTool::class, ['search' => 'turki'])
        ->assertSee('Turki')
        ->assertDontSee('Arab Saudi');
});

test('get country tool finds country by id, slug, or ISO alpha-2/alpha-3 case-insensitive', function () {
    // 1. Lookup by ID
    BaharsyahServer::tool(GetCountryTool::class, ['id' => $this->country->id, 'locale' => 'id'])
        ->assertSee('Arab Saudi')
        ->assertSee('SAR')
        ->assertSee('Riyadh');

    // 2. Lookup by slug
    BaharsyahServer::tool(GetCountryTool::class, ['slug' => 'arab-saudi', 'locale' => 'en'])
        ->assertSee('Saudi Arabia')
        ->assertSee('SAU');

    // 3. Lookup by ISO Alpha-2 (lowercase)
    BaharsyahServer::tool(GetCountryTool::class, ['iso_code' => 'sa'])
        ->assertSee('Arab Saudi');

    // 4. Lookup by ISO Alpha-3 (lowercase)
    BaharsyahServer::tool(GetCountryTool::class, ['iso_code' => 'sau'])
        ->assertSee('Arab Saudi');

    // 5. Not found error
    BaharsyahServer::tool(GetCountryTool::class, ['iso_code' => 'XX'])
        ->assertSee('Country with ISO code \'XX\' not found.');
});

test('create country tool creates country with upper-cased ISO codes and multilingual support', function () {
    $response = BaharsyahServer::tool(CreateCountryTool::class, [
        'name' => [
            'id' => 'Jepang',
            'en' => 'Japan',
        ],
        'slug' => 'jepang',
        'iso_alpha_2' => 'jp',
        'iso_alpha_3' => 'jpn',
        'capital_city' => 'Tokyo',
        'currency_code' => 'JPY',
        'is_active' => true,
        'is_featured' => true,
    ]);

    $response->assertSee('Country \'Jepang\' created successfully.');

    $created = Country::where('slug', 'jepang')->first();
    expect($created)->not->toBeNull()
        ->and($created->iso_alpha_2)->toBe('JP')
        ->and($created->iso_alpha_3)->toBe('JPN')
        ->and($created->currency_code)->toBe('JPY')
        ->and($created->getTranslation('name', 'en'))->toBe('Japan');

    // Duplicate check
    BaharsyahServer::tool(CreateCountryTool::class, [
        'name' => 'Jepang Baru',
        'iso_alpha_2' => 'JP',
    ])->assertSee("A country with ISO Alpha-2 code 'JP' already exists.");
});

test('update country tool updates attributes and validates unique constraints', function () {
    BaharsyahServer::tool(UpdateCountryTool::class, [
        'id' => $this->country->id,
        'name' => 'Kerajaan Arab Saudi',
        'capital_city' => [
            'id' => 'Riyadh Baru',
            'en' => 'New Riyadh',
        ],
        'is_featured' => false,
    ])->assertSee('Country \'Kerajaan Arab Saudi\' updated successfully.');

    $this->country->refresh();
    expect($this->country->getTranslation('name', 'id'))->toBe('Kerajaan Arab Saudi')
        ->and($this->country->getTranslation('capital_city', 'en'))->toBe('New Riyadh')
        ->and($this->country->is_featured)->toBeFalse();
});

test('delete country tool requires force=true when connected to destinations, visa services, or articles', function () {
    $category = PostCategory::create([
        'name' => ['id' => 'Umrah', 'en' => 'Umrah'],
        'slug' => 'umrah',
    ]);
    $author = User::factory()->create();

    $post = Post::create([
        'user_id' => $author->id,
        'post_category_id' => $category->id,
        'title' => ['id' => 'Panduan Umrah', 'en' => 'Umrah Guide'],
        'slug' => ['id' => 'panduan-umrah'],
        'content' => ['id' => '<p>Konten</p>', 'en' => '<p>Content</p>'],
        'status' => 'published',
    ]);
    $this->country->posts()->attach($post->id);

    $visa = VisaService::create([
        'country_id' => $this->country->id,
        'name' => ['id' => 'Visa Umrah', 'en' => 'Umrah Visa'],
        'slug' => 'visa-umrah-saudi',
        'visa_type' => ['id' => 'Elektronik', 'en' => 'Electronic'],
        'is_active' => true,
    ]);

    // 1. Attempt delete without force must fail
    BaharsyahServer::tool(DeleteCountryTool::class, [
        'id' => $this->country->id,
        'force' => false,
    ])->assertSee("Cannot delete country 'Arab Saudi' because it is currently linked to 2 item(s) (0 destination(s), 1 visa service(s), 0 tour package(s), 0 umrah package(s), 1 article(s)). Set 'force' => true to detach links and delete.");

    // 2. Force delete succeeds, detaches relationships and soft-deletes country
    BaharsyahServer::tool(DeleteCountryTool::class, [
        'id' => $this->country->id,
        'force' => true,
    ])->assertSee("Country 'Arab Saudi' (ID {$this->country->id}) deleted successfully.");

    expect(Country::find($this->country->id))->toBeNull()
        ->and(Country::withTrashed()->find($this->country->id))->not->toBeNull()
        ->and($this->country->posts()->count())->toBe(0)
        ->and(VisaService::find($visa->id))->toBeNull(); // soft-deleted
});

test('country tools enforce granular oauth scopes', function () {
    $user = User::factory()->create();

    // 1. Token with 'mcp:read' can call list_countries
    $readToken = $user->createToken('Read Agent', ['mcp:read'])->accessToken;
    $this->withToken($readToken)->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'list_countries',
            'arguments' => [],
        ],
        'id' => 1,
    ])->assertStatus(200);

    // 2. Token with 'mcp:read' is forbidden to call create_country
    $this->withToken($readToken)->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'create_country',
            'arguments' => ['name' => 'Qatar', 'iso_alpha_2' => 'QA'],
        ],
        'id' => 2,
    ])
        ->assertStatus(403)
        ->assertJsonPath('error.message', "Forbidden: OAuth token lacks required scope 'mcp:write' for tool 'create_country'.");

    // 3. Token with 'mcp:write' is forbidden to call delete_country
    $writeToken = $user->createToken('Write Agent', ['mcp:write'])->accessToken;
    $this->withToken($writeToken)->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'delete_country',
            'arguments' => ['id' => $this->country->id],
        ],
        'id' => 3,
    ])
        ->assertStatus(403)
        ->assertJsonPath('error.message', "Forbidden: OAuth token lacks required scope 'mcp:admin' for tool 'delete_country'.");

    // 4. Token with 'mcp:admin' can call delete_country
    $adminToken = $user->createToken('Admin Agent', ['mcp:admin'])->accessToken;
    $this->withToken($adminToken)->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'delete_country',
            'arguments' => ['id' => $this->country->id, 'force' => true],
        ],
        'id' => 4,
    ])
        ->assertStatus(200)
        ->assertJsonPath('result.content.0.text', fn ($text) => str_contains($text, 'deleted successfully'));
});
