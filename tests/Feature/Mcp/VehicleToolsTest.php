<?php

use App\Enums\VehicleCategory;
use App\Mcp\Servers\BaharsyahServer;
use App\Mcp\Tools\Vehicles\CreateVehicleTool;
use App\Mcp\Tools\Vehicles\DeleteVehicleTool;
use App\Mcp\Tools\Vehicles\GetVehicleTool;
use App\Mcp\Tools\Vehicles\ListVehiclesTool;
use App\Mcp\Tools\Vehicles\UpdateVehicleTool;
use App\Models\Country;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\TourPackage;
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
    Config::set('mcp.auth_token', 'test-static-agent-token-vehicle');

    $clientRepository = app(ClientRepository::class);
    $clientRepository->createPersonalAccessGrantClient('Test Personal Client', 'users');

    $this->country = Country::create([
        'name' => ['id' => 'Indonesia', 'en' => 'Indonesia'],
        'slug' => 'indonesia',
        'iso_alpha_2' => 'ID',
    ]);

    $this->area = VehicleRentalArea::create([
        'name' => ['id' => 'Balikpapan & Sekitarnya', 'en' => 'Balikpapan & Surroundings'],
        'slug' => 'balikpapan-sekitarnya',
        'minimum_rental_days' => 1,
        'is_active' => true,
    ]);

    $this->vehicle = Vehicle::create([
        'catalog_code' => 'INNOVA-ZENIX-01',
        'name' => ['id' => 'Toyota Innova Zenix Hybrid', 'en' => 'Toyota Innova Zenix Hybrid'],
        'slug' => ['id' => 'toyota-innova-zenix-hybrid'],
        'category' => VehicleCategory::Car,
        'brand' => 'Toyota',
        'model' => 'Innova Zenix V',
        'year' => 2023,
        'capacity_pax' => 7,
        'capacity_luggage' => 4,
        'transmission' => 'automatic',
        'has_ac' => true,
        'has_wifi' => true,
        'price_per_day_idr' => 900000,
        'price_per_trip_idr' => 450000,
        'overtime_rate_idr' => 90000,
        'is_active' => true,
        'is_featured' => true,
    ]);
    $this->vehicle->countries()->attach($this->country->id);

    VehicleRentalRate::create([
        'vehicle_id' => $this->vehicle->id,
        'vehicle_rental_area_id' => $this->area->id,
        'price_per_day_idr' => 950000,
        'valid_from' => now()->startOfDay(),
        'is_active' => true,
    ]);
});

test('vehicle tools are registered in baharsyah server', function () {
    $server = app(BaharsyahServer::class, ['transport' => new FakeTransporter]);
    $reflection = new ReflectionClass($server);
    $tools = $reflection->getProperty('tools')->getValue($server);

    expect($tools)->toContain(
        ListVehiclesTool::class,
        GetVehicleTool::class,
        CreateVehicleTool::class,
        UpdateVehicleTool::class,
        DeleteVehicleTool::class,
    );
});

test('list vehicles returns filtered and paginated results', function () {
    Vehicle::create([
        'catalog_code' => 'BUS-PARIWISATA-35',
        'name' => ['id' => 'Bus Pariwisata Medium 35 Seat'],
        'slug' => ['id' => 'bus-pariwisata-medium-35-seat'],
        'category' => VehicleCategory::Bus,
        'capacity_pax' => 35,
        'transmission' => 'manual',
        'is_active' => false,
    ]);

    // 1. List active only
    BaharsyahServer::tool(ListVehiclesTool::class, ['is_active' => true])
        ->assertSee('Toyota Innova Zenix Hybrid')
        ->assertDontSee('Bus Pariwisata Medium');

    // 2. Filter by category
    BaharsyahServer::tool(ListVehiclesTool::class, ['category' => 'car'])
        ->assertSee('Toyota Innova Zenix Hybrid');

    // 3. Filter by min passengers
    BaharsyahServer::tool(ListVehiclesTool::class, ['min_passengers' => 10])
        ->assertDontSee('Toyota Innova Zenix Hybrid');
});

test('get vehicle returns detailed profile with specs and area rates', function () {
    BaharsyahServer::tool(GetVehicleTool::class, ['catalog_code' => 'INNOVA-ZENIX-01', 'locale' => 'id'])
        ->assertSee('Toyota Innova Zenix Hybrid')
        ->assertSee('Balikpapan & Sekitarnya')
        ->assertSee('950000')
        ->assertSee('Indonesia');

    // Not found
    BaharsyahServer::tool(GetVehicleTool::class, ['catalog_code' => 'UNKNOWN-99'])
        ->assertSee('Vehicle with catalog code \'UNKNOWN-99\' not found.');
});

