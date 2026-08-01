<?php

use App\Filament\Resources\Vehicles\VehicleResource;
use App\Livewire\VehicleCatalog;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleRentalArea;
use App\Models\VehicleRentalRate;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('renders the active vehicle catalog and filters it interactively', function () {
    $smallVehicle = Vehicle::factory()->create([
        'name' => ['id' => 'Innova Keluarga', 'en' => 'Family Innova', 'ms' => 'Innova Keluarga'],
        'slug' => ['id' => 'innova-keluarga', 'en' => 'family-innova', 'ms' => 'innova-keluarga'],
        'capacity_pax' => 6,
        'transmission' => 'automatic',
    ]);
    $largeVehicle = Vehicle::factory()->create([
        'name' => ['id' => 'HiAce Rombongan', 'en' => 'Group HiAce', 'ms' => 'HiAce Kumpulan'],
        'slug' => ['id' => 'hiace-rombongan', 'en' => 'group-hiace', 'ms' => 'hiace-kumpulan'],
        'capacity_pax' => 14,
        'transmission' => 'manual',
    ]);
    Vehicle::factory()->create([
        'name' => ['id' => 'Armada Nonaktif', 'en' => 'Inactive Vehicle', 'ms' => 'Armada Tidak Aktif'],
        'is_active' => false,
    ]);
    $area = VehicleRentalArea::factory()->create(['slug' => 'jakarta']);
    VehicleRentalRate::factory()->for($smallVehicle)->for($area, 'area')->create();
    VehicleRentalRate::factory()->for($largeVehicle)->for($area, 'area')->create();

    get('/id/transport?area=jakarta')
        ->assertSuccessful()
        ->assertSee('Armada yang tepat untuk setiap perjalanan')
        ->assertSee($smallVehicle->name)
        ->assertSee($largeVehicle->name)
        ->assertDontSee('Armada Nonaktif');

    Livewire::test(VehicleCatalog::class)
        ->set('area', 'jakarta')
        ->set('capacity', '10')
        ->assertSee($largeVehicle->name)
        ->assertDontSee($smallVehicle->name)
        ->set('category', 'car')
        ->assertSee('Belum ada armada yang sesuai');
});

it('shows a single all-area catalog with the current starting price', function () {
    $vehicle = Vehicle::factory()->create([
        'name' => ['id' => 'HiAce Antar Kota', 'en' => 'Intercity HiAce', 'ms' => 'HiAce Antara Bandar'],
        'slug' => ['id' => 'hiace-antar-kota', 'en' => 'intercity-hiace', 'ms' => 'hiace-antara-bandar'],
        'capacity_pax' => 14,
    ]);
    $otherVehicle = Vehicle::factory()->create([
        'name' => ['id' => 'Innova Jakarta', 'en' => 'Jakarta Innova', 'ms' => 'Innova Jakarta'],
        'capacity_pax' => 6,
    ]);
    $expiredVehicle = Vehicle::factory()->create([
        'name' => ['id' => 'Armada Kadaluarsa', 'en' => 'Expired Vehicle', 'ms' => 'Armada Tamat'],
    ]);
    $jakarta = VehicleRentalArea::factory()->create(['slug' => 'jakarta', 'minimum_rental_days' => 1]);
    $bandung = VehicleRentalArea::factory()->create(['slug' => 'bandung', 'minimum_rental_days' => 3]);

    VehicleRentalRate::factory()->for($vehicle)->for($jakarta, 'area')->create(['price_per_day_idr' => 900000]);
    VehicleRentalRate::factory()->for($vehicle)->for($bandung, 'area')->create(['price_per_day_idr' => 700000]);
    VehicleRentalRate::factory()->for($otherVehicle)->for($jakarta, 'area')->create(['price_per_day_idr' => 800000]);
    VehicleRentalRate::factory()->for($expiredVehicle)->for($jakarta, 'area')->create([
        'valid_from' => today()->subMonth(),
        'valid_until' => today()->subDay(),
    ]);

    get('/id/transport')
        ->assertSuccessful()
        ->assertSee($vehicle->name)
        ->assertSee($otherVehicle->name)
        ->assertDontSee($expiredVehicle->name)
        ->assertSee('Rp 700.000')
        ->assertSee('Tersedia di 2 wilayah');

    Livewire::test(VehicleCatalog::class)
        ->set('area', 'bandung')
        ->assertSee($vehicle->name)
        ->assertDontSee($otherVehicle->name)
        ->assertSee('Rp 700.000')
        ->assertSee('Minimum 3 hari')
        ->call('resetFilters')
        ->assertSee($vehicle->name)
        ->assertSee($otherVehicle->name);
});

