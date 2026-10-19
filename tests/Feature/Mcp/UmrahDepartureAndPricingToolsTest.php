<?php

use App\Enums\UmrahPackageType;
use App\Enums\UmrahRoomType;
use App\Mcp\Servers\BaharsyahServer;
use App\Mcp\Tools\Umrah\CalculateUmrahPriceTool;
use App\Mcp\Tools\Umrah\ListUmrahDeparturesTool;
use App\Mcp\Tools\Umrah\ManageUmrahDepartureTool;
use App\Mcp\Tools\Umrah\ManageUmrahPricesTool;
use App\Models\UmrahDeparture;
use App\Models\UmrahPackage;
use App\Models\UmrahPackagePrice;
use App\Models\UmrahPackagePrivatePrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\ClientRepository;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-static-agent-token-umrah-pricing');

    $clientRepository = app(ClientRepository::class);
    $clientRepository->createPersonalAccessGrantClient('Test Personal Client', 'users');

    $this->regularPackage = UmrahPackage::create([
        'name' => ['id' => 'Paket Umrah Reguler 9 Hari'],
        'slug' => ['id' => 'paket-umrah-reguler-9-hari'],
        'package_type' => UmrahPackageType::Regular,
        'duration_days' => 9,
        'duration_nights' => 8,
        'price_idr' => 27500000,
        'airline' => 'Saudia Airlines',
        'is_active' => true,
    ]);

    $this->privatePackage = UmrahPackage::create([
        'name' => ['id' => 'Paket Umrah Private Mandiri'],
        'slug' => ['id' => 'paket-umrah-private-mandiri'],
        'package_type' => UmrahPackageType::Private,
        'duration_days' => 10,
        'duration_nights' => 9,
        'price_idr' => 35000000,
        'airline' => 'Garuda Indonesia',
        'is_active' => true,
    ]);

    // Regular room pricing
    UmrahPackagePrice::create([
        'umrah_package_id' => $this->regularPackage->id,
        'room_type' => UmrahRoomType::Quad,
        'price_idr' => 27500000,
    ]);
    UmrahPackagePrice::create([
        'umrah_package_id' => $this->regularPackage->id,
        'room_type' => UmrahRoomType::Double,
        'price_idr' => 31500000,
    ]);

    // Private pricing tier
    UmrahPackagePrivatePrice::create([
        'umrah_package_id' => $this->privatePackage->id,
        'duration_nights' => 9,
        'pax' => 4,
        'price_idr' => 35000000,
    ]);

    // Departure
    $this->departure1 = UmrahDeparture::create([
        'package_id' => $this->regularPackage->id,
        'departure_date' => now()->addDays(20)->toDateString(),
        'return_date' => now()->addDays(29)->toDateString(),
        'quota_total' => 45,
        'quota_booked' => 42,
        'status' => 'nearly_full',
    ]);
});

test('list umrah departures returns schedules with filter by package and status', function () {
    $dep2 = UmrahDeparture::create([
        'package_id' => $this->regularPackage->id,
        'departure_date' => now()->addDays(60)->toDateString(),
        'return_date' => now()->addDays(69)->toDateString(),
        'quota_total' => 45,
        'quota_booked' => 10,
        'status' => 'open',
    ]);

    BaharsyahServer::tool(ListUmrahDeparturesTool::class, [
        'umrah_package_id' => $this->regularPackage->id,
    ])
        ->assertSee($this->departure1->departure_date->toDateString())
        ->assertSee($dep2->departure_date->toDateString());

    BaharsyahServer::tool(ListUmrahDeparturesTool::class, [
        'status' => 'nearly_full',
    ])
        ->assertSee($this->departure1->departure_date->toDateString())
        ->assertDontSee($dep2->departure_date->toDateString());
});

test('manage umrah departure handles create, update, and delete actions with quota auto status', function () {
    // 1. Create with quota_total = 30, quota_booked = 28 -> 28/30 = 93% (>=80%) -> auto nearly_full
    $createResponse = BaharsyahServer::tool(ManageUmrahDepartureTool::class, [
        'action' => 'create',
        'package_id' => $this->regularPackage->id,
        'departure_date' => now()->addDays(90)->toDateString(),
        'return_date' => now()->addDays(99)->toDateString(),
        'quota_total' => 30,
        'quota_booked' => 28,
    ]);

    $createResponse->assertSee('Jadwal keberangkatan umrah berhasil dibuat.')
        ->assertSee('nearly_full');

    $createdDep = UmrahDeparture::where('package_id', $this->regularPackage->id)->where('quota_total', 30)->firstOrFail();
    $depId = $createdDep->id;

    // 2. Update to quota_booked = 30 -> 100% -> auto full
    $updateResponse = BaharsyahServer::tool(ManageUmrahDepartureTool::class, [
        'action' => 'update',
        'departure_id' => $depId,
        'quota_booked' => 30,
    ]);

    $updateResponse->assertSee('Jadwal keberangkatan umrah berhasil diperbarui.')
        ->assertSee('full');

    // 3. Delete departure
    $deleteResponse = BaharsyahServer::tool(ManageUmrahDepartureTool::class, [
        'action' => 'delete',
        'departure_id' => $depId,
    ]);

    $deleteResponse->assertSee('Jadwal keberangkatan umrah berhasil dihapus.');
    $this->assertDatabaseMissing('umrah_departures', ['id' => $depId]);
});

