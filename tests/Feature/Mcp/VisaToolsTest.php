<?php

use App\Enums\VisaEntryType;
use App\Enums\VisaItemType;
use App\Mcp\Servers\BaharsyahServer;
use App\Mcp\Tools\VisaServices\CreateVisaServiceTool;
use App\Mcp\Tools\VisaServices\DeleteVisaServiceTool;
use App\Mcp\Tools\VisaServices\GetVisaServiceTool;
use App\Mcp\Tools\VisaServices\ListVisaServicesTool;
use App\Mcp\Tools\VisaServices\UpdateVisaServiceTool;
use App\Models\Country;
use App\Models\User;
use App\Models\VisaService;
use App\Models\VisaServiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Mcp\Server\Transport\FakeTransporter;
use Laravel\Passport\ClientRepository;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-static-agent-token-visa');

    $clientRepository = app(ClientRepository::class);
    $clientRepository->createPersonalAccessGrantClient('Test Personal Client', 'users');

    $this->country = Country::create([
        'name' => ['id' => 'Arab Saudi', 'en' => 'Saudi Arabia'],
        'slug' => 'arab-saudi',
        'iso_alpha_2' => 'SA',
        'iso_alpha_3' => 'SAU',
        'currency_code' => 'SAR',
    ]);

    $this->visa = VisaService::create([
        'country_id' => $this->country->id,
        'name' => ['id' => 'Visa Turis Elektronik', 'en' => 'Electronic Tourist Visa'],
        'slug' => 'visa-turis-elektronik-saudi',
        'visa_type' => ['id' => 'Turis', 'en' => 'Tourist'],
        'entry_type' => VisaEntryType::Multiple,
        'processing_days_min' => 2,
        'processing_days_max' => 5,
        'validity_days' => 365,
        'maximum_stay_days' => 90,
        'currency' => 'SAR',
        'price' => 350.00,
        'price_idr' => 1500000,
        'is_active' => true,
        'is_featured' => true,
    ]);

    VisaServiceItem::create([
        'visa_service_id' => $this->visa->id,
        'type' => VisaItemType::Requirement,
        'content' => ['id' => 'Paspor berlaku minimal 6 bulan', 'en' => 'Passport valid for at least 6 months'],
        'is_mandatory' => true,
    ]);

    VisaServiceItem::create([
        'visa_service_id' => $this->visa->id,
        'type' => VisaItemType::Included,
        'content' => ['id' => 'Asuransi Kesehatan Covid-19', 'en' => 'Covid-19 Health Insurance'],
    ]);
});

test('visa tools are registered in baharsyah server', function () {
    $server = app(BaharsyahServer::class, ['transport' => new FakeTransporter]);
    $reflection = new ReflectionClass($server);
    $tools = $reflection->getProperty('tools')->getValue($server);

    expect($tools)->toContain(
        ListVisaServicesTool::class,
        GetVisaServiceTool::class,
        CreateVisaServiceTool::class,
        UpdateVisaServiceTool::class,
        DeleteVisaServiceTool::class,
    );
});

test('list visa services returns filtered and paginated results', function () {
    $c2 = Country::create([
        'name' => ['id' => 'Jepang', 'en' => 'Japan'],
        'slug' => 'jepang',
        'iso_alpha_2' => 'JP',
        'currency_code' => 'JPY',
    ]);

    VisaService::create([
        'country_id' => $c2->id,
        'name' => ['id' => 'Visa Waiver Jepang'],
        'slug' => 'visa-waiver-jepang',
        'visa_type' => ['id' => 'Waiver'],
        'is_active' => false,
    ]);

    // 1. List active only
    BaharsyahServer::tool(ListVisaServicesTool::class, ['is_active' => true])
        ->assertSee('Visa Turis Elektronik')
        ->assertDontSee('Visa Waiver Jepang');

    // 2. Filter by country ISO code
    BaharsyahServer::tool(ListVisaServicesTool::class, ['iso_code' => 'sa'])
        ->assertSee('Visa Turis Elektronik');

    // 3. Filter by entry type
    BaharsyahServer::tool(ListVisaServicesTool::class, ['entry_type' => 'single'])
        ->assertDontSee('Visa Turis Elektronik');
});

test('get visa service returns detailed profile with grouped checklist items', function () {
    BaharsyahServer::tool(GetVisaServiceTool::class, ['slug' => 'visa-turis-elektronik-saudi', 'locale' => 'id'])
        ->assertSee('Visa Turis Elektronik')
        ->assertSee('Arab Saudi')
        ->assertSee('Paspor berlaku minimal 6 bulan')
        ->assertSee('Asuransi Kesehatan Covid-19');

    // Not found
    BaharsyahServer::tool(GetVisaServiceTool::class, ['slug' => 'non-existent-visa'])
        ->assertSee('Visa service with slug \'non-existent-visa\' not found.');
});

