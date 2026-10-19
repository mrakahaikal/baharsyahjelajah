<?php

use App\Enums\TourType;
use App\Mcp\Servers\BaharsyahServer;
use App\Mcp\Tools\Tours\CalculateTourPriceTool;
use App\Mcp\Tools\Tours\ManageTourIncludesTool;
use App\Mcp\Tools\Tours\ManageTourItineraryTool;
use App\Mcp\Tools\Tours\ManageTourPricingTierTool;
use App\Mcp\Tools\Tours\Packages\CreateTourPackageTool;
use App\Mcp\Tools\Tours\Packages\DeleteTourPackageTool;
use App\Mcp\Tools\Tours\Packages\GetTourPackageTool;
use App\Mcp\Tools\Tours\Packages\ListTourPackagesTool;
use App\Mcp\Tools\Tours\Packages\UpdateTourPackageTool;
use App\Models\Destination;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\TourPackage;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\ClientRepository;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-static-agent-token-tour-packages');

    $clientRepository = app(ClientRepository::class);
    $clientRepository->createPersonalAccessGrantClient('Test Personal Client', 'users');

    $this->category = TourCategory::create([
        'name' => ['id' => 'Wisata Alam'],
        'slug' => ['id' => 'wisata-alam'],
        'sort_order' => 1,
    ]);

    $this->tour = Tour::create([
        'tour_category_id' => $this->category->id,
        'name' => ['id' => 'Eksplorasi Labuan Bajo & Komodo'],
        'slug' => ['id' => 'eksplorasi-labuan-bajo-komodo'],
        'short_description' => ['id' => 'Sailing Komodo 3H2M & 4H3M'],
        'description' => ['id' => '<p>Berlayar di Taman Nasional Komodo</p>'],
        'tour_type' => TourType::Domestic,
        'currency' => 'IDR',
        'is_active' => true,
    ]);

    $this->destinationPadar = Destination::create([
        'name' => ['id' => 'Pulau Padar', 'en' => 'Padar Island'],
        'slug' => 'pulau-padar',
        'description' => ['id' => '<p>Bukit ikonik tiga teluk di Taman Nasional Komodo.</p>'],
        'location' => 'Manggarai Barat, NTT',
        'is_active' => true,
        'is_featured' => true,
    ]);

    $this->vehicleHiace = Vehicle::create([
        'catalog_code' => 'FLT-HIACE-01',
        'name' => ['id' => 'Toyota Hiace Premio'],
        'slug' => ['id' => 'toyota-hiace-premio'],
        'category' => 'minibus',
        'capacity_pax' => 12,
        'capacity_luggage' => 10,
        'transmission' => 'manual',
        'has_ac' => true,
        'is_active' => true,
    ]);

    $this->package = TourPackage::create([
        'tour_id' => $this->tour->id,
        'name' => ['id' => 'Sailing Phinisi 3 Hari 2 Malam', 'en' => 'Phinisi Sailing 3 Days 2 Nights'],
        'slug' => ['id' => 'sailing-phinisi-3-hari-2-malam', 'en' => 'phinisi-sailing-3-days-2-nights'],
        'duration_days' => 3,
        'duration_nights' => 2,
    ]);
    $this->package->destinations()->sync([$this->destinationPadar->id]);
});

