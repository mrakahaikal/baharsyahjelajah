<?php

use App\Enums\TourType;
use App\Filament\Resources\Tours\Pages\CreateTour;
use App\Filament\Resources\Tours\Pages\EditTour;
use App\Filament\Resources\Tours\Pages\ListTours;
use App\Filament\Resources\Tours\Pages\ViewTour;
use App\Models\Destination;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('creates a tour with nested packages and package details', function () {
    $this->actingAs(User::factory()->create());
    Storage::fake('public');

    $category = TourCategory::create([
        'name' => ['id' => 'Wisata Alam'],
        'slug' => ['id' => 'wisata-alam'],
        'sort_order' => 1,
    ]);

    $destination = Destination::create([
        'name' => ['id' => 'Camp Leakey'],
        'slug' => 'camp-leakey',
    ]);

    $replacementDestination = Destination::create([
        'name' => ['id' => 'Sungai Sekonyer'],
        'slug' => 'sungai-sekonyer',
    ]);

    Livewire::test(CreateTour::class)
        ->fillForm([
            'tour_category_id' => $category->id,
            'name' => ['id' => 'Jelajah Kalimantan'],
            'slug' => ['id' => 'jelajah-kalimantan'],
            'short_description' => ['id' => 'Perjalanan menyusuri Kalimantan.'],
            'description' => ['id' => '<p>Deskripsi lengkap perjalanan.</p>'],
            'tour_type' => TourType::Domestic->value,
            'currency' => 'IDR',
            'is_active' => true,
            'is_featured' => false,
            'packages' => [[
                'name' => ['id' => 'Paket Empat Hari'],
                'slug' => ['id' => 'paket-empat-hari'],
                'duration_days' => 4,
                'duration_nights' => 3,
                'cover' => [
                    UploadedFile::fake()->create('cover.jpg', 10, 'image/jpeg'),
                ],
                'gallery' => [
                    UploadedFile::fake()->create('gallery.jpg', 10, 'image/jpeg'),
                ],
                'itineraries' => [[
                    'day_number' => 1,
                    'title' => ['id' => 'Kedatangan'],
                    'description' => ['id' => '<p>Penjemputan di bandara.</p>'],
                    'destinations' => [$destination->id],
                ]],
                'includes' => [[
                    'type' => 'include',
                    'sort_order' => 1,
                    'item' => ['id' => 'Transportasi'],
                ]],
                'tiers' => [[
                    'name' => ['id' => 'Standar'],
                    'hotel_stars' => 3,
                    'priceTiers' => [[
                        'min_pax' => 1,
                        'max_pax' => null,
                        'price' => 3500000,
                        'currency' => 'IDR',
                    ]],
                ]],
            ]],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified()
        ->assertRedirect();

    $tour = Tour::query()
        ->with('packages.itineraries.destinations', 'packages.includes', 'packages.tiers.priceTiers')
        ->sole();
    $package = $tour->packages->sole();
    $tier = $package->tiers->sole();

    expect($tour->tour_category_id)->toBe($category->id)
        ->and($tour->tour_type)->toBe(TourType::Domestic)
        ->and($package->duration_days)->toBe(4)
        ->and($package->itineraries)->toHaveCount(1)
        ->and($package->itineraries->sole()->destinations->sole()->is($destination))->toBeTrue()
        ->and($package->includes)->toHaveCount(1)
        ->and($package->getFirstMedia('cover'))->not->toBeNull()
        ->and($package->getMedia('gallery'))->toHaveCount(1)
        ->and($tier->hotel_stars)->toBe(3)
        ->and($tier->priceTiers)->toHaveCount(1)
        ->and($tier->priceTiers->sole()->price)->toBe('3500000.00');

    $editPage = Livewire::test(EditTour::class, ['record' => $tour->getRouteKey()]);
    $packagesState = $editPage->get('data.packages');
    $packageStateKey = array_key_first($packagesState);
    $itineraryStateKey = array_key_first($packagesState[$packageStateKey]['itineraries']);

    $editPage
        ->set(
            "data.packages.{$packageStateKey}.itineraries.{$itineraryStateKey}.destinations",
            [$replacementDestination->id],
        )
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    expect($package->itineraries->sole()->fresh()->destinations->sole()->is($replacementDestination))->toBeTrue();
});

it('lists tours using package duration and tier pricing', function () {
    $this->actingAs(User::factory()->create());
    app()->setLocale('id');

    $category = TourCategory::create([
        'name' => ['id' => 'Wisata Alam'],
        'slug' => ['id' => 'wisata-alam'],
        'sort_order' => 1,
    ]);

    $domesticTour = Tour::create([
        'tour_category_id' => $category->id,
        'name' => ['id' => 'Jelajah Kalimantan'],
        'slug' => ['id' => 'jelajah-kalimantan'],
        'tour_type' => TourType::Domestic,
        'currency' => 'IDR',
        'is_active' => true,
        'is_featured' => true,
    ]);

    $package = $domesticTour->packages()->create([
        'name' => ['id' => 'Paket Tiga Hari'],
        'slug' => ['id' => 'paket-tiga-hari'],
        'duration_days' => 3,
        'duration_nights' => 2,
    ]);

    $tier = $package->tiers()->create([
        'name' => ['id' => 'Standar'],
        'hotel_stars' => 3,
    ]);

    $tier->priceTiers()->create([
        'min_pax' => 1,
        'max_pax' => null,
        'price' => 1250000,
        'currency' => 'IDR',
    ]);

    $internationalTour = Tour::create([
        'tour_category_id' => $category->id,
        'name' => ['id' => 'Jelajah Thailand'],
        'slug' => ['id' => 'jelajah-thailand'],
        'tour_type' => TourType::International,
        'currency' => 'USD',
        'is_active' => false,
        'is_featured' => false,
    ]);

    Livewire::test(ListTours::class)
        ->assertCanSeeTableRecords([$domesticTour, $internationalTour])
        ->assertSee('Domestik')
        ->assertSee('Internasional')
        ->assertSee('3 Hari 2 Malam')
        ->assertSee('Rp 1.250.000')
        ->filterTable('tour_type', TourType::Domestic->value)
        ->assertCanSeeTableRecords([$domesticTour])
        ->assertCanNotSeeTableRecords([$internationalTour]);
});

it('shows nested package details in the tour infolist', function () {
    $this->actingAs(User::factory()->create());
    app()->setLocale('id');

    $category = TourCategory::create([
        'name' => ['id' => 'Wisata Alam'],
        'slug' => ['id' => 'wisata-alam'],
        'sort_order' => 1,
    ]);

    $tour = Tour::create([
        'tour_category_id' => $category->id,
        'name' => ['id' => 'Ekspedisi Hutan'],
        'slug' => ['id' => 'ekspedisi-hutan'],
        'short_description' => ['id' => 'Perjalanan ke hutan Kalimantan.'],
        'description' => ['id' => '<p>Deskripsi ekspedisi lengkap.</p>'],
        'tour_type' => TourType::Domestic,
        'currency' => 'IDR',
        'is_active' => true,
        'is_featured' => true,
    ]);

    $package = $tour->packages()->create([
        'name' => ['id' => 'Paket Klotok'],
        'slug' => ['id' => 'paket-klotok'],
        'duration_days' => 4,
        'duration_nights' => 3,
    ]);

    $package->itineraries()->create([
        'day_number' => 1,
        'title' => ['id' => 'Menyusuri Sungai'],
        'description' => ['id' => '<p>Perjalanan dimulai dengan klotok.</p>'],
    ]);

    $package->includes()->create([
        'type' => 'include',
        'sort_order' => 1,
        'item' => ['id' => 'Transportasi klotok'],
    ]);

    $tier = $package->tiers()->create([
        'name' => ['id' => 'Superior'],
        'hotel_stars' => 4,
    ]);

    $tier->priceTiers()->create([
        'min_pax' => 2,
        'max_pax' => null,
        'price' => 2750000,
        'currency' => 'IDR',
    ]);

    Livewire::test(ViewTour::class, ['record' => $tour->getRouteKey()])
        ->assertSee('Ekspedisi Hutan')
        ->assertSee('Domestik')
        ->assertSee('Paket Klotok')
        ->assertSee('4 Hari')
        ->assertSee('Superior')
        ->assertSee('4 Bintang')
        ->assertSee('2.750.000,00')
        ->assertSee('Menyusuri Sungai')
        ->assertSee('Transportasi klotok')
        ->assertSee('Termasuk');
});
