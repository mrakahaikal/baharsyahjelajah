<?php

use App\Enums\TourType;
use App\Livewire\TourPackageCalculator;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\TourPackage;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createCalculatorPackage(string $suffix = ''): TourPackage
{
    $category = TourCategory::create([
        'name' => ['id' => 'Alam', 'en' => 'Nature', 'ms' => 'Alam'],
        'slug' => ['id' => 'alam'.$suffix, 'en' => 'nature'.$suffix, 'ms' => 'alam'.$suffix],
        'icon' => 'heroicon-o-map',
        'sort_order' => $suffix === '' ? 1 : 2,
    ]);
    $tour = Tour::create([
        'tour_category_id' => $category->id,
        'name' => ['id' => 'Tour Sungai', 'en' => 'River Tour', 'ms' => 'Lawatan Sungai'],
        'slug' => ['id' => 'tour-sungai'.$suffix, 'en' => 'river-tour'.$suffix, 'ms' => 'lawatan-sungai'.$suffix],
        'short_description' => ['id' => 'Jelajah sungai.', 'en' => 'Explore the river.', 'ms' => 'Jelajah sungai.'],
        'description' => ['id' => 'Jelajah sungai.', 'en' => 'Explore the river.', 'ms' => 'Jelajah sungai.'],
        'tour_type' => TourType::Domestic,
        'currency' => 'IDR',
        'is_active' => true,
        'is_featured' => true,
    ]);
    $package = $tour->packages()->create([
        'name' => ['id' => 'Paket Sungai', 'en' => 'River Package', 'ms' => 'Pakej Sungai'],
        'slug' => ['id' => 'paket-sungai'.$suffix, 'en' => 'river-package'.$suffix, 'ms' => 'pakej-sungai'.$suffix],
        'duration_days' => 4,
        'duration_nights' => 3,
    ]);
    $standardTier = $package->tiers()->create([
        'name' => ['id' => 'Standar', 'en' => 'Standard', 'ms' => 'Standard'],
        'hotel_stars' => 3,
    ]);
    $standardTier->priceTiers()->createMany([
        ['min_pax' => 1, 'max_pax' => 3, 'price' => 2000000, 'currency' => 'IDR'],
        ['min_pax' => 4, 'max_pax' => null, 'price' => 1500000, 'currency' => 'IDR'],
    ]);
    $premiumTier = $package->tiers()->create([
        'name' => ['id' => 'Premium', 'en' => 'Premium', 'ms' => 'Premium'],
        'hotel_stars' => 4,
    ]);
    $premiumTier->priceTiers()->create([
        'min_pax' => 1,
        'max_pax' => null,
        'price' => 3000000,
        'currency' => 'IDR',
    ]);

    $settings = app(GeneralSettings::class);
    $settings->default_pax = 2;
    $settings->save();

    return $package->fresh();
}

it('recalculates per-person and total prices without a page request', function () {
    $package = createCalculatorPackage();
    $premiumTier = $package->tiers()->where('name->id', 'Premium')->firstOrFail();

    Livewire::test(TourPackageCalculator::class, ['package' => $package])
        ->assertSet('pax', '2')
        ->assertSee('Rp 2.000.000')
        ->assertSee('Rp 4.000.000')
        ->set('pax', '4')
        ->assertSee('Rp 1.500.000')
        ->assertSee('Rp 6.000.000')
        ->set('selectedTierId', $premiumTier->id)
        ->assertSee('Rp 3.000.000')
        ->assertSee('Rp 12.000.000')
        ->assertSee(route('tour.package.booking', [
            'locale' => 'id',
            'tour' => 'tour-sungai',
            'package' => 'paket-sungai',
            'tier' => $premiumTier->id,
            'pax' => 4,
        ]));
});

it('rejects invalid participant values and foreign tiers', function () {
    $package = createCalculatorPackage();
    $foreignPackage = createCalculatorPackage('-foreign');
    $foreignTier = $foreignPackage->tiers()->firstOrFail();

    Livewire::test(TourPackageCalculator::class, ['package' => $package])
        ->set('pax', '0')
        ->assertSee('Masukkan jumlah peserta antara 1 dan 1.000.')
        ->assertDontSee(route('tour.package.booking', [
            'locale' => 'id',
            'tour' => 'tour-sungai',
            'package' => 'paket-sungai',
            'pax' => 0,
        ]))
        ->set('pax', '2')
        ->set('selectedTierId', $foreignTier->id)
        ->assertSee('Harga untuk pilihan ini akan dikonfirmasi oleh tim kami.');
});

it('allows a booking request when fixed tiers are unavailable', function () {
    $package = createCalculatorPackage();
    $package->tiers()->delete();

    Livewire::test(TourPackageCalculator::class, ['package' => $package])
        ->assertSee('Harga untuk pilihan ini akan dikonfirmasi oleh tim kami.')
        ->assertSee(route('tour.package.booking', [
            'locale' => 'id',
            'tour' => 'tour-sungai',
            'package' => 'paket-sungai',
            'pax' => 2,
        ]));
});
