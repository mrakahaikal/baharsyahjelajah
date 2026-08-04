<?php

use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\UmrahPackage;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createTestCategory(): TourCategory
{
    return TourCategory::create([
        'name' => ['en' => 'Nature Tour', 'id' => 'Wisata Alam', 'ms' => 'Pelancongan Alam'],
        'slug' => ['en' => 'nature-tour-'.rand(), 'id' => 'wisata-alam-'.rand(), 'ms' => 'pelancongan-alam-'.rand()],
        'icon' => 'heroicon-o-sparkles',
        'sort_order' => rand(1, 999999),
    ]);
}

function createTestTour(): Tour
{
    $category = createTestCategory();

    return Tour::create([
        'tour_category_id' => $category->id,
        'name' => ['id' => 'Pesona Bali Island Tour', 'en' => 'Pesona Bali Island Tour', 'ms' => 'Pesona Bali Island Tour'],
        'slug' => ['id' => 'pesona-bali-island-tour', 'en' => 'pesona-bali-island-tour', 'ms' => 'pesona-bali-island-tour'],
        'short_description' => ['id' => 'Deskripsi', 'en' => 'Description', 'ms' => 'Description'],
        'description' => ['id' => 'Deskripsi', 'en' => 'Description', 'ms' => 'Description'],
        'tour_type' => 'domestic',
        'currency' => 'IDR',
        'is_active' => true,
    ]);
}

it('can attach vehicles to tour package and umrah package', function () {
    $vehicle = Vehicle::factory()->create([
        'name' => ['id' => 'Toyota HiAce Commuter'],
        'slug' => 'toyota-hiace-commuter',
        'catalog_code' => 'VHC-HIACE-01',
    ]);

    $tour = createTestTour();
    $tourPackage = $tour->packages()->create([
        'name' => ['id' => 'Paket Bali 4D3N', 'en' => 'Bali Package 4D3N'],
        'slug' => ['id' => 'paket-bali-4d3n', 'en' => 'bali-package-4d3n'],
        'duration_days' => 4,
        'duration_nights' => 3,
    ]);
    $umrahPackage = UmrahPackage::factory()->create();

    $tourPackage->vehicles()->attach($vehicle);
    $umrahPackage->vehicles()->attach($vehicle);

    expect($tourPackage->fresh()->vehicles)->toHaveCount(1)
        ->and($tourPackage->fresh()->vehicles->first()->id)->toBe($vehicle->id);

    expect($umrahPackage->fresh()->vehicles)->toHaveCount(1)
        ->and($umrahPackage->fresh()->vehicles->first()->id)->toBe($vehicle->id);

    expect($vehicle->fresh()->tourPackages)->toHaveCount(1);
    expect($vehicle->fresh()->umrahPackages)->toHaveCount(1);
});

it('detaches vehicles when tour package or umrah package is deleted', function () {
    $vehicle = Vehicle::factory()->create([
        'catalog_code' => 'VHC-ALPHARD-01',
    ]);

    $tour = createTestTour();
    $tourPackage = $tour->packages()->create([
        'name' => ['id' => 'Paket Bali 4D3N', 'en' => 'Bali Package 4D3N'],
        'slug' => ['id' => 'paket-bali-4d3n', 'en' => 'bali-package-4d3n'],
        'duration_days' => 4,
        'duration_nights' => 3,
    ]);
    $umrahPackage = UmrahPackage::factory()->create();

    $tourPackage->vehicles()->attach($vehicle);
    $umrahPackage->vehicles()->attach($vehicle);

    $tourPackage->delete();
    expect($vehicle->fresh()->tourPackages)->toHaveCount(0);

    $umrahPackage->forceDelete();
    expect($vehicle->fresh()->umrahPackages)->toHaveCount(0);
});

it('renders associated vehicles on umrah package show page', function () {
    $vehicle = Vehicle::factory()->create([
        'name' => ['id' => 'GMC Executive Bus', 'en' => 'GMC Executive Bus'],
        'slug' => 'gmc-executive-bus',
        'brand' => 'GMC',
        'model' => 'Luxury',
        'is_active' => true,
    ]);

    $package = UmrahPackage::factory()->create([
        'name' => ['id' => 'Umrah Plus Turki Luxury', 'en' => 'Umrah Plus Turkey Luxury', 'ms' => 'Umrah Plus Turki Luxury'],
        'slug' => ['id' => 'umrah-plus-turki-luxury', 'en' => 'umrah-plus-turkey-luxury', 'ms' => 'umrah-plus-turki-luxury'],
        'is_active' => true,
    ]);

    $package->vehicles()->attach($vehicle);

    $response = $this->get('/id/umroh/umrah-plus-turki-luxury');

    $response->assertStatus(200)
        ->assertSee('Armada')
        ->assertSee('GMC Executive Bus');
});

it('renders associated vehicles on tour package show page', function () {
    $vehicle = Vehicle::factory()->create([
        'name' => ['id' => 'Hyundai Staria VIP'],
        'slug' => 'hyundai-staria-vip',
        'brand' => 'Hyundai',
        'model' => 'Staria',
        'is_active' => true,
    ]);

    $tour = createTestTour();
    $tourPackage = $tour->packages()->create([
        'name' => ['id' => 'Paket Bali 4D3N', 'en' => 'Bali Package 4D3N'],
        'slug' => ['id' => 'paket-bali-4d3n', 'en' => 'bali-package-4d3n'],
        'duration_days' => 4,
        'duration_nights' => 3,
    ]);

    $tourPackage->vehicles()->attach($vehicle);

    $response = $this->get('/id/tour/pesona-bali-island-tour/package/paket-bali-4d3n');

    $response->assertStatus(200)
        ->assertSee('Armada')
        ->assertSee('Hyundai Staria VIP');
});