test('create vehicle creates new fleet entry with rates and country links', function () {
    $response = BaharsyahServer::tool(CreateVehicleTool::class, [
        'name' => [
            'id' => 'Toyota HiAce Premio Luxury',
            'en' => 'Toyota HiAce Premio Luxury',
        ],
        'slug' => 'toyota-hiace-premio-luxury',
        'catalog_code' => 'HIACE-PREMIO-LUX',
        'category' => 'minibus',
        'brand' => 'Toyota',
        'model' => 'HiAce Premio',
        'year' => 2024,
        'capacity_pax' => 12,
        'capacity_luggage' => 8,
        'transmission' => 'manual',
        'price_per_day_idr' => 1400000,
        'country_slugs' => ['indonesia'],
        'rental_rates' => [
            [
                'area_id' => $this->area->id,
                'price_per_day_idr' => 1500000,
            ],
        ],
    ]);

    $response->assertSee('Vehicle \'Toyota HiAce Premio Luxury\' created successfully.');

    $created = Vehicle::where('catalog_code', 'HIACE-PREMIO-LUX')->with(['countries', 'rentalRates'])->first();
    expect($created)->not->toBeNull()
        ->and($created->category)->toBe(VehicleCategory::Minibus)
        ->and($created->capacity_pax)->toBe(12)
        ->and($created->countries)->toHaveCount(1)
        ->and($created->rentalRates)->toHaveCount(1);
});

test('update vehicle updates attributes and syncs area rental rates', function () {
    BaharsyahServer::tool(UpdateVehicleTool::class, [
        'id' => $this->vehicle->id,
        'name' => 'Toyota Innova Zenix Facelift',
        'price_per_day_idr' => 1000000,
        'capacity_luggage' => 6,
    ])->assertSee('Vehicle \'Toyota Innova Zenix Facelift\' updated successfully.');

    $this->vehicle->refresh();
    expect($this->vehicle->getTranslation('name', 'id'))->toBe('Toyota Innova Zenix Facelift')
        ->and($this->vehicle->price_per_day_idr)->toBe(1000000)
        ->and($this->vehicle->capacity_luggage)->toBe(6);
});

test('delete vehicle requires force=true when connected to tour packages', function () {
    $tourCategory = TourCategory::create([
        'name' => ['id' => 'Wisata Alam'],
        'slug' => ['id' => 'wisata-alam'],
    ]);

    $tour = Tour::create([
        'tour_category_id' => $tourCategory->id,
        'name' => ['id' => 'Eksplorasi Mahakam'],
        'slug' => ['id' => 'eksplorasi-mahakam'],
    ]);

    $package = TourPackage::create([
        'tour_id' => $tour->id,
        'name' => ['id' => 'Paket Mahakam 3D2N'],
        'slug' => ['id' => 'paket-mahakam-3d2n'],
        'duration_days' => 3,
        'duration_nights' => 2,
    ]);

    $this->vehicle->tourPackages()->attach($package->id);

    // 1. Delete without force rejected
    BaharsyahServer::tool(DeleteVehicleTool::class, [
        'id' => $this->vehicle->id,
        'force' => false,
    ])->assertSee('because it is currently linked to 1 package(s)');

    expect(Vehicle::find($this->vehicle->id))->not->toBeNull();

    // 2. Delete with force=true succeeds
    BaharsyahServer::tool(DeleteVehicleTool::class, [
        'id' => $this->vehicle->id,
        'force' => true,
    ])->assertSee('deleted successfully.');

    expect(Vehicle::find($this->vehicle->id))->toBeNull()
        ->and(Vehicle::withTrashed()->find($this->vehicle->id))->not->toBeNull()
        ->and($package->vehicles()->count())->toBe(0);
});

test('vehicle tools enforce granular oauth scopes', function () {
    $user = User::factory()->create();

    // 1. Token with 'mcp:read' can call list_vehicles
    $readToken = $user->createToken('Read Agent', ['mcp:read'])->accessToken;
    $this->withToken($readToken)->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'list_vehicles',
            'arguments' => [],
        ],
        'id' => 1,
    ])->assertStatus(200);

    // 2. Token with 'mcp:read' is forbidden to call create_vehicle
    $this->withToken($readToken)->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'create_vehicle',
            'arguments' => ['name' => 'Uji Scope', 'category' => 'car', 'capacity_pax' => 4],
        ],
        'id' => 2,
    ])
        ->assertStatus(403)
        ->assertJsonPath('error.message', "Forbidden: OAuth token lacks required scope 'mcp:write' for tool 'create_vehicle'.");

    // 3. Token with 'mcp:write' is forbidden to call delete_vehicle
    $writeToken = $user->createToken('Write Agent', ['mcp:write'])->accessToken;
    $this->withToken($writeToken)->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'delete_vehicle',
            'arguments' => ['id' => $this->vehicle->id],
        ],
        'id' => 3,
    ])
        ->assertStatus(403)
        ->assertJsonPath('error.message', "Forbidden: OAuth token lacks required scope 'mcp:admin' for tool 'delete_vehicle'.");

    // 4. Token with 'mcp:admin' can call delete_vehicle
    $adminToken = $user->createToken('Admin Agent', ['mcp:admin'])->accessToken;
    $this->withToken($adminToken)->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'delete_vehicle',
            'arguments' => ['id' => $this->vehicle->id, 'force' => true],
        ],
        'id' => 4,
    ])->assertStatus(200);
});