test('create, list, get, update, and delete tour package work with relations', function () {
    $createResponse = BaharsyahServer::tool(CreateTourPackageTool::class, [
        'tour_id' => $this->tour->id,
        'name' => [
            'id' => 'Private Speedboat 4 Hari 3 Malam',
            'en' => 'Private Speedboat 4 Days 3 Nights',
        ],
        'duration_days' => 4,
        'duration_nights' => 3,
        'destination_ids' => [$this->destinationPadar->id],
        'vehicle_ids' => [$this->vehicleHiace->id],
    ]);

    $createResponse->assertSee('Paket tur berhasil dibuat.')
        ->assertSee('private-speedboat-4-hari-3-malam')
        ->assertSee('Pulau Padar')
        ->assertSee('Toyota Hiace Premio');

    $createdPkg = TourPackage::whereLocalizedSlug('private-speedboat-4-hari-3-malam')->firstOrFail();

    BaharsyahServer::tool(ListTourPackagesTool::class, [
        'tour_id' => $this->tour->id,
        'duration_days' => 4,
    ])->assertSee('Private Speedboat 4 Hari 3 Malam')
        ->assertDontSee('Sailing Phinisi 3 Hari 2 Malam');

    BaharsyahServer::tool(GetTourPackageTool::class, ['id' => $createdPkg->id])
        ->assertSee('Private Speedboat 4 Hari 3 Malam')
        ->assertSee('Pulau Padar')
        ->assertSee('Toyota Hiace Premio');

    BaharsyahServer::tool(UpdateTourPackageTool::class, [
        'id' => $createdPkg->id,
        'name' => ['id' => 'Luxury Speedboat 5 Hari 4 Malam'],
        'duration_days' => 5,
        'duration_nights' => 4,
    ])->assertSee('Paket tur berhasil diperbarui.')
        ->assertSee('Luxury Speedboat 5 Hari 4 Malam');

    expect($createdPkg->fresh()->duration_days)->toBe(5);

    BaharsyahServer::tool(DeleteTourPackageTool::class, ['id' => $createdPkg->id])
        ->assertSee('Paket tur berhasil dihapus.');

    $this->assertDatabaseMissing('tour_packages', ['id' => $createdPkg->id]);
});

test('manage tour pricing tier and calculate tour price estimate work accurately', function () {
    // 1. Create Silver Tier with 2 brackets
    $silverResponse = BaharsyahServer::tool(ManageTourPricingTierTool::class, [
        'action' => 'upsert',
        'tour_package_id' => $this->package->id,
        'name' => ['id' => 'Silver Cabin', 'en' => 'Silver Cabin'],
        'hotel_stars' => 3,
        'price_tiers' => [
            ['min_pax' => 2, 'max_pax' => 4, 'price' => 4500000, 'currency' => 'IDR'],
            ['min_pax' => 5, 'max_pax' => 10, 'price' => 3800000, 'currency' => 'IDR'],
        ],
    ]);

    $silverResponse->assertSee('Tier harga paket tur berhasil disimpan.')
        ->assertSee('Silver Cabin')
        ->assertSee('4500000');

    // 2. Create Gold Tier
    BaharsyahServer::tool(ManageTourPricingTierTool::class, [
        'action' => 'upsert',
        'tour_package_id' => $this->package->id,
        'name' => ['id' => 'Gold Master Cabin'],
        'hotel_stars' => 5,
        'price_tiers' => [
            ['min_pax' => 2, 'max_pax' => 6, 'price' => 6500000, 'currency' => 'IDR'],
        ],
    ])->assertSee('Gold Master Cabin');

    // 3. Calculate price for 6 pax (Silver should use 3,800,000 x 6 = 22,800,000; Gold should use 6,500,000 x 6 = 39,000,000)
    BaharsyahServer::tool(CalculateTourPriceTool::class, [
        'tour_package_id' => $this->package->id,
        'pax' => 6,
    ])->assertSee('Silver Cabin')
        ->assertSee('3800000')
        ->assertSee('22800000')
        ->assertSee('Gold Master Cabin')
        ->assertSee('39000000');
});

