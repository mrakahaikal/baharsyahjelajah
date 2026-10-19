<?php

use App\Enums\UmrahPackageType;
use App\Mcp\Prompts\ConsultUmrahPackagePrompt;
use App\Mcp\Prompts\DraftUmrahQuotationPrompt;
use App\Mcp\Resources\UmrahCatalogResource;
use App\Mcp\Resources\UmrahDeparturesScheduleResource;
use App\Mcp\Servers\BaharsyahServer;
use App\Mcp\Tools\Umrah\CalculateUmrahPriceTool;
use App\Mcp\Tools\Umrah\CreateUmrahPackageTool;
use App\Mcp\Tools\Umrah\DeleteUmrahPackageTool;
use App\Mcp\Tools\Umrah\GetUmrahPackageTool;
use App\Mcp\Tools\Umrah\ListUmrahDeparturesTool;
use App\Mcp\Tools\Umrah\ListUmrahPackagesTool;
use App\Mcp\Tools\Umrah\ManageUmrahDepartureTool;
use App\Mcp\Tools\Umrah\ManageUmrahIncludesTool;
use App\Mcp\Tools\Umrah\ManageUmrahItineraryTool;
use App\Mcp\Tools\Umrah\ManageUmrahPricesTool;
use App\Mcp\Tools\Umrah\UpdateUmrahPackageTool;
use App\Models\UmrahPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Mcp\Server\Transport\FakeTransporter;
use Laravel\Passport\ClientRepository;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-static-agent-token-umrah-packages');

    $clientRepository = app(ClientRepository::class);
    $clientRepository->createPersonalAccessGrantClient('Test Personal Client', 'users');

    $this->packageVip = UmrahPackage::create([
        'name' => [
            'id' => 'Paket Umrah VIP Bintang 5',
            'en' => 'VIP Umrah Package 5-Star',
        ],
        'slug' => [
            'id' => 'paket-umrah-vip-bintang-5',
            'en' => 'vip-umrah-package-5-star',
        ],
        'package_type' => UmrahPackageType::Vip,
        'duration_days' => 12,
        'duration_nights' => 11,
        'price_idr' => 45000000,
        'hotel_makkah' => 'Fairmont Makkah Clock Royal Tower',
        'hotel_madinah' => 'Dar Al Taqwa Madinah',
        'airline' => 'Saudia Airlines',
        'departure_city' => 'Jakarta (CGK)',
        'description' => ['id' => '<p>Umrah VIP fasilitas mewah dekat pelataran masjid.</p>'],
        'is_active' => true,
        'is_featured' => true,
    ]);

    $this->packageRamadan = UmrahPackage::create([
        'name' => [
            'id' => 'Paket Umrah Itikaf Akhir Ramadan',
            'en' => 'Last 10 Days Ramadan Itikaf Umrah',
        ],
        'slug' => [
            'id' => 'paket-umrah-itikaf-akhir-ramadan',
            'en' => 'last-10-days-ramadan-itikaf-umrah',
        ],
        'package_type' => UmrahPackageType::Ramadan,
        'duration_days' => 16,
        'duration_nights' => 15,
        'price_idr' => 52000000,
        'hotel_makkah' => 'Pullman Zamzam',
        'hotel_madinah' => 'Rove Madinah',
        'airline' => 'Garuda Indonesia',
        'departure_city' => 'Surabaya (SUB)',
        'description' => ['id' => '<p>Meraih keutamaan malam Lailatul Qadar di Tanah Suci.</p>'],
        'is_active' => false,
        'is_featured' => false,
    ]);
});

test('umrah tools, resources, and prompts are registered in baharsyah server', function () {
    $server = app(BaharsyahServer::class, ['transport' => new FakeTransporter]);
    $reflection = new ReflectionClass($server);

    $tools = $reflection->getProperty('tools')->getValue($server);
    expect($tools)->toContain(
        ListUmrahPackagesTool::class,
        GetUmrahPackageTool::class,
        CreateUmrahPackageTool::class,
        UpdateUmrahPackageTool::class,
        DeleteUmrahPackageTool::class,
        ListUmrahDeparturesTool::class,
        ManageUmrahDepartureTool::class,
        CalculateUmrahPriceTool::class,
        ManageUmrahPricesTool::class,
        ManageUmrahItineraryTool::class,
        ManageUmrahIncludesTool::class,
    );

    $resources = $reflection->getProperty('resources')->getValue($server);
    expect($resources)->toContain(
        UmrahCatalogResource::class,
        UmrahDeparturesScheduleResource::class,
    );

    $prompts = $reflection->getProperty('prompts')->getValue($server);
    expect($prompts)->toContain(
        ConsultUmrahPackagePrompt::class,
        DraftUmrahQuotationPrompt::class,
    );
});

