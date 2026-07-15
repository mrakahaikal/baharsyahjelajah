<?php

use App\Filament\Resources\Vehicles\VehicleResource;
use App\Livewire\VehicleCatalog;
use App\Models\User;
use App\Models\Vehicle;
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

    get('/id/transport')
        ->assertSuccessful()
        ->assertSee('Armada yang tepat untuk setiap perjalanan')
        ->assertSee($smallVehicle->name)
        ->assertSee($largeVehicle->name)
        ->assertDontSee('Armada Nonaktif');

    Livewire::test(VehicleCatalog::class)
        ->set('capacity', '10')
        ->assertSee($largeVehicle->name)
        ->assertDontSee($smallVehicle->name)
        ->set('transmission', 'automatic')
        ->assertSee('Belum ada armada yang sesuai');
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
    $settings = app(GeneralSettings::class);
    $settings->whatsapp_number = '+6281234567890';
    $settings->save();

    get(route('transport.booking', [
        'locale' => 'id',
        'vehicle' => $vehicle->slug,
        'rate' => 'daily',
        'pax' => 4,
    ]))
        ->assertSuccessful()
        ->assertSee('Lengkapi detail perjalanan')
        ->assertSee('wire:id=', false);
});
