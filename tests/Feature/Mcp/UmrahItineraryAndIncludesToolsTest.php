<?php

use App\Enums\UmrahItemType;
use App\Enums\UmrahPackageType;
use App\Enums\UmrahRoomType;
use App\Mcp\Prompts\ConsultUmrahPackagePrompt;
use App\Mcp\Prompts\DraftUmrahQuotationPrompt;
use App\Mcp\Resources\UmrahCatalogResource;
use App\Mcp\Resources\UmrahDeparturesScheduleResource;
use App\Mcp\Servers\BaharsyahServer;
use App\Mcp\Tools\Umrah\ManageUmrahIncludesTool;
use App\Mcp\Tools\Umrah\ManageUmrahItineraryTool;
use App\Models\UmrahDeparture;
use App\Models\UmrahInclude;
use App\Models\UmrahPackage;
use App\Models\UmrahPackageItinerary;
use App\Models\UmrahPackagePrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\ClientRepository;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-static-agent-token-umrah-itinerary');

    $clientRepository = app(ClientRepository::class);
    $clientRepository->createPersonalAccessGrantClient('Test Personal Client', 'users');

    $this->package = UmrahPackage::create([
        'name' => [
            'id' => 'Paket Umrah Syawal 10 Hari',
            'en' => 'Shawwal Umrah Package 10 Days',
        ],
        'slug' => [
            'id' => 'paket-umrah-syawal-10-hari',
            'en' => 'shawwal-umrah-package-10-days',
        ],
        'package_type' => UmrahPackageType::Regular,
        'duration_days' => 10,
        'duration_nights' => 9,
        'price_idr' => 28500000,
        'hotel_makkah' => 'Swissotel Makkah',
        'hotel_madinah' => 'Al Aqeeq Madinah',
        'airline' => 'Garuda Indonesia',
        'departure_city' => 'Jakarta (CGK)',
        'description' => ['id' => '<p>Keberkahan ibadah umrah di bulan Syawal.</p>'],
        'is_active' => true,
        'is_featured' => true,
    ]);

    UmrahPackagePrice::create([
        'umrah_package_id' => $this->package->id,
        'room_type' => UmrahRoomType::Quad,
        'price_idr' => 28500000,
    ]);

    $this->departure = UmrahDeparture::create([
        'package_id' => $this->package->id,
        'departure_date' => now()->addDays(30)->toDateString(),
        'return_date' => now()->addDays(39)->toDateString(),
        'quota_total' => 45,
        'quota_booked' => 20,
        'status' => 'open',
    ]);
});

test('manage umrah itinerary handles sync, add, and delete actions', function () {
    // 1. Sync entire itinerary
    $syncResponse = BaharsyahServer::tool(ManageUmrahItineraryTool::class, [
        'action' => 'sync',
        'umrah_package_id' => $this->package->id,
        'items' => [
            [
                'day_number' => 1,
                'title' => 'Keberangkatan Jakarta - Jeddah - Madinah',
                'description' => 'Berkumpul di Bandara Soekarno-Hatta Terminal 3.',
            ],
            [
                'day_number' => 2,
                'title' => 'Ziarah Masjid Nabawi & Raudhah',
                'description' => 'Memperbanyak ibadah di Raudhah dan makam Rasulullah SAW.',
            ],
        ],
    ]);

    $syncResponse->assertSee('Itinerary umrah berhasil disinkronkan.')
        ->assertSee('Raudhah')
        ->assertSee('Bandara Soekarno-Hatta');

    expect(UmrahPackageItinerary::where('umrah_package_id', $this->package->id)->count())->toBe(2);

    // 2. Add single itinerary day
    $addResponse = BaharsyahServer::tool(ManageUmrahItineraryTool::class, [
        'action' => 'add',
        'umrah_package_id' => $this->package->id,
        'day_number' => 3,
        'title' => 'Ziarah Kota Madinah',
        'description' => 'Mengunjungi Masjid Quba, Jabal Uhud, dan Kebun Kurma.',
    ]);

    $addResponse->assertSee('Itinerary hari ke-3 berhasil disimpan.')
        ->assertSee('Masjid Quba');

    $itemDay3 = UmrahPackageItinerary::where('umrah_package_id', $this->package->id)
        ->where('day_number', 3)
        ->firstOrFail();

    // 3. Delete single itinerary item
    $deleteResponse = BaharsyahServer::tool(ManageUmrahItineraryTool::class, [
        'action' => 'delete',
        'id' => $itemDay3->id,
    ]);

    $deleteResponse->assertSee('Item itinerary umrah hari ke-3 berhasil dihapus.');
    $this->assertDatabaseMissing('umrah_package_itineraries', ['id' => $itemDay3->id]);
});

