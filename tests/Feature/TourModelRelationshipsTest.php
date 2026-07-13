<?php

use App\Enums\TourType;
use App\Models\PackageTier;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\TourPackage;
use App\Models\TourPriceTier;
use Database\Seeders\TourCategorySeeder;
use Database\Seeders\TourSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Downloaders\Downloader;

class FakeTourMediaDownloader implements Downloader
{
    public function getTempFile(string $url): string
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'tour-media-');
        file_put_contents($temporaryFile, 'fake image for '.$url);

        return $temporaryFile;
    }
}

uses(RefreshDatabase::class);

it('models the tour package hierarchy and resolves a price for the requested pax', function () {
    $category = TourCategory::create([
        'name' => ['id' => 'Alam'],
        'slug' => ['id' => 'alam'],
        'sort_order' => 1,
    ]);

    $tour = Tour::create([
        'tour_category_id' => $category->id,
        'name' => ['id' => 'Jelajah Hutan'],
        'slug' => ['id' => 'jelajah-hutan'],
        'tour_type' => TourType::Domestic,
        'currency' => 'IDR',
        'is_active' => true,
        'is_featured' => false,
    ]);

    $package = $tour->packages()->create([
        'name' => ['id' => 'Paket Tiga Hari'],
        'slug' => ['id' => 'paket-tiga-hari'],
        'duration_days' => 3,
        'duration_nights' => 2,
    ]);

    $package->itineraries()->createMany([
        ['day_number' => 2, 'title' => ['id' => 'Hari Kedua']],
        ['day_number' => 1, 'title' => ['id' => 'Hari Pertama']],
    ]);

    $package->includes()->createMany([
        ['item' => ['id' => 'Makan'], 'type' => 'include', 'sort_order' => 2],
        ['item' => ['id' => 'Transportasi'], 'type' => 'include', 'sort_order' => 1],
    ]);

    $tier = $package->tiers()->create([
        'name' => ['id' => 'Standar'],
        'hotel_stars' => 3,
    ]);

    $tier->priceTiers()->createMany([
        ['min_pax' => 1, 'max_pax' => 2, 'price' => 1500000, 'currency' => 'IDR'],
        ['min_pax' => 3, 'max_pax' => null, 'price' => 1250000, 'currency' => 'IDR'],
    ]);

    expect($tour->tour_type)->toBe(TourType::Domestic)
        ->and($tour->category->is($category))->toBeTrue()
        ->and($category->tours->first()->is($tour))->toBeTrue()
        ->and($package->tour->is($tour))->toBeTrue()
        ->and($package->duration_label)->toBe('3 Hari 2 Malam')
        ->and($package->itineraries->pluck('day_number')->all())->toBe([1, 2])
        ->and($package->includes->pluck('sort_order')->all())->toBe([1, 2])
        ->and($tier)->toBeInstanceOf(PackageTier::class)
        ->and($tier->tourPackage->is($package))->toBeTrue()
        ->and($tier->priceTierForPax(2)?->price)->toBe('1500000.00')
        ->and($tier->priceTierForPax(4)?->price)->toBe('1250000.00')
        ->and($tier->priceTierForPax(0))->toBeNull();
});

it('stores package covers and galleries with the media library', function () {
    Storage::fake('public');

    $category = TourCategory::create([
        'name' => ['id' => 'Alam'],
        'slug' => ['id' => 'alam'],
        'sort_order' => 1,
    ]);

    $tour = Tour::create([
        'tour_category_id' => $category->id,
        'name' => ['id' => 'Jelajah Hutan'],
        'slug' => ['id' => 'jelajah-hutan'],
        'tour_type' => TourType::Domestic,
        'currency' => 'IDR',
        'is_active' => true,
        'is_featured' => false,
    ]);

    $package = $tour->packages()->create([
        'name' => ['id' => 'Paket Tiga Hari'],
        'slug' => ['id' => 'paket-tiga-hari'],
        'duration_days' => 3,
        'duration_nights' => 2,
    ]);

    $package->addMedia(UploadedFile::fake()->create('cover.jpg', 10, 'image/jpeg'))
        ->toMediaCollection(TourPackage::MEDIA_COLLECTION_COVER);

    $gallery = $package
        ->addMedia(UploadedFile::fake()->create('gallery.jpg', 10, 'image/jpeg'))
        ->withCustomProperties([
            'caption' => [
                'id' => 'Pemandangan hutan',
                'en' => 'Forest view',
            ],
        ])
        ->toMediaCollection(TourPackage::MEDIA_COLLECTION_GALLERY);

    app()->setLocale('en');

    expect(Schema::hasTable('tour_galleries'))->toBeFalse()
        ->and($package->getFirstMedia(TourPackage::MEDIA_COLLECTION_COVER))->not->toBeNull()
        ->and($package->getMedia(TourPackage::MEDIA_COLLECTION_GALLERY))->toHaveCount(1)
        ->and($package->localizedMediaCaption($gallery))->toBe('Forest view');
});

it('seeds tours into packages, tiers, prices, and media collections', function () {
    Storage::fake('public');
    config()->set('media-library.media_downloader', FakeTourMediaDownloader::class);

    $this->seed([
        TourCategorySeeder::class,
        TourSeeder::class,
    ]);

    $tours = Tour::query()->with('packages.tiers.priceTiers')->get();

    expect($tours)->toHaveCount(2)
        ->and($tours->pluck('tour_type')->all())->toContain(TourType::Domestic, TourType::International)
        ->and($tours->every(fn (Tour $tour): bool => $tour->packages->count() === 1))->toBeTrue()
        ->and(TourPackage::query()->count())->toBe(2)
        ->and(PackageTier::query()->count())->toBe(2)
        ->and(TourPriceTier::query()->count())->toBe(4)
        ->and(TourPackage::query()->withCount('media')->get()->every(fn (TourPackage $package): bool => $package->media_count === 3))->toBeTrue()
        ->and(Schema::hasTable('tour_galleries'))->toBeFalse();
});
