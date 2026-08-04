<?php

use App\Enums\TourType;
use App\Models\Country;
use App\Models\Destination;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\TourPackage;
use App\Models\UmrahPackage;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows attaching countries to tour packages, umrah packages, destinations, vehicles, and posts', function () {
    $indonesia = Country::factory()->create([
        'name' => ['id' => 'Indonesia', 'en' => 'Indonesia', 'ms' => 'Indonesia'],
        'slug' => 'indonesia',
        'iso_alpha_2' => 'ID',
        'iso_alpha_3' => 'IDN',
    ]);

    $saudi = Country::factory()->create([
        'name' => ['id' => 'Arab Saudi', 'en' => 'Saudi Arabia', 'ms' => 'Arab Saudi'],
        'slug' => 'arab-saudi',
        'iso_alpha_2' => 'SA',
        'iso_alpha_3' => 'SAU',
    ]);

    // 1. Tour & TourPackage
    $category = TourCategory::query()->create([
        'name' => ['id' => 'Tur Alam'],
        'slug' => ['id' => 'tur-alam'],
    ]);

    $tour = Tour::query()->create([
        'tour_category_id' => $category->id,
        'name' => ['id' => 'Tour Borobudur'],
        'slug' => ['id' => 'tour-borobudur'],
        'tour_type' => TourType::Domestic,
    ]);

    $tourPackage = TourPackage::query()->create([
        'tour_id' => $tour->id,
        'name' => ['id' => 'Paket Borobudur 3D2N'],
        'slug' => ['id' => 'paket-borobudur-3d2n'],
        'duration_days' => 3,
        'duration_nights' => 2,
    ]);

    $tour->countries()->attach([$indonesia->id]);
    $tourPackage->countries()->attach([$indonesia->id]);

    // 2. UmrahPackage
    $umrahPackage = UmrahPackage::factory()->create();
    $umrahPackage->countries()->attach([$saudi->id]);

    // 3. Destination
    $destination = Destination::factory()->create();
    $destination->countries()->attach([$indonesia->id]);

    // 4. Vehicle
    $vehicle = Vehicle::factory()->create();
    $vehicle->countries()->attach([$indonesia->id, $saudi->id]);

    // 5. Post
    $user = User::factory()->create();
    $postCategory = PostCategory::query()->create([
        'name' => ['id' => 'Panduan Perjalanan'],
        'slug' => ['id' => 'panduan-perjalanan'],
    ]);

    $post = Post::query()->create([
        'post_category_id' => $postCategory->id,
        'user_id' => $user->id,
        'title' => ['id' => 'Panduan Wisata Borobudur'],
        'slug' => ['id' => 'panduan-wisata-borobudur'],
        'content' => ['id' => 'Detail artikel mengenai wisata Borobudur.'],
        'status' => 'published',
    ]);

    $post->countries()->attach([$indonesia->id]);

    // Assert Forward Relationships
    expect($tour->fresh()->countries->pluck('id'))->toContain($indonesia->id)
        ->and($tourPackage->fresh()->countries->pluck('id'))->toContain($indonesia->id)
        ->and($umrahPackage->fresh()->countries->pluck('id'))->toContain($saudi->id)
        ->and($destination->fresh()->countries->pluck('id'))->toContain($indonesia->id)
        ->and($vehicle->fresh()->countries->pluck('id'))->toContain($indonesia->id, $saudi->id)
        ->and($post->fresh()->countries->pluck('id'))->toContain($indonesia->id);

    // Assert Inverse Relationships on Country
    expect($indonesia->fresh()->tours->pluck('id'))->toContain($tour->id)
        ->and($indonesia->fresh()->tourPackages->pluck('id'))->toContain($tourPackage->id)
        ->and($saudi->fresh()->umrahPackages->pluck('id'))->toContain($umrahPackage->id)
        ->and($indonesia->fresh()->destinations->pluck('id'))->toContain($destination->id)
        ->and($indonesia->fresh()->vehicles->pluck('id'))->toContain($vehicle->id)
        ->and($saudi->fresh()->vehicles->pluck('id'))->toContain($vehicle->id)
        ->and($indonesia->fresh()->posts->pluck('id'))->toContain($post->id);
});

it('detaches country relations when model is deleted', function () {
    $country = Country::factory()->create();
    $umrahPackage = UmrahPackage::factory()->create();
    $umrahPackage->countries()->attach([$country->id]);

    expect($umrahPackage->countries()->count())->toBe(1);

    $umrahPackage->delete();

    expect($country->fresh()->umrahPackages()->count())->toBe(0);
});
