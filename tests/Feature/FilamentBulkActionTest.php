<?php

use App\Filament\Resources\Tours\Pages\ListTours;
use App\Filament\Resources\UmrahPackages\Pages\ListUmrahPackages;
use App\Filament\Resources\Vehicles\Pages\ListVehicles;
use App\Models\Country;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\UmrahPackage;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

function createBulkTestCategory(): TourCategory
{
    return TourCategory::create([
        'name' => ['en' => 'Nature Tour', 'id' => 'Wisata Alam', 'ms' => 'Pelancongan Alam'],
        'slug' => ['en' => 'nature-tour-bulk-'.rand(), 'id' => 'wisata-alam-bulk-'.rand(), 'ms' => 'pelancongan-alam-bulk-'.rand()],
        'icon' => 'heroicon-o-sparkles',
        'sort_order' => rand(1, 999999),
    ]);
}

function createBulkTestTour(string $name): Tour
{
    $category = createBulkTestCategory();

    return Tour::create([
        'tour_category_id' => $category->id,
        'name' => ['id' => $name, 'en' => $name, 'ms' => $name],
        'slug' => ['id' => Str::slug($name), 'en' => Str::slug($name)],
        'short_description' => ['id' => 'Deskripsi', 'en' => 'Description'],
        'description' => ['id' => 'Deskripsi', 'en' => 'Description'],
        'tour_type' => 'domestic',
        'currency' => 'IDR',
        'is_active' => true,
    ]);
}

it('allows bulk attaching countries to tours in Filament table', function () {
    $tour1 = createBulkTestTour('Tour 1');
    $tour2 = createBulkTestTour('Tour 2');

    $country = Country::factory()->create([
        'name' => ['id' => 'Jepang', 'en' => 'Japan', 'ms' => 'Jepun'],
        'slug' => 'japan',
    ]);

    Livewire::test(ListTours::class)
        ->callTableBulkAction('attachCountries', [$tour1, $tour2], [
            'countries' => [$country->id],
        ])
        ->assertHasNoTableBulkActionErrors();

    expect($tour1->fresh()->countries)->toHaveCount(1);
    expect($tour2->fresh()->countries)->toHaveCount(1);
});

it('allows bulk attaching vehicles to umrah packages in Filament table', function () {
    $umrah1 = UmrahPackage::factory()->create();
    $umrah2 = UmrahPackage::factory()->create();

    $vehicle = Vehicle::factory()->create([
        'catalog_code' => 'VHC-BUS-01',
    ]);

    Livewire::test(ListUmrahPackages::class)
        ->callTableBulkAction('attachVehicles', [$umrah1, $umrah2], [
            'vehicles' => [$vehicle->id],
        ])
        ->assertHasNoTableBulkActionErrors();

    expect($umrah1->fresh()->vehicles)->toHaveCount(1);
    expect($umrah2->fresh()->vehicles)->toHaveCount(1);
});

it('allows bulk attaching tours and umrah packages to vehicles in Filament table', function () {
    $vehicle1 = Vehicle::factory()->create(['catalog_code' => 'VHC-V1']);
    $vehicle2 = Vehicle::factory()->create(['catalog_code' => 'VHC-V2']);

    $tour = createBulkTestTour('Tour Wisata');
    $umrah = UmrahPackage::factory()->create();

    Livewire::test(ListVehicles::class)
        ->callTableBulkAction('attachTours', [$vehicle1, $vehicle2], [
            'tours' => [$tour->id],
        ])
        ->callTableBulkAction('attachUmrahPackages', [$vehicle1, $vehicle2], [
            'umrahPackages' => [$umrah->id],
        ])
        ->assertHasNoTableBulkActionErrors();

    expect($vehicle1->fresh()->tours)->toHaveCount(1);
    expect($vehicle2->fresh()->tours)->toHaveCount(1);
    expect($vehicle1->fresh()->umrahPackages)->toHaveCount(1);
    expect($vehicle2->fresh()->umrahPackages)->toHaveCount(1);
});
