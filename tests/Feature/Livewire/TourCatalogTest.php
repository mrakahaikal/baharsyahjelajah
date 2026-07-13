<?php

use App\Enums\TourType;
use App\Livewire\TourCatalog;
use App\Models\Tour;
use App\Models\TourCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createCatalogCategory(string $name, string $slug, int $sortOrder = 1): TourCategory
{
    return TourCategory::create([
        'name' => ['id' => $name, 'en' => $name.' EN', 'ms' => $name.' MS'],
        'slug' => ['id' => $slug, 'en' => $slug.'-en', 'ms' => $slug.'-ms'],
        'icon' => 'heroicon-o-map',
        'sort_order' => $sortOrder,
    ]);
}

function createCatalogTour(
    TourCategory $category,
    string $name,
    string $slug,
    TourType $type = TourType::Domestic,
    bool $isActive = true,
    ?string $packageName = null,
): Tour {
    $tour = Tour::create([
        'tour_category_id' => $category->id,
        'name' => ['id' => $name, 'en' => $name.' EN', 'ms' => $name.' MS'],
        'slug' => ['id' => $slug, 'en' => $slug.'-en', 'ms' => $slug.'-ms'],
        'short_description' => [
            'id' => 'Perjalanan menuju '.$name,
            'en' => 'A journey to '.$name.' EN',
            'ms' => 'Perjalanan ke '.$name.' MS',
        ],
        'description' => ['id' => 'Deskripsi '.$name, 'en' => 'Description '.$name, 'ms' => 'Penerangan '.$name],
        'tour_type' => $type,
        'currency' => 'IDR',
        'is_active' => $isActive,
        'is_featured' => false,
    ]);

    $tour->packages()->create([
        'name' => [
            'id' => $packageName ?? 'Paket '.$name,
            'en' => ($packageName ?? 'Package '.$name).' EN',
            'ms' => ($packageName ?? 'Pakej '.$name).' MS',
        ],
        'slug' => ['id' => $slug.'-package', 'en' => $slug.'-package-en', 'ms' => $slug.'-package-ms'],
        'duration_days' => 4,
        'duration_nights' => 3,
    ]);

    return $tour;
}

function tourCatalogComponent(): Testable
{
    return Livewire::test(TourCatalog::class, [
        'heroImageUrl' => 'https://example.com/tour-cover.jpg',
        'heroImageAlt' => 'Tour cover',
    ]);
}

it('searches tours and package names while excluding inactive tours', function () {
    $nature = createCatalogCategory('Wisata Alam', 'wisata-alam');
    $city = createCatalogCategory('Wisata Kota', 'wisata-kota', 2);
    createCatalogTour($nature, 'Jelajah Sungai', 'jelajah-sungai');
    createCatalogTour($city, 'Liburan Thailand', 'liburan-thailand', TourType::International, packageName: 'Bangkok Explorer');
    createCatalogTour($nature, 'Tour Tersembunyi', 'tour-tersembunyi', isActive: false);

    tourCatalogComponent()
        ->assertSee('Jelajah Sungai')
        ->assertSee('Liburan Thailand')
        ->assertDontSee('Tour Tersembunyi')
        ->set('destination', 'Sungai')
        ->assertSee('Jelajah Sungai')
        ->assertDontSee('Liburan Thailand')
        ->set('destination', 'Bangkok Explorer')
        ->assertSee('Liburan Thailand')
        ->assertDontSee('Jelajah Sungai');
});

it('combines translated category and tour type filters', function () {
    $nature = createCatalogCategory('Wisata Alam', 'wisata-alam');
    $city = createCatalogCategory('Wisata Kota', 'wisata-kota', 2);
    createCatalogTour($nature, 'Sungai Domestik', 'sungai-domestik');
    createCatalogTour($nature, 'Sungai Internasional', 'sungai-internasional', TourType::International);
    createCatalogTour($city, 'Kota Internasional', 'kota-internasional', TourType::International);

    app()->setLocale('en');

    tourCatalogComponent()
        ->set('category', 'wisata-alam-en')
        ->set('type', TourType::International->value)
        ->assertSee('Sungai Internasional EN')
        ->assertDontSee('Sungai Domestik EN')
        ->assertDontSee('Kota Internasional EN')
        ->assertSee('International')
        ->assertSee('Reset filters');
});

it('hydrates supported filters from the query string and rejects invalid tour types', function () {
    $category = createCatalogCategory('Wisata Kota', 'wisata-kota');
    createCatalogTour($category, 'Bangkok Halal', 'bangkok-halal', TourType::International);
    createCatalogTour($category, 'Bandung', 'bandung');

    Livewire::withQueryParams([
        'destination' => 'Bangkok',
        'type' => TourType::International->value,
    ])->test(TourCatalog::class, [
        'heroImageUrl' => 'https://example.com/tour-cover.jpg',
        'heroImageAlt' => 'Tour cover',
    ])
        ->assertSet('destination', 'Bangkok')
        ->assertSet('type', TourType::International->value)
        ->assertSee('Bangkok Halal')
        ->assertDontSee('Bandung');

    Livewire::withQueryParams(['type' => 'invalid'])
        ->test(TourCatalog::class, [
            'heroImageUrl' => 'https://example.com/tour-cover.jpg',
            'heroImageAlt' => 'Tour cover',
        ])
        ->assertSet('type', '');
});

it('resets pagination when filters change and can clear every filter', function () {
    $category = createCatalogCategory('Wisata Alam', 'wisata-alam');

    foreach (range(1, 7) as $index) {
        createCatalogTour($category, 'Tour '.$index, 'tour-'.$index);
    }

    tourCatalogComponent()
        ->call('setPage', 2)
        ->assertSet('paginators.page', 2)
        ->set('destination', 'Tour 1')
        ->assertSet('paginators.page', 1)
        ->set('category', 'wisata-alam')
        ->set('type', TourType::Domestic->value)
        ->call('resetFilters')
        ->assertSet('destination', '')
        ->assertSet('category', '')
        ->assertSet('type', '')
        ->assertSet('paginators.page', 1);
});
