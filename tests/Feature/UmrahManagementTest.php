<?php

use App\Models\UmrahDeparturePrice;
use App\Models\UmrahPackage;
use App\Models\UmrahPackageItinerary;
use App\Models\UmrahPackagePrice;
use Database\Seeders\UmrahPackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('resolves room prices, departure overrides, itinerary translations, and quota status', function () {
    $package = UmrahPackage::factory()->create();
    $roomPrice = UmrahPackagePrice::factory()->for($package, 'package')->create([
        'room_type' => 'double',
        'price_idr' => 40_000_000,
    ]);
    $departure = $package->departures()->create([
        'departure_date' => now()->addMonth(),
        'return_date' => now()->addMonth()->addDays(9),
        'quota_total' => 10,
        'quota_booked' => 8,
        'status' => 'open',
    ]);
    UmrahDeparturePrice::query()->create([
        'umrah_departure_id' => $departure->id,
        'umrah_package_price_id' => $roomPrice->id,
        'price_idr' => 42_000_000,
    ]);
    $itinerary = UmrahPackageItinerary::factory()->for($package, 'package')->create([
        'day_number' => 1,
        'title' => ['id' => 'Berangkat', 'en' => 'Departure', 'ms' => 'Berlepas'],
    ]);

    $departure->load('prices');

    expect($package->getPriceForDeparture($departure, $roomPrice))->toBe(42_000_000)
        ->and($departure->status)->toBe('nearly_full')
        ->and($package->prices()->count())->toBe(1)
        ->and($itinerary->getTranslation('title', 'en'))->toBe('Departure');
});

it('renders a localized active package show and hides inactive packages', function () {
    $package = UmrahPackage::factory()->create([
        'name' => ['id' => 'Umrah Awal Musim', 'en' => 'Early Season Umrah', 'ms' => 'Umrah Awal Musim'],
        'slug' => ['id' => 'umrah-awal-musim', 'en' => 'early-season-umrah', 'ms' => 'umrah-awal-musim'],
    ]);
    UmrahPackagePrice::factory()->for($package, 'package')->create([
        'room_type' => 'quad',
        'price_idr' => 32_000_000,
    ]);
    $inactive = UmrahPackage::factory()->create([
        'slug' => ['id' => 'paket-nonaktif', 'en' => 'inactive-package', 'ms' => 'pakej-tidak-aktif'],
        'is_active' => false,
    ]);

    get('/en/umroh/early-season-umrah')
        ->assertSuccessful()
        ->assertSee('Early Season Umrah')
        ->assertSee('Plan for your group')
        ->assertSee('href="'.route('umroh.show', ['locale' => 'id', 'umrah' => 'umrah-awal-musim']).'"', false);

    get('/id/umroh/'.$inactive->getTranslation('slug', 'id'))
        ->assertNotFound();
});

it('uses the Indonesian umrah slug when localized slugs are missing', function () {
    $package = UmrahPackage::factory()->create();
    $package->forgetTranslations('slug')
        ->setTranslation('slug', 'id', 'umrah-indonesia')
        ->save();

    get('/en/umroh/umrah-indonesia')
        ->assertSuccessful()
        ->assertSee('<link rel="alternate" hreflang="ms" href="'.route('umroh.show', [
            'locale' => 'ms',
            'umrah' => 'umrah-indonesia',
        ]).'">', false);
});

it('redirects unlocalized umrah child paths and preserves their query string', function () {
    get('/umroh/paket-reguler?ref=campaign')
        ->assertRedirect('/id/umroh/paket-reguler?ref=campaign');
});

it('seeds the umrah catalog idempotently', function () {
    $this->seed(UmrahPackageSeeder::class);
    $this->seed(UmrahPackageSeeder::class);

    expect(UmrahPackage::query()->count())->toBe(3)
        ->and(UmrahPackagePrice::query()->count())->toBe(12)
        ->and(UmrahPackageItinerary::query()->count())->toBe(12);
});