test('calculate umrah price computes regular and private packages accurately', function () {
    // 1. Regular Quad: 27,500,000 x 2 pax = 55,000,000
    $regularResponse = BaharsyahServer::tool(CalculateUmrahPriceTool::class, [
        'umrah_package_id' => $this->regularPackage->id,
        'room_type' => 'quad',
        'pax' => 2,
    ]);

    $regularResponse->assertSee('Estimasi harga paket umrah berhasil dihitung.')
        ->assertSee('27500000')
        ->assertSee('55000000');

    // 2. Private: duration 9 nights, 4 pax -> tier price 35,000,000 x 4 pax = 140,000,000
    $privateResponse = BaharsyahServer::tool(CalculateUmrahPriceTool::class, [
        'umrah_package_id' => $this->privatePackage->id,
        'is_private' => true,
        'duration_nights' => 9,
        'pax' => 4,
    ]);

    $privateResponse->assertSee('Estimasi harga paket umrah private berhasil dihitung.')
        ->assertSee('35000000')
        ->assertSee('140000000');
});

test('manage umrah prices updates room rates and private pricing tiers', function () {
    $manageResponse = BaharsyahServer::tool(ManageUmrahPricesTool::class, [
        'umrah_package_id' => $this->regularPackage->id,
        'prices' => [
            ['room_type' => 'quad', 'price_idr' => 29000000],
            ['room_type' => 'triple', 'price_idr' => 31000000],
            ['room_type' => 'double', 'price_idr' => 34000000],
            ['room_type' => 'single', 'price_idr' => 40000000],
        ],
    ]);

    $manageResponse->assertSee('Daftar harga paket umrah berhasil diperbarui.')
        ->assertSee('quad')
        ->assertSee('29000000')
        ->assertSee('single')
        ->assertSee('40000000');

    $this->assertDatabaseHas('umrah_package_prices', [
        'umrah_package_id' => $this->regularPackage->id,
        'room_type' => 'single',
        'price_idr' => 40000000,
    ]);

    // Update private tiers
    $privateManageResponse = BaharsyahServer::tool(ManageUmrahPricesTool::class, [
        'umrah_package_id' => $this->privatePackage->id,
        'private_prices' => [
            ['duration_nights' => 12, 'pax' => 6, 'price_idr' => 42000000],
        ],
    ]);

    $privateManageResponse->assertSee('Daftar harga paket umrah berhasil diperbarui.')
        ->assertSee('42000000');

    $this->assertDatabaseHas('umrah_package_private_prices', [
        'umrah_package_id' => $this->privatePackage->id,
        'duration_nights' => 12,
        'pax' => 6,
        'price_idr' => 42000000,
    ]);
});

test('departure and pricing tools enforce granular oauth scopes', function () {
    $user = User::factory()->create();
    $readToken = $user->createToken('Read Token', ['mcp:read'])->accessToken;
    $writeToken = $user->createToken('Write Token', ['mcp:write'])->accessToken;

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => [
            'name' => 'calculate_umrah_price',
            'arguments' => [
                'umrah_package_id' => $this->regularPackage->id,
                'room_type' => 'quad',
            ],
        ],
    ], ['Authorization' => "Bearer {$readToken}"])->assertOk();

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/call',
        'params' => [
            'name' => 'manage_umrah_departure',
            'arguments' => [
                'action' => 'create',
                'umrah_package_id' => $this->regularPackage->id,
                'departure_date' => now()->addDays(40)->toDateString(),
                'return_date' => now()->addDays(49)->toDateString(),
                'quota' => 20,
            ],
        ],
    ], ['Authorization' => "Bearer {$readToken}"])->assertStatus(403);

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'tools/call',
        'params' => [
            'name' => 'manage_umrah_departure',
            'arguments' => [
                'action' => 'create',
                'umrah_package_id' => $this->regularPackage->id,
                'departure_date' => now()->addDays(40)->toDateString(),
                'return_date' => now()->addDays(49)->toDateString(),
                'quota' => 20,
            ],
        ],
    ], ['Authorization' => "Bearer {$writeToken}"])->assertOk();
});