test('manage tour itinerary supports upsert, sync, and delete', function () {
    // Upsert Day 1
    BaharsyahServer::tool(ManageTourItineraryTool::class, [
        'action' => 'upsert',
        'tour_package_id' => $this->package->id,
        'day_number' => 1,
        'title' => ['id' => 'Penjemputan Bandara & Pulau Kelor'],
        'description' => 'Snorkeling di Pulau Kelor dan Manjarite.',
        'destination_ids' => [$this->destinationPadar->id],
    ])->assertSee('Itinerary hari ke-1 berhasil disimpan.')
        ->assertSee('Pulau Padar');

    // Sync multiple days
    BaharsyahServer::tool(ManageTourItineraryTool::class, [
        'action' => 'sync',
        'tour_package_id' => $this->package->id,
        'itineraries' => [
            [
                'day_number' => 1,
                'title' => 'Hari 1: Bandara - Pulau Rinca',
                'description' => 'Trekking melihat komodo.',
            ],
            [
                'day_number' => 2,
                'title' => 'Hari 2: Sunrise Pulau Padar & Pink Beach',
                'description' => 'Mendaki puncak Pulau Padar saat matahari terbit.',
                'destination_ids' => [$this->destinationPadar->id],
            ],
        ],
    ])->assertSee('Seluruh jadwal itinerary paket tur berhasil disinkronisasi.')
        ->assertSee('Sunrise Pulau Padar');

    expect($this->package->itineraries()->count())->toBe(2);

    // Delete Day 2
    BaharsyahServer::tool(ManageTourItineraryTool::class, [
        'action' => 'delete',
        'tour_package_id' => $this->package->id,
        'day_number' => 2,
    ])->assertSee('Itinerary hari ke-2 berhasil dihapus.');

    expect($this->package->itineraries()->count())->toBe(1);
});

test('manage tour includes supports upsert, sync, and delete', function () {
    // Sync items
    BaharsyahServer::tool(ManageTourIncludesTool::class, [
        'action' => 'sync',
        'tour_package_id' => $this->package->id,
        'items' => [
            ['type' => 'include', 'item' => 'Kapal Phinisi AC Selama 3H2M', 'sort_order' => 1],
            ['type' => 'exclude', 'item' => 'Tiket Pesawat menuju Labuan Bajo', 'sort_order' => 2],
            ['type' => 'note', 'item' => 'Wajib membawa kartu identitas KTP/Paspor', 'sort_order' => 3],
        ],
    ])->assertSee('Daftar fasilitas paket tur berhasil disinkronisasi.')
        ->assertSee('Kapal Phinisi AC Selama 3H2M');

    expect($this->package->includes()->count())->toBe(3);

    $firstInclude = $this->package->includes()->first();

    // Delete single item
    BaharsyahServer::tool(ManageTourIncludesTool::class, [
        'action' => 'delete',
        'tour_package_id' => $this->package->id,
        'include_id' => $firstInclude->id,
    ])->assertSee('Item fasilitas/layanan paket tur berhasil dihapus.');

    expect($this->package->includes()->count())->toBe(2);
});

test('tour package tools and manage_* tools enforce granular oauth scopes', function () {
    $user = User::factory()->create();
    $readToken = $user->createToken('Read Token', ['mcp:read'])->accessToken;
    $writeToken = $user->createToken('Write Token', ['mcp:write'])->accessToken;

    // calculate_tour_price allowed with mcp:read
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => [
            'name' => 'calculate_tour_price',
            'arguments' => ['tour_package_id' => $this->package->id, 'pax' => 2],
        ],
    ], ['Authorization' => "Bearer {$readToken}"])->assertOk();

    // manage_tour_includes forbidden with mcp:read, allowed with mcp:write
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/call',
        'params' => [
            'name' => 'manage_tour_includes',
            'arguments' => [
                'action' => 'upsert',
                'tour_package_id' => $this->package->id,
                'type' => 'include',
                'item' => 'Dokumentasi Drone & GoPro',
            ],
        ],
    ], ['Authorization' => "Bearer {$readToken}"])->assertStatus(403);

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'tools/call',
        'params' => [
            'name' => 'manage_tour_includes',
            'arguments' => [
                'action' => 'upsert',
                'tour_package_id' => $this->package->id,
                'type' => 'include',
                'item' => 'Dokumentasi Drone & GoPro',
            ],
        ],
    ], ['Authorization' => "Bearer {$writeToken}"])->assertOk();
});