test('manage umrah includes handles sync, add, and delete actions for different types', function () {
    // 1. Sync include items
    $syncResponse = BaharsyahServer::tool(ManageUmrahIncludesTool::class, [
        'action' => 'sync',
        'umrah_package_id' => $this->package->id,
        'type' => 'include',
        'items' => [
            'Tiket pesawat PP Jakarta - Madinah / Jeddah - Jakarta',
            'Akomodasi hotel Makkah dan Madinah sesuai paket',
            'Makan 3 kali sehari menu Indonesia',
        ],
    ]);

    $syncResponse->assertSee('Daftar fasilitas/ketentuan umrah berhasil disinkronkan.')
        ->assertSee('Tiket pesawat PP')
        ->assertSee('Akomodasi hotel');

    expect(UmrahInclude::where('package_id', $this->package->id)->where('type', UmrahItemType::Include)->count())->toBe(3);

    // 2. Add requirement item
    $addResponse = BaharsyahServer::tool(ManageUmrahIncludesTool::class, [
        'action' => 'add',
        'umrah_package_id' => $this->package->id,
        'type' => 'requirement',
        'description' => 'Paspor asli masih berlaku minimal 7 bulan sebelum tanggal berangkat',
    ]);

    $addResponse->assertSee('Item fasilitas/persyaratan umrah berhasil')
        ->assertSee('Paspor asli');

    $reqItem = UmrahInclude::where('package_id', $this->package->id)
        ->where('type', UmrahItemType::Requirement)
        ->firstOrFail();

    // 3. Delete item
    $deleteResponse = BaharsyahServer::tool(ManageUmrahIncludesTool::class, [
        'action' => 'delete',
        'id' => $reqItem->id,
    ]);

    $deleteResponse->assertSee('Item fasilitas/persyaratan umrah berhasil dihapus.');
    $this->assertDatabaseMissing('umrah_includes', ['id' => $reqItem->id]);
});

test('umrah catalog and departures resources return complete data', function () {
    BaharsyahServer::resource(UmrahCatalogResource::class)
        ->assertSee('umrah://catalog')
        ->assertSee('Paket Umrah Syawal 10 Hari')
        ->assertSee('Swissotel Makkah')
        ->assertSee('28500000');

    BaharsyahServer::resource(UmrahDeparturesScheduleResource::class)
        ->assertSee('umrah://departures')
        ->assertSee('Paket Umrah Syawal 10 Hari')
        ->assertSee('Garuda Indonesia')
        ->assertSee('open');
});

test('consult and draft umrah prompts produce guided contexts', function () {
    BaharsyahServer::prompt(ConsultUmrahPackagePrompt::class, [
        'budget_per_person' => 30000000,
        'room_type' => 'quad',
        'departure_city' => 'Jakarta',
    ])
        ->assertSee('Senior Umrah Consultant & Pembimbing Ibadah')
        ->assertSee('30.000.000')
        ->assertSee('quad')
        ->assertSee('Swissotel Makkah');

    BaharsyahServer::prompt(DraftUmrahQuotationPrompt::class, [
        'package_id' => $this->package->id,
        'departure_id' => $this->departure->id,
        'client_name' => 'Keluarga Bpk. Rahmat',
        'room_type' => 'quad',
        'pax' => 4,
    ])
        ->assertSee('Senior Umrah Quotation Specialist')
        ->assertSee('Keluarga Bpk. Rahmat')
        ->assertSee('Paket Umrah Syawal 10 Hari')
        ->assertSee('Garuda Indonesia')
        ->assertSee('Swissotel Makkah');
});

test('itinerary and includes tools enforce oauth write scope', function () {
    $user = User::factory()->create();
    $readToken = $user->createToken('Read Token', ['mcp:read'])->accessToken;
    $writeToken = $user->createToken('Write Token', ['mcp:write'])->accessToken;

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => [
            'name' => 'manage_umrah_itinerary',
            'arguments' => [
                'action' => 'add',
                'umrah_package_id' => $this->package->id,
                'day_number' => 1,
                'title' => 'Hari Pertama',
            ],
        ],
    ], ['Authorization' => "Bearer {$readToken}"])->assertStatus(403);

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/call',
        'params' => [
            'name' => 'manage_umrah_itinerary',
            'arguments' => [
                'action' => 'add',
                'umrah_package_id' => $this->package->id,
                'day_number' => 1,
                'title' => 'Hari Pertama',
            ],
        ],
    ], ['Authorization' => "Bearer {$writeToken}"])->assertOk();
});
