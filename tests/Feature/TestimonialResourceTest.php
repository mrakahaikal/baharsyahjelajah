<?php

use App\Enums\TourType;
use App\Filament\Resources\Testimonials\Pages\ManageTestimonials;
use App\Models\Testimonial;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\UmrahPackage;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can render the testimonials resource page and see product names in table', function () {
    $vehicle = Vehicle::factory()->create([
        'name' => ['id' => 'Toyota HiAce Luxury', 'en' => 'Toyota HiAce Luxury', 'ms' => 'Toyota HiAce Luxury'],
    ]);

    Testimonial::create([
        'reviewer_name' => ['id' => 'Budi Santoso', 'en' => 'Budi Santoso', 'ms' => 'Budi Santoso'],
        'reviewer_country' => 'ID',
        'reviewer_flag' => '🇮🇩',
        'product_type' => Vehicle::class,
        'product_id' => $vehicle->id,
        'rating' => 5,
        'content' => ['id' => 'Mobil sangat nyaman dan bersih.', 'en' => 'Very comfortable car.', 'ms' => 'Kereta sangat selesa.'],
        'is_featured' => true,
        'is_active' => true,
    ]);

    Livewire::test(ManageTestimonials::class)
        ->assertSuccessful()
        ->assertSee('Budi Santoso')
        ->assertSee('Toyota HiAce Luxury')
        ->assertSee('Sewa Mobil');
});

it('can create a testimonial using dynamic product dropdown', function () {
    $tourCategory = TourCategory::firstOrCreate(
        ['icon' => 'heroicon-o-sparkles'],
        [
            'name' => ['id' => 'Alam', 'en' => 'Nature', 'ms' => 'Alam'],
            'slug' => ['id' => 'alam', 'en' => 'nature', 'ms' => 'alam'],
        ]
    );

    $tour = Tour::create([
        'tour_category_id' => $tourCategory->id,
        'name' => ['id' => 'Derawan Island Paradise', 'en' => 'Derawan Island Paradise', 'ms' => 'Derawan Island Paradise'],
        'slug' => ['id' => 'derawan-island-paradise', 'en' => 'derawan-island-paradise', 'ms' => 'derawan-island-paradise'],
        'short_description' => ['id' => 'Short desc', 'en' => 'Short desc', 'ms' => 'Short desc'],
        'tour_type' => TourType::Domestic,
        'currency' => 'IDR',
        'is_active' => true,
        'is_featured' => false,
    ]);

    Livewire::test(ManageTestimonials::class)
        ->callAction(CreateAction::class, data: [
            'reviewer_name' => ['id' => 'Ahmad Fauzi', 'en' => 'Ahmad Fauzi', 'ms' => 'Ahmad Fauzi'],
            'reviewer_country' => 'ID',
            'product_type' => Tour::class,
            'product_id' => $tour->id,
            'rating' => 5,
            'content' => ['id' => 'Pengalaman luar biasa!', 'en' => 'Amazing experience!', 'ms' => 'Pengalaman luar biasa!'],
            'is_featured' => false,
            'is_active' => true,
        ])
        ->assertHasNoActionErrors();

    expect(Testimonial::query()->where('product_id', $tour->id)->where('product_type', Tour::class)->exists())
        ->toBeTrue();
});

it('can edit an existing testimonial and change its product', function () {
    $vehicleA = Vehicle::factory()->create(['name' => ['id' => 'Innova Reborn', 'en' => 'Innova Reborn', 'ms' => 'Innova Reborn']]);
    $vehicleB = Vehicle::factory()->create(['name' => ['id' => 'Fortuner GR', 'en' => 'Fortuner GR', 'ms' => 'Fortuner GR']]);

    $testimonial = Testimonial::create([
        'reviewer_name' => ['id' => 'Dewi Lestari', 'en' => 'Dewi Lestari', 'ms' => 'Dewi Lestari'],
        'product_type' => Vehicle::class,
        'product_id' => $vehicleA->id,
        'rating' => 4,
        'content' => ['id' => 'Pelayanan ramah.', 'en' => 'Friendly service.', 'ms' => 'Layanan mesra.'],
        'is_active' => true,
    ]);

    Livewire::test(ManageTestimonials::class)
        ->callAction(TestAction::make('edit')->table($testimonial), data: [
            'product_type' => Vehicle::class,
            'product_id' => $vehicleB->id,
        ])
        ->assertHasNoActionErrors();

    expect($testimonial->fresh()->product_id)->toBe($vehicleB->id);
});

it('can create a testimonial for umrah package', function () {
    $package = UmrahPackage::factory()->create([
        'name' => ['id' => 'Paket Umrah Ramadhan', 'en' => 'Ramadan Umrah Package', 'ms' => 'Pakej Umrah Ramadhan'],
    ]);

    Livewire::test(ManageTestimonials::class)
        ->callAction(CreateAction::class, data: [
            'reviewer_name' => ['id' => 'Ustadz Abdullah', 'en' => 'Ustadz Abdullah', 'ms' => 'Ustadz Abdullah'],
            'product_type' => UmrahPackage::class,
            'product_id' => $package->id,
            'rating' => 5,
            'content' => ['id' => 'Ibadah sangat khusyuk dan terbimbing.', 'en' => 'Worship was guided well.', 'ms' => 'Ibadah dibimbing dengan baik.'],
            'is_featured' => true,
            'is_active' => true,
        ])
        ->assertHasNoActionErrors();

    expect(Testimonial::query()->where('product_id', $package->id)->where('product_type', UmrahPackage::class)->exists())
        ->toBeTrue();
});
