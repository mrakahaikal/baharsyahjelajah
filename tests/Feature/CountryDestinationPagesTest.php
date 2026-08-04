<?php

use App\Models\Country;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\UmrahPackage;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders country index page with active countries', function () {
    Country::factory()->create([
        'name' => ['id' => 'Indonesia', 'en' => 'Indonesia', 'ms' => 'Indonesia'],
        'slug' => 'indonesia',
        'is_active' => true,
    ]);

    Country::factory()->create([
        'name' => ['id' => 'Arab Saudi', 'en' => 'Saudi Arabia', 'ms' => 'Arab Saudi'],
        'slug' => 'arab-saudi',
        'is_active' => true,
    ]);

    $response = $this->get('/id/negara');

    $response->assertStatus(200)
        ->assertSee('Indonesia')
        ->assertSee('Arab Saudi');
});

it('renders country show page for an active country', function () {
    $country = Country::factory()->create([
        'name' => ['id' => 'Japan', 'en' => 'Japan', 'ms' => 'Jepun'],
        'slug' => 'japan',
        'is_active' => true,
    ]);

    $response = $this->get('/id/negara/japan');

    $response->assertStatus(200)
        ->assertSee('Japan');
});

it('returns 404 for inactive country show page', function () {
    $country = Country::factory()->create([
        'name' => ['id' => 'Inactive Country'],
        'slug' => 'inactive-country',
        'is_active' => false,
    ]);

    $response = $this->get('/id/negara/inactive-country');

    $response->assertStatus(404);
});

it('redirects unlocalized /negara requests to /id/negara', function () {
    $response = $this->get('/negara');

    $response->assertRedirect('/id/negara');
});

it('renders country metadata on show page', function () {
    $country = Country::factory()->create([
        'name' => ['id' => 'Mesir', 'en' => 'Egypt', 'ms' => 'Mesir'],
        'slug' => 'mesir',
        'capital_city' => ['id' => 'Kairo', 'en' => 'Cairo'],
        'currency_code' => 'EGP',
        'language' => ['id' => 'Bahasa Arab'],
        'best_time_to_visit' => ['id' => 'Oktober - April'],
        'is_active' => true,
    ]);

    $response = $this->get('/id/negara/mesir');

    $response->assertStatus(200)
        ->assertSee('Mesir')
        ->assertSee('Kairo')
        ->assertSee('EGP')
        ->assertSee('Bahasa Arab');
});

it('renders country mega menu in header navbar', function () {
    Country::factory()->create([
        'name' => ['id' => 'Turki', 'en' => 'Turkey', 'ms' => 'Turki'],
        'slug' => 'turki',
        'is_active' => true,
        'is_featured' => true,
    ]);

    $response = $this->get('/id');

    $response->assertStatus(200)
        ->assertSee('Negara')
        ->assertSee('Turki')
        ->assertSee('id="country-mega-menu"', false);
});

it('renders associated tours, umrah packages, and vehicles on country show page', function () {
    $country = Country::factory()->create([
        'name' => ['id' => 'Jepang', 'en' => 'Japan', 'ms' => 'Jepun'],
        'slug' => 'jepang',
        'is_active' => true,
    ]);

    $tour = Tour::create([
        'tour_category_id' => TourCategory::create([
            'name' => ['id' => 'Wisata Alam', 'en' => 'Nature Tour'],
            'slug' => ['id' => 'wisata-alam-jp', 'en' => 'nature-tour-jp'],
            'icon' => 'heroicon-o-sparkles',
            'sort_order' => rand(1, 999999),
        ])->id,
        'name' => ['id' => 'Tour Sakura Jepang', 'en' => 'Japan Sakura Tour'],
        'slug' => ['id' => 'tour-sakura-jepang', 'en' => 'japan-sakura-tour'],
        'short_description' => ['id' => 'Deskripsi'],
        'description' => ['id' => 'Deskripsi'],
        'tour_type' => 'international',
        'currency' => 'IDR',
        'is_active' => true,
    ]);
    $tour->countries()->attach($country);

    $umrah = UmrahPackage::factory()->create([
        'name' => ['id' => 'Umrah Plus Sakura Jepang', 'en' => 'Umrah Plus Sakura Japan'],
        'is_active' => true,
    ]);
    $umrah->countries()->attach($country);

    $vehicle = Vehicle::factory()->create([
        'name' => ['id' => 'Toyota Coaster Japan VIP'],
        'is_active' => true,
    ]);
    $vehicle->countries()->attach($country);

    $response = $this->get('/id/negara/jepang');

    $response->assertStatus(200)
        ->assertSee('Tour Sakura Jepang')
        ->assertSee('Umrah Plus Sakura Jepang')
        ->assertSee('Toyota Coaster Japan VIP');
});
