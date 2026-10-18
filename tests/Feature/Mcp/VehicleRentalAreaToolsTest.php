<?php

use App\Enums\VehicleCategory;
use App\Mcp\Resources\VehicleRentalAreasResource;
use App\Mcp\Servers\BaharsyahServer;
use App\Mcp\Tools\Vehicles\CreateVehicleRentalAreaTool;
use App\Mcp\Tools\Vehicles\DeleteVehicleRentalAreaTool;
use App\Mcp\Tools\Vehicles\GetVehicleRentalAreaTool;
use App\Mcp\Tools\Vehicles\ListVehicleRentalAreasTool;
use App\Mcp\Tools\Vehicles\UpdateVehicleRentalAreaTool;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleRentalArea;
use App\Models\VehicleRentalRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Mcp\Server\Transport\FakeTransporter;
use Laravel\Passport\ClientRepository;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-static-agent-token-vehicle-area');

    $clientRepository = app(ClientRepository::class);
    $clientRepository->createPersonalAccessGrantClient('Test Personal Client', 'users');

    $this->area = VehicleRentalArea::create([
        'name' => [
            'id' => 'Makkah & Sekitarnya',
            'en' => 'Makkah & Surroundings',
            'ms' => 'Makkah & Sekitarnya',
        ],
        'slug' => 'makkah-sekitarnya',
        'description' => [
            'id' => 'Layanan rental mobil area kota Makkah dan ziarah sekitarnya.',
            'en' => 'Car rental services in Makkah city and holy sites.',
        ],
        'minimum_rental_days' => 2,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->vehicle = Vehicle::create([
        'catalog_code' => 'HIACE-01',
        'name' => ['id' => 'Toyota HiAce Commuter'],
        'slug' => ['id' => 'toyota-hiace-commuter'],
        'category' => VehicleCategory::Minibus,
        'capacity_pax' => 14,
        'transmission' => 'manual',
        'is_active' => true,
    ]);

    $this->rate = VehicleRentalRate::create([
        'vehicle_id' => $this->vehicle->id,
        'vehicle_rental_area_id' => $this->area->id,
        'price_per_day_idr' => 1500000,
        'valid_from' => now()->startOfDay(),
        'is_active' => true,
    ]);
});

test('vehicle rental area tools and resource are registered in baharsyah server', function () {
    $server = app(BaharsyahServer::class, ['transport' => new FakeTransporter]);
    $reflection = new ReflectionClass($server);

    $tools = $reflection->getProperty('tools')->getValue($server);
    expect($tools)->toContain(
        ListVehicleRentalAreasTool::class,
        GetVehicleRentalAreaTool::class,
        CreateVehicleRentalAreaTool::class,
        UpdateVehicleRentalAreaTool::class,
        DeleteVehicleRentalAreaTool::class,
    );

    $resources = $reflection->getProperty('resources')->getValue($server);
    expect($resources)->toContain(
        VehicleRentalAreasResource::class,
    );
});

test('list vehicle rental areas returns filtered results and counts', function () {
    VehicleRentalArea::create([
        'name' => ['id' => 'Madinah Munawwarah', 'en' => 'Medina Munawwarah'],
        'slug' => 'madinah-munawwarah',
        'minimum_rental_days' => 1,
        'is_active' => false,
        'sort_order' => 2,
    ]);

    // 1. List active only
    BaharsyahServer::tool(ListVehicleRentalAreasTool::class, ['is_active' => true])
        ->assertSee('Makkah & Sekitarnya', false)
        ->assertDontSee('Madinah Munawwarah');

    // 2. Search by keyword
    BaharsyahServer::tool(ListVehicleRentalAreasTool::class, ['search' => 'Madinah'])
        ->assertSee('Madinah Munawwarah')
        ->assertDontSee('Makkah');

    // 3. Checks counts in output
    BaharsyahServer::tool(ListVehicleRentalAreasTool::class, ['search' => 'Makkah'])
        ->assertSee('"rates_count":1', false)
        ->assertSee('"active_vehicles_count":1', false);
});

test('get vehicle rental area returns detailed data and active rates', function () {
    BaharsyahServer::tool(GetVehicleRentalAreaTool::class, ['slug' => 'makkah-sekitarnya', 'locale' => 'id'])
        ->assertSee('Makkah & Sekitarnya', false)
        ->assertSee('1500000')
        ->assertSee('Toyota HiAce Commuter');

    // Error if missing identifiers
    BaharsyahServer::tool(GetVehicleRentalAreaTool::class, [])
        ->assertSee('Either "id" or "slug" must be provided.');
});

test('create vehicle rental area handles multilingual input and auto-slug', function () {
    $response = BaharsyahServer::tool(CreateVehicleRentalAreaTool::class, [
        'name' => [
            'id' => 'Jeddah City & Airport',
            'en' => 'Jeddah City & Airport',
        ],
        'description' => 'Area penjemputan bandara King Abdulaziz dan kota Jeddah.',
        'minimum_rental_days' => 1,
        'sort_order' => 3,
    ]);

    $response->assertSee('Wilayah sewa kendaraan berhasil dibuat.')
        ->assertSee('jeddah-city-airport');

    $this->assertDatabaseHas('vehicle_rental_areas', [
        'slug' => 'jeddah-city-airport',
        'minimum_rental_days' => 1,
    ]);
});