test('list umrah packages returns filtered and searched records', function () {
    BaharsyahServer::tool(ListUmrahPackagesTool::class, [])
        ->assertSee('Paket Umrah VIP Bintang 5')
        ->assertSee('Paket Umrah Itikaf Akhir Ramadan');

    BaharsyahServer::tool(ListUmrahPackagesTool::class, [
        'is_active' => true,
    ])
        ->assertSee('Paket Umrah VIP Bintang 5')
        ->assertDontSee('Paket Umrah Itikaf Akhir Ramadan');

    BaharsyahServer::tool(ListUmrahPackagesTool::class, [
        'package_type' => 'vip',
    ])
        ->assertSee('Paket Umrah VIP Bintang 5')
        ->assertDontSee('Paket Umrah Itikaf Akhir Ramadan');

    BaharsyahServer::tool(ListUmrahPackagesTool::class, [
        'search' => 'Ramadan',
    ])
        ->assertSee('Paket Umrah Itikaf Akhir Ramadan')
        ->assertDontSee('Paket Umrah VIP Bintang 5');
});

test('get umrah package retrieves package by id and localized slug', function () {
    BaharsyahServer::tool(GetUmrahPackageTool::class, [
        'id' => $this->packageVip->id,
    ])
        ->assertSee('Paket Umrah VIP Bintang 5')
        ->assertSee('Fairmont Makkah Clock Royal Tower')
        ->assertSee('Saudia Airlines');

    BaharsyahServer::tool(GetUmrahPackageTool::class, [
        'slug' => 'paket-umrah-vip-bintang-5',
    ])
        ->assertSee('Paket Umrah VIP Bintang 5')
        ->assertSee('Fairmont Makkah Clock Royal Tower');
});

test('create, update, and delete umrah package works correctly', function () {
    $createResponse = BaharsyahServer::tool(CreateUmrahPackageTool::class, [
        'name' => [
            'id' => 'Paket Umrah Plus Turki 12 Hari',
            'en' => 'Umrah Plus Turkey 12 Days',
        ],
        'package_type' => 'plus',
        'duration_days' => 12,
        'duration_nights' => 11,
        'price_idr' => 38000000,
        'hotel_makkah' => 'Movenpick Hajar Tower',
        'hotel_madinah' => 'Frontel Al Harithia',
        'airline' => 'Turkish Airlines',
        'departure_city' => 'Jakarta (CGK)',
        'description' => ['id' => '<p>Ziarah Tanah Suci dan wisata sejarah di Istanbul.</p>'],
        'is_active' => true,
        'is_featured' => true,
    ]);

    $createResponse->assertSee('Paket umrah berhasil dibuat.')
        ->assertSee('paket-umrah-plus-turki-12-hari')
        ->assertSee('Turkish Airlines');

    $createdPackage = UmrahPackage::whereLocalizedSlug('paket-umrah-plus-turki-12-hari')->firstOrFail();

    $updateResponse = BaharsyahServer::tool(UpdateUmrahPackageTool::class, [
        'id' => $createdPackage->id,
        'name' => [
            'id' => 'Paket Umrah Plus Turki & Cappadocia 13 Hari',
        ],
        'duration_days' => 13,
        'duration_nights' => 12,
        'airline' => 'Emirates',
    ]);

    $updateResponse->assertSee('Paket umrah berhasil diperbarui.')
        ->assertSee('Paket Umrah Plus Turki & Cappadocia 13 Hari')
        ->assertSee('Emirates');

    $deleteResponse = BaharsyahServer::tool(DeleteUmrahPackageTool::class, [
        'id' => $createdPackage->id,
    ]);

    $deleteResponse->assertSee('Paket umrah berhasil dihapus.');
    $this->assertDatabaseMissing('umrah_packages', ['id' => $createdPackage->id]);
});

test('umrah package tools enforce granular oauth scopes', function () {
    $user = User::factory()->create();
    $readToken = $user->createToken('Read Token', ['mcp:read'])->accessToken;
    $writeToken = $user->createToken('Write Token', ['mcp:write'])->accessToken;
    $adminToken = $user->createToken('Admin Token', ['mcp:admin'])->accessToken;

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => ['name' => 'list_umrah_packages', 'arguments' => []],
    ], ['Authorization' => "Bearer {$readToken}"])->assertOk();

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/call',
        'params' => [
            'name' => 'create_umrah_package',
            'arguments' => [
                'name' => 'Paket Umrah Hemat 9 Hari',
                'description' => 'Umrah hemat terjangkau',
                'duration_days' => 9,
                'duration_nights' => 8,
                'price_idr' => 26000000,
            ],
        ],
    ], ['Authorization' => "Bearer {$readToken}"])->assertStatus(403);

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'tools/call',
        'params' => [
            'name' => 'create_umrah_package',
            'arguments' => [
                'name' => 'Paket Umrah Hemat 9 Hari',
                'description' => 'Umrah hemat terjangkau',
                'duration_days' => 9,
                'duration_nights' => 8,
                'price_idr' => 26000000,
            ],
        ],
    ], ['Authorization' => "Bearer {$writeToken}"])->assertOk();

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 4,
        'method' => 'tools/call',
        'params' => ['name' => 'delete_umrah_package', 'arguments' => ['id' => $this->packageRamadan->id]],
    ], ['Authorization' => "Bearer {$writeToken}"])->assertStatus(403);

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 5,
        'method' => 'tools/call',
        'params' => ['name' => 'delete_umrah_package', 'arguments' => ['id' => $this->packageRamadan->id]],
    ], ['Authorization' => "Bearer {$adminToken}"])->assertOk();
});
