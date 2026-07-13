<?php

use App\Enums\TourType;
use App\Filament\Resources\Destinations\Pages\ManageDestinations;
use App\Models\Destination;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\TourPackage;
use App\Models\TourPackageItinerary;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shares a destination between packages and itineraries through a polymorphic pivot', function () {
    $category = TourCategory::create([
        'name' => ['id' => 'Wisata Alam'],
        'slug' => ['id' => 'wisata-alam'],
        'sort_order' => 1,
    ]);

    $tour = Tour::create([
        'tour_category_id' => $category->id,
        'name' => ['id' => 'Jelajah Kalimantan'],
        'slug' => ['id' => 'jelajah-kalimantan'],
        'tour_type' => TourType::Domestic,
        'currency' => 'IDR',
        'is_active' => true,
        'is_featured' => false,
    ]);

    $firstPackage = $tour->packages()->create([
        'name' => ['id' => 'Paket Tiga Hari'],
        'slug' => ['id' => 'paket-tiga-hari'],
        'duration_days' => 3,
        'duration_nights' => 2,
    ]);

    $secondPackage = $tour->packages()->create([
        'name' => ['id' => 'Paket Empat Hari'],
        'slug' => ['id' => 'paket-empat-hari'],
        'duration_days' => 4,
        'duration_nights' => 3,
    ]);

    $itinerary = $firstPackage->itineraries()->create([
        'day_number' => 1,
        'title' => ['id' => 'Menyusuri Sungai'],
    ]);

    $destination = Destination::create([
        'name' => ['id' => 'Sungai Sekonyer'],
        'slug' => 'sungai-sekonyer',
    ]);

    $firstPackage->destinations()->attach($destination);
    $secondPackage->destinations()->attach($destination);
    $itinerary->destinations()->attach($destination);
    $firstPackage->destinations()->syncWithoutDetaching([$destination->id]);

    $secondItinerary = $secondPackage->itineraries()->create([
        'day_number' => 1,
        'title' => ['id' => 'Wisata Kota'],
    ]);
    $secondItinerary->destinations()->attach($destination);

    expect(Schema::hasTable('destinationables'))->toBeTrue()
        ->and(Schema::hasColumns('destinations', ['destinationable_type', 'destinationable_id']))->toBeFalse()
        ->and($firstPackage->fresh()->destinations->sole()->is($destination))->toBeTrue()
        ->and($secondPackage->fresh()->destinations->sole()->is($destination))->toBeTrue()
        ->and($itinerary->fresh()->destinations->sole()->is($destination))->toBeTrue()
        ->and($destination->fresh()->tourPackages)->toHaveCount(2)
        ->and($destination->fresh()->itineraries)->toHaveCount(2)
        ->and(DB::table('destinationables')->count())->toBe(4)
        ->and($destination->tourPackages->every(fn (TourPackage $package): bool => $package->tour_id === $tour->id))->toBeTrue()
        ->and($destination->itineraries->contains(fn (TourPackageItinerary $item): bool => $item->is($itinerary)))->toBeTrue();

    $secondItinerary->delete();

    expect($destination->fresh()->itineraries->sole()->is($itinerary))->toBeTrue()
        ->and(DB::table('destinationables')->count())->toBe(3);

    $firstPackage->delete();

    expect($destination->fresh()->tourPackages->sole()->is($secondPackage))->toBeTrue()
        ->and($destination->fresh()->itineraries)->toBeEmpty()
        ->and(DB::table('destinationables')->count())->toBe(1);

    $destination->delete();

    expect(DB::table('destinationables')->count())->toBe(0);
});

it('renders the destination resource without legacy morph columns', function () {
    $this->actingAs(User::factory()->create());

    $destination = Destination::create([
        'name' => ['id' => 'Camp Leakey'],
        'slug' => 'camp-leakey',
    ]);

    Livewire::test(ManageDestinations::class)
        ->assertCanSeeTableRecords([$destination]);
});

it('creates a localized destination with gallery media from the filament resource', function () {
    $this->actingAs(User::factory()->create());
    Storage::fake('public');

    Livewire::test(ManageDestinations::class)
        ->callAction(CreateAction::class, data: [
            'name' => [
                'id' => 'Taman Nasional Tanjung Puting',
                'en' => 'Tanjung Puting National Park',
                'ms' => 'Taman Negara Tanjung Puting',
            ],
            'slug' => 'taman-nasional-tanjung-puting',
            'description' => [
                'id' => 'Kawasan konservasi orangutan.',
                'en' => 'An orangutan conservation area.',
                'ms' => 'Kawasan pemuliharaan orang utan.',
            ],
            'location' => 'Kotawaringin Barat, Kalimantan Tengah',
            'map_url' => 'https://maps.google.com/?q=Tanjung+Puting',
            'gallery' => [
                UploadedFile::fake()->image('destination.jpg', 1200, 800),
            ],
        ])
        ->assertHasNoActionErrors()
        ->assertNotified();

    $destination = Destination::query()->sole();

    expect($destination->getTranslation('name', 'en'))->toBe('Tanjung Puting National Park')
        ->and($destination->slug)->toBe('taman-nasional-tanjung-puting')
        ->and($destination->getMedia(Destination::MEDIA_COLLECTION_GALLERY))->toHaveCount(1);

    Livewire::test(ManageDestinations::class)
        ->callAction(TestAction::make('edit')->table($destination), data: [
            'name' => [
                'id' => 'Tanjung Puting dan Sekonyer',
                'en' => 'Tanjung Puting and Sekonyer',
                'ms' => 'Tanjung Puting dan Sekonyer',
            ],
            'slug' => 'tanjung-puting-sekonyer',
            'description' => $destination->getTranslations('description'),
            'location' => 'Kumai, Kalimantan Tengah',
            'map_url' => $destination->map_url,
        ])
        ->assertHasNoActionErrors()
        ->assertNotified();

    $destination->refresh();

    expect($destination->getTranslation('name', 'id'))->toBe('Tanjung Puting dan Sekonyer')
        ->and($destination->slug)->toBe('tanjung-puting-sekonyer')
        ->and($destination->location)->toBe('Kumai, Kalimantan Tengah')
        ->and($destination->getMedia(Destination::MEDIA_COLLECTION_GALLERY))->toHaveCount(1);
});