test('create vehicle rental area prevents duplicate custom slug', function () {
    BaharsyahServer::tool(CreateVehicleRentalAreaTool::class, [
        'name' => 'Duplikat Makkah',
        'slug' => 'makkah-sekitarnya',
    ])->assertSee("Vehicle rental area with slug 'makkah-sekitarnya' already exists.");
});

test('update vehicle rental area updates partial fields and validates slug', function () {
    $response = BaharsyahServer::tool(UpdateVehicleRentalAreaTool::class, [
        'id' => $this->area->id,
        'minimum_rental_days' => 3,
        'new_slug' => 'makkah-al-mukarramah',
    ]);

    $response->assertSee('Wilayah sewa kendaraan berhasil diperbarui.')
        ->assertSee('makkah-al-mukarramah');

    expect($this->area->fresh()->minimum_rental_days)->toBe(3);
    expect($this->area->fresh()->slug)->toBe('makkah-al-mukarramah');
});

test('delete vehicle rental area enforces relation safeguards and supports force cascade', function () {
    // 1. Rejects deletion if linked with rates and force: false
    BaharsyahServer::tool(DeleteVehicleRentalAreaTool::class, [
        'id' => $this->area->id,
        'force' => false,
    ])->assertSee('Cannot delete rental area')
        ->assertSee('active rental rate(s)');

    // 2. Deletes rates and area when force: true
    $response = BaharsyahServer::tool(DeleteVehicleRentalAreaTool::class, [
        'id' => $this->area->id,
        'force' => true,
    ]);

    $response->assertSee('Wilayah sewa kendaraan berhasil dihapus.');
    $this->assertSoftDeleted('vehicle_rental_areas', ['id' => $this->area->id]);
    $this->assertDatabaseMissing('vehicle_rental_rates', ['id' => $this->rate->id]);
});

test('vehicle rental area tools enforce granular oauth scopes over http', function () {
    $adminUser = User::factory()->create();
    $readToken = $adminUser->createToken('Read Token', ['mcp:read'])->accessToken;
    $writeToken = $adminUser->createToken('Write Token', ['mcp:write'])->accessToken;
    $adminToken = $adminUser->createToken('Admin Token', ['mcp:admin'])->accessToken;

    // 1. list_vehicle_rental_areas allowed with mcp:read
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => [
            'name' => 'list_vehicle_rental_areas',
            'arguments' => [],
        ],
    ], [
        'Authorization' => "Bearer {$readToken}",
    ])->assertOk();

    // 2. create_vehicle_rental_area forbidden with mcp:read, allowed with mcp:write
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/call',
        'params' => [
            'name' => 'create_vehicle_rental_area',
            'arguments' => [
                'name' => 'Riyadh Area',
            ],
        ],
    ], [
        'Authorization' => "Bearer {$readToken}",
    ])->assertStatus(403);

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'tools/call',
        'params' => [
            'name' => 'create_vehicle_rental_area',
            'arguments' => [
                'name' => 'Riyadh Area',
            ],
        ],
    ], [
        'Authorization' => "Bearer {$writeToken}",
    ])->assertOk();

    // 3. delete_vehicle_rental_area forbidden with mcp:write, allowed with mcp:admin
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 4,
        'method' => 'tools/call',
        'params' => [
            'name' => 'delete_vehicle_rental_area',
            'arguments' => [
                'slug' => 'riyadh-area',
                'force' => true,
            ],
        ],
    ], [
        'Authorization' => "Bearer {$writeToken}",
    ])->assertStatus(403);

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 5,
        'method' => 'tools/call',
        'params' => [
            'name' => 'delete_vehicle_rental_area',
            'arguments' => [
                'slug' => 'riyadh-area',
                'force' => true,
            ],
        ],
    ], [
        'Authorization' => "Bearer {$adminToken}",
    ])->assertOk();
});

test('vehicle rental areas resource can be read and listed via mcp', function () {
    // 1. resources/list contains vehicles://rental-areas
    $listResponse = $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 10,
        'method' => 'resources/list',
    ], [
        'Authorization' => 'Bearer test-static-agent-token-vehicle-area',
    ]);

    $listResponse->assertOk()
        ->assertJsonFragment(['uri' => 'vehicles://rental-areas']);

    // 2. resources/read returns active areas data
    $readResponse = $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 11,
        'method' => 'resources/read',
        'params' => [
            'uri' => 'vehicles://rental-areas',
        ],
    ], [
        'Authorization' => 'Bearer test-static-agent-token-vehicle-area',
    ]);

    $readResponse->assertOk()
        ->assertSee('Makkah & Sekitarnya', false)
        ->assertSee('makkah-sekitarnya');
});