test('create visa service creates new service with checklist items in atomic transaction', function () {
    $response = BaharsyahServer::tool(CreateVisaServiceTool::class, [
        'country_iso' => 'SA',
        'name' => [
            'id' => 'Visa Umrah Reguler',
            'en' => 'Regular Umrah Visa',
        ],
        'slug' => 'visa-umrah-reguler-saudi',
        'visa_type' => 'Umrah',
        'entry_type' => 'single',
        'processing_days_min' => 3,
        'processing_days_max' => 7,
        'currency' => 'SAR',
        'price' => 500.00,
        'items' => [
            [
                'type' => 'requirement',
                'content' => 'Buku nikah asli bagi pasangan suami istri',
                'is_mandatory' => true,
            ],
            [
                'type' => 'included',
                'content' => 'Tasrih masuk Raudhah resmi',
            ],
            [
                'type' => 'term',
                'content' => 'Visa tidak dapat diuangkan kembali setelah diterbitkan',
            ],
        ],
    ]);

    $response->assertSee('Visa service \'Visa Umrah Reguler\' created successfully.');

    $created = VisaService::where('slug', 'visa-umrah-reguler-saudi')->with('items')->first();
    expect($created)->not->toBeNull()
        ->and($created->country_id)->toBe($this->country->id)
        ->and($created->currency)->toBe('SAR')
        ->and((float) $created->price)->toBe(500.0)
        ->and($created->items)->toHaveCount(3);
});

test('update visa service updates attributes and syncs checklist items', function () {
    BaharsyahServer::tool(UpdateVisaServiceTool::class, [
        'id' => $this->visa->id,
        'name' => 'Visa Turis Arab Saudi Update',
        'price' => 400.00,
        'items' => [
            [
                'type' => 'requirement',
                'content' => 'Item Persyaratan Baru',
            ],
        ],
    ])->assertSee('Visa service \'Visa Turis Arab Saudi Update\' updated successfully.');

    $this->visa->refresh();
    expect($this->visa->getTranslation('name', 'id'))->toBe('Visa Turis Arab Saudi Update')
        ->and((float) $this->visa->price)->toBe(400.0)
        ->and($this->visa->items)->toHaveCount(1)
        ->and($this->visa->items->first()->getTranslation('content', 'id'))->toBe('Item Persyaratan Baru');
});

test('delete visa service performs soft delete and permanent delete when force=true', function () {
    // 1. Soft-delete
    BaharsyahServer::tool(DeleteVisaServiceTool::class, [
        'id' => $this->visa->id,
        'force' => false,
    ])->assertSee('soft-deleted successfully.');

    expect(VisaService::find($this->visa->id))->toBeNull()
        ->and(VisaService::withTrashed()->find($this->visa->id))->not->toBeNull();

    // 2. Force delete
    BaharsyahServer::tool(DeleteVisaServiceTool::class, [
        'id' => $this->visa->id,
        'force' => true,
    ])->assertSee('permanently deleted successfully.');

    expect(VisaService::withTrashed()->find($this->visa->id))->toBeNull();
});

test('visa tools enforce granular oauth scopes', function () {
    $user = User::factory()->create();

    // 1. Token with 'mcp:read' can call list_visa_services
    $readToken = $user->createToken('Read Agent', ['mcp:read'])->accessToken;
    $this->withToken($readToken)->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'list_visa_services',
            'arguments' => [],
        ],
        'id' => 1,
    ])->assertStatus(200);

    // 2. Token with 'mcp:read' is forbidden to call create_visa_service
    $this->withToken($readToken)->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'create_visa_service',
            'arguments' => ['country_id' => $this->country->id, 'name' => 'Uji Scope', 'visa_type' => 'Turis'],
        ],
        'id' => 2,
    ])
        ->assertStatus(403)
        ->assertJsonPath('error.message', "Forbidden: OAuth token lacks required scope 'mcp:write' for tool 'create_visa_service'.");

    // 3. Token with 'mcp:write' is forbidden to call delete_visa_service
    $writeToken = $user->createToken('Write Agent', ['mcp:write'])->accessToken;
    $this->withToken($writeToken)->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'delete_visa_service',
            'arguments' => ['id' => $this->visa->id],
        ],
        'id' => 3,
    ])
        ->assertStatus(403)
        ->assertJsonPath('error.message', "Forbidden: OAuth token lacks required scope 'mcp:admin' for tool 'delete_visa_service'.");

    // 4. Token with 'mcp:admin' can call delete_visa_service
    $adminToken = $user->createToken('Admin Agent', ['mcp:admin'])->accessToken;
    $this->withToken($adminToken)->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'delete_visa_service',
            'arguments' => ['id' => $this->visa->id],
        ],
        'id' => 4,
    ])->assertStatus(200);
});
