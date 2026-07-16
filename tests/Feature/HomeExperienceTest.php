<?php

use App\Models\Country;
use App\Models\UmrahDeparture;
use App\Models\UmrahPackage;
use App\Models\VisaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

function createUmrahPackageForExperience(string $name, string $type = 'regular', bool $isActive = true): UmrahPackage
{
    return UmrahPackage::create([
        'name' => ['id' => $name, 'en' => $name, 'ms' => $name],
        'description' => ['id' => 'Paket perjalanan ibadah.', 'en' => 'A worship journey package.', 'ms' => 'Pakej perjalanan ibadah.'],
        'package_type' => $type,
        'duration_days' => 9,
        'price_idr' => 32_500_000,
        'airline' => 'Garuda Indonesia',
        'hotel_makkah' => 'Hotel Makkah',
        'room_type' => 'quad',
        'is_active' => $isActive,
    ]);
}

it('renders a conversion focused home with working tour filters and active umrah packages', function () {
    $activePackage = createUmrahPackageForExperience('Umrah Awal Musim');
    createUmrahPackageForExperience('Umrah Tidak Aktif', isActive: false);

    UmrahDeparture::create([
        'package_id' => $activePackage->id,
        'departure_date' => now()->addMonth()->toDateString(),
        'return_date' => now()->addMonth()->addDays(9)->toDateString(),
        'quota_total' => 30,
        'quota_booked' => 5,
        'status' => 'open',
    ]);

    get('/id')
        ->assertSuccessful()
        ->assertSee('Perjalanan yang jelas sejak langkah pertama.')
        ->assertSee('Pilih layanan perjalanan')
        ->assertSee('Transport')
        ->assertSee('Visa')
        ->assertSee('name="destination"', false)
        ->assertSee('name="type"', false)
        ->assertDontSee('name="pax"', false)
        ->assertSee('Umrah Awal Musim')
        ->assertDontSee('Umrah Tidak Aktif')
        ->assertSee('href="'.route('umroh.index', ['locale' => 'id']).'"', false)
        ->assertSee('<meta name="description" content="'.__('home.seo.description').'">', false)
        ->assertSee('<link rel="alternate" hreflang="en" href="'.route('home', ['locale' => 'en']).'">', false)
        ->assertSee('"@type":"TravelAgency"', false);
});

it('shows only publicly available visa services on the homepage', function () {
    $egypt = Country::factory()->create([
        'name' => ['id' => 'Mesir', 'en' => 'Egypt', 'ms' => 'Mesir'],
        'slug' => 'mesir',
    ]);
    $activeService = VisaService::factory()->for($egypt)->create([
        'name' => ['id' => 'Visa Kunjungan Mesir', 'en' => 'Egypt Visit Visa', 'ms' => 'Visa Lawatan Mesir'],
        'slug' => 'visa-kunjungan-mesir',
        'is_featured' => true,
    ]);
    VisaService::factory()->for($egypt)->create([
        'name' => ['id' => 'Visa Tidak Aktif', 'en' => 'Inactive Visa', 'ms' => 'Visa Tidak Aktif'],
        'slug' => 'visa-tidak-aktif',
        'is_active' => false,
    ]);

    $inactiveCountry = Country::factory()->create(['is_active' => false]);
    VisaService::factory()->for($inactiveCountry)->create([
        'name' => ['id' => 'Visa Negara Tidak Aktif', 'en' => 'Inactive Country Visa', 'ms' => 'Visa Negara Tidak Aktif'],
        'slug' => 'visa-negara-tidak-aktif',
    ]);

    $deletedCountry = Country::factory()->create();
    VisaService::factory()->for($deletedCountry)->create([
        'name' => ['id' => 'Visa Negara Terhapus', 'en' => 'Deleted Country Visa', 'ms' => 'Visa Negara Terhapus'],
        'slug' => 'visa-negara-terhapus',
    ]);
    $deletedCountry->delete();

    get('/id')
        ->assertSuccessful()
        ->assertSee('Layanan Visa untuk WNI')
        ->assertSee($activeService->name)
        ->assertSee('href="'.route('visa.index', ['locale' => 'id']).'"', false)
        ->assertSee('href="'.route('visa.show', ['locale' => 'id', 'visaService' => $activeService->slug]).'"', false)
        ->assertDontSee('Visa Tidak Aktif')
        ->assertDontSee('Visa Negara Tidak Aktif')
        ->assertDontSee('Visa Negara Terhapus');
});

it('filters the umrah catalog by a supported package type', function () {
    createUmrahPackageForExperience('Umrah Reguler', 'regular');
    createUmrahPackageForExperience('Umrah VIP', 'vip');

    get('/id/umroh?type=vip')
        ->assertSuccessful()
        ->assertSee('Umrah VIP')
        ->assertDontSee('Umrah Reguler')
        ->assertSee('value="vip" selected', false)
        ->assertSee('Hapus filter');
});

it('ignores unsupported umrah package filters', function () {
    createUmrahPackageForExperience('Umrah Reguler', 'regular');
    createUmrahPackageForExperience('Umrah VIP', 'vip');

    get('/id/umroh?type=unknown')
        ->assertSuccessful()
        ->assertSee('Umrah Reguler')
        ->assertSee('Umrah VIP')
        ->assertDontSee('Hapus filter');
});

it('localizes the home and umrah catalog', function (string $locale, string $homeTitle, string $umrahTitle) {
    get('/'.$locale)
        ->assertSuccessful()
        ->assertSee($homeTitle)
        ->assertSee('<html lang="'.$locale.'"', false);

    get('/'.$locale.'/umroh')
        ->assertSuccessful()
        ->assertSee($umrahTitle);
})->with([
    'Indonesia' => ['id', 'Perjalanan yang jelas sejak langkah pertama.', 'Persiapan yang baik untuk perjalanan ibadah yang lebih tenang.'],
    'English' => ['en', 'A clearer journey from the very first step.', 'Thoughtful preparation for a calmer journey of worship.'],
    'Malay' => ['ms', 'Perjalanan yang lebih jelas sejak langkah pertama.', 'Persediaan yang baik untuk perjalanan ibadah yang lebih tenang.'],
]);