it('shows every effective regional rate until an area is selected', function () {
    $vehicle = Vehicle::factory()->create([
        'name' => ['id' => 'HiAce Wisata', 'en' => 'Touring HiAce', 'ms' => 'HiAce Lawatan'],
        'slug' => ['id' => 'hiace-wisata', 'en' => 'touring-hiace', 'ms' => 'hiace-lawatan'],
    ]);
    $jakarta = VehicleRentalArea::factory()->create(['slug' => 'jakarta', 'minimum_rental_days' => 1]);
    $malang = VehicleRentalArea::factory()->create(['slug' => 'malang', 'minimum_rental_days' => 5]);
    VehicleRentalRate::factory()->for($vehicle)->for($jakarta, 'area')->create(['price_per_day_idr' => 850000]);
    VehicleRentalRate::factory()->for($vehicle)->for($malang, 'area')->create(['price_per_day_idr' => 1250000]);

    get('/id/transport/hiace-wisata')
        ->assertSuccessful()
        ->assertSee($jakarta->name)
        ->assertSee($malang->name)
        ->assertSee('Rp 850.000')
        ->assertSee('Rp 1.250.000')
        ->assertSee(route('transport.booking', ['locale' => 'id', 'vehicle' => 'hiace-wisata']), false);

    get('/id/transport/hiace-wisata?area=malang')
        ->assertSuccessful()
        ->assertSee('Minimum 5 hari')
        ->assertSee(route('transport.booking', ['locale' => 'id', 'vehicle' => 'hiace-wisata', 'area' => 'malang']), false);
});

it('renders localized details and normalizes legacy slugs', function () {
    $vehicle = Vehicle::factory()->create([
        'name' => ['id' => 'Toyota Alphard', 'en' => 'Toyota Alphard', 'ms' => 'Toyota Alphard'],
        'slug' => ['id' => 'toyota-alphard', 'en' => 'toyota-alphard-en', 'ms' => 'toyota-alphard-ms'],
    ]);

    get('/en/transport/toyota-alphard')
        ->assertRedirect('/en/transport/toyota-alphard-en')
        ->assertStatus(301);

    get('/en/transport/toyota-alphard-en')
        ->assertSuccessful()
        ->assertSee('Professional driver included')
        ->assertSee(route('transport.booking', ['locale' => 'en', 'vehicle' => 'toyota-alphard-en']), false);

    $vehicle->update(['is_active' => false]);
    get('/id/transport/toyota-alphard')->assertNotFound();
});

it('uses the Indonesian vehicle slug when localized slugs are missing', function () {
    $vehicle = Vehicle::factory()->create();
    $vehicle->forgetTranslations('slug')
        ->setTranslation('slug', 'id', 'hiace-indonesia')
        ->save();

    get('/ms/transport/hiace-indonesia')
        ->assertSuccessful()
        ->assertSee('<link rel="alternate" hreflang="en" href="'.route('transport.show', [
            'locale' => 'en',
            'vehicle' => 'hiace-indonesia',
        ]).'">', false);

    get('/en/transport/hiace-indonesia/booking')->assertSuccessful();
});

it('redirects unlocalized transport paths and preserves their query strings', function () {
    get('/transport')->assertRedirect('/id/transport');
    get('/transport/hiace/booking?rate=daily&pax=8')
        ->assertRedirect('/id/transport/hiace/booking?pax=8&rate=daily');
});

it('shows the vehicle resource in the admin panel', function () {
    actingAs(User::factory()->create(['email' => 'vehicle-admin@baharsyahjelajah.com']));

    get(VehicleResource::getUrl('index'))->assertSuccessful();
    get(VehicleResource::getUrl('create'))->assertSuccessful();
});

it('renders a dedicated booking page', function () {
    $vehicle = Vehicle::factory()->create();
    $area = VehicleRentalArea::factory()->create(['slug' => 'jakarta']);
    VehicleRentalRate::factory()->for($vehicle)->for($area, 'area')->create();
    $settings = app(GeneralSettings::class);
    $settings->whatsapp_number = '+6281234567890';
    $settings->save();

    get(route('transport.booking', [
        'locale' => 'id',
        'vehicle' => $vehicle->slug,
        'area' => 'jakarta',
        'pax' => 4,
    ]))
        ->assertSuccessful()
        ->assertSee('Lengkapi detail perjalanan')
        ->assertSee('wire:id=', false);
});
