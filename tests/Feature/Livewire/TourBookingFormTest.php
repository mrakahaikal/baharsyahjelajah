<?php

use App\Enums\TourType;
use App\Livewire\TourBookingForm;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\TourPackage;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

function createBookingPackage(string $suffix = ''): TourPackage
{
    $category = TourCategory::create([
        'name' => ['id' => 'Budaya'.$suffix, 'en' => 'Culture'.$suffix, 'ms' => 'Budaya'.$suffix],
        'slug' => ['id' => 'budaya'.$suffix, 'en' => 'culture'.$suffix, 'ms' => 'budaya'.$suffix],
        'icon' => 'heroicon-o-map',
        'sort_order' => $suffix === '' ? 1 : 2,
    ]);
    $tour = Tour::create([
        'tour_category_id' => $category->id,
        'name' => ['id' => 'Tour Bangkok'.$suffix, 'en' => 'Bangkok Tour'.$suffix, 'ms' => 'Lawatan Bangkok'.$suffix],
        'slug' => ['id' => 'tour-bangkok'.$suffix, 'en' => 'bangkok-tour'.$suffix, 'ms' => 'lawatan-bangkok'.$suffix],
        'short_description' => ['id' => 'Jelajah Bangkok.', 'en' => 'Explore Bangkok.', 'ms' => 'Jelajah Bangkok.'],
        'description' => ['id' => 'Jelajah Bangkok.', 'en' => 'Explore Bangkok.', 'ms' => 'Jelajah Bangkok.'],
        'tour_type' => TourType::International,
        'currency' => 'IDR',
        'is_active' => true,
        'is_featured' => true,
    ]);
    $package = $tour->packages()->create([
        'name' => ['id' => 'Paket Bangkok'.$suffix, 'en' => 'Bangkok Package'.$suffix, 'ms' => 'Pakej Bangkok'.$suffix],
        'slug' => ['id' => 'paket-bangkok'.$suffix, 'en' => 'bangkok-package'.$suffix, 'ms' => 'pakej-bangkok'.$suffix],
        'duration_days' => 5,
        'duration_nights' => 4,
    ]);
    $tier = $package->tiers()->create([
        'name' => ['id' => 'Superior', 'en' => 'Superior', 'ms' => 'Superior'],
        'hotel_stars' => 4,
    ]);
    $tier->priceTiers()->createMany([
        ['min_pax' => 1, 'max_pax' => 3, 'price' => 2500000, 'currency' => 'IDR'],
        ['min_pax' => 4, 'max_pax' => null, 'price' => 2000000, 'currency' => 'IDR'],
    ]);

    $settings = app(GeneralSettings::class);
    $settings->default_pax = 2;
    $settings->whatsapp_number = '+6281234567890';
    $settings->save();

    return $package->fresh('tiers.priceTiers');
}

it('renders a localized booking page with normalized package selections', function () {
    $package = createBookingPackage();
    $tier = $package->tiers->first();

    get(route('tour.package.booking', [
        'locale' => 'id',
        'tour' => 'tour-bangkok',
        'package' => 'paket-bangkok',
        'tier' => $tier->id,
        'pax' => 4,
    ]))
        ->assertSuccessful()
        ->assertSee('Lengkapi rencana perjalanan Anda')
        ->assertSee('Rp 2.000.000')
        ->assertSee('Rp 8.000.000')
        ->assertSee('<meta name="robots" content="noindex, follow">', false)
        ->assertSee('<link rel="canonical" href="'.route('tour.package.booking', [
            'locale' => 'id',
            'tour' => 'tour-bangkok',
            'package' => 'paket-bangkok',
        ]).'">', false);

    get('/en/tour/tour-bangkok/package/paket-bangkok/booking?tier='.$tier->id.'&pax=4')
        ->assertRedirect(route('tour.package.booking', [
            'locale' => 'en',
            'tour' => 'bangkok-tour',
            'package' => 'bangkok-package',
            'tier' => $tier->id,
            'pax' => 4,
        ]))
        ->assertStatus(301);
});

it('validates booking details and redirects a complete request to whatsapp', function () {
    $package = createBookingPackage();
    $tier = $package->tiers->first();

    Livewire::test(TourBookingForm::class, [
        'package' => $package,
        'initialTierId' => $tier->id,
        'initialPax' => 4,
    ])
        ->set('customerName', 'Raka Haikal')
        ->set('whatsappNumber', '081234567890')
        ->set('email', 'raka@example.com')
        ->set('departureDate', today()->addMonth()->toDateString())
        ->set('notes', 'Kamar berdekatan')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirectContains('https://wa.me/6281234567890')
        ->assertRedirectContains('Raka%20Haikal')
        ->assertRedirectContains('Rp%208.000.000');

    expect(Tour::query()->count())->toBe(1)
        ->and(TourPackage::query()->count())->toBe(1);
});

it('rejects invalid booking data and a tier from another package', function () {
    $package = createBookingPackage();
    $foreignTier = createBookingPackage('-lain')->tiers->first();

    Livewire::test(TourBookingForm::class, ['package' => $package])
        ->set('customerName', '')
        ->set('whatsappNumber', 'invalid phone')
        ->set('email', 'not-an-email')
        ->set('departureDate', today()->subDay()->toDateString())
        ->set('pax', '0')
        ->set('selectedTierId', $foreignTier->id)
        ->call('submit')
        ->assertHasErrors([
            'customerName' => 'required',
            'whatsappNumber' => 'regex',
            'email' => 'email',
            'departureDate' => 'after_or_equal',
            'pax' => 'min',
            'selectedTierId' => 'exists',
        ]);
});

it('keeps the user on the booking form when whatsapp is unavailable', function () {
    $package = createBookingPackage();
    $settings = app(GeneralSettings::class);
    $settings->whatsapp_number = '';
    $settings->save();

    Livewire::test(TourBookingForm::class, ['package' => $package])
        ->set('customerName', 'Raka Haikal')
        ->set('whatsappNumber', '081234567890')
        ->set('departureDate', today()->addMonth()->toDateString())
        ->call('submit')
        ->assertHasErrors(['service']);
});
