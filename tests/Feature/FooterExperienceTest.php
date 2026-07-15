<?php

use App\Models\Destination;
use App\Models\Tour;
use App\Models\TourPackage;
use App\Models\TourPackageItinerary;
use App\Settings\FooterSettings;
use App\Settings\GeneralSettings;
use App\Settings\SocialSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

function createFooterDestination(string $name, string $slug, bool $hasActiveTour = true): Destination
{
    $tour = Tour::create([
        'name' => ['id' => "Tour {$name}", 'en' => "{$name} Tour", 'ms' => "Lawatan {$name}"],
        'slug' => ['id' => "tour-{$slug}", 'en' => "{$slug}-tour", 'ms' => "lawatan-{$slug}"],
        'short_description' => ['id' => 'Deskripsi', 'en' => 'Description', 'ms' => 'Penerangan'],
        'description' => ['id' => 'Deskripsi', 'en' => 'Description', 'ms' => 'Penerangan'],
        'tour_type' => 'domestic',
        'currency' => 'IDR',
        'is_active' => $hasActiveTour,
        'is_featured' => false,
    ]);

    $package = TourPackage::create([
        'tour_id' => $tour->id,
        'name' => ['id' => "Paket {$name}", 'en' => "{$name} Package", 'ms' => "Pakej {$name}"],
        'slug' => ['id' => "paket-{$slug}", 'en' => "{$slug}-package", 'ms' => "pakej-{$slug}"],
        'duration_days' => 3,
        'duration_nights' => 2,
    ]);

    $itinerary = TourPackageItinerary::create([
        'tour_package_id' => $package->id,
        'day_number' => 1,
        'title' => ['id' => 'Hari Pertama', 'en' => 'First Day', 'ms' => 'Hari Pertama'],
        'description' => ['id' => 'Tiba', 'en' => 'Arrival', 'ms' => 'Tiba'],
    ]);

    $destination = Destination::create([
        'name' => ['id' => $name, 'en' => $name, 'ms' => $name],
        'slug' => $slug,
        'description' => ['id' => 'Destinasi', 'en' => 'Destination', 'ms' => 'Destinasi'],
        'location' => 'Indonesia',
    ]);

    $itinerary->destinations()->attach($destination);

    return $destination;
}

it('renders the managed OTA footer structure', function () {
    createFooterDestination('Tanjung Puting', 'tanjung-puting');

    get('/id')
        ->assertOk()
        ->assertSee('Butuh bantuan memilih perjalanan?')
        ->assertSee('Ikuti perjalanan kami')
        ->assertSeeInOrder(['Layanan', 'Destinasi', 'Perusahaan', 'Bantuan'])
        ->assertSee('Pertanyaan Umum')
        ->assertSee('PT Baharsyah Jelajah Untuk Semua. Hak Cipta Dilindungi.');
});

it('only lists destinations connected to active tours and respects the configured limit', function () {
    createFooterDestination('Tanjung Puting', 'tanjung-puting');
    createFooterDestination('Bangkok', 'bangkok');
    createFooterDestination('Tour Nonaktif', 'tour-nonaktif', false);

    $settings = app(FooterSettings::class);
    $settings->destination_limit = 1;
    $settings->save();

    app()->setLocale('id');
    $footer = Blade::render('<x-shared.footer />');

    expect($footer)
        ->toContain('Bangkok')
        ->toContain('href="'.route('destination.show', ['locale' => 'id', 'destination' => 'bangkok']).'"')
        ->toContain('Semua destinasi')
        ->toContain('href="'.route('destination.index', ['locale' => 'id']).'"')
        ->not->toContain('Tanjung Puting')
        ->not->toContain('Tour Nonaktif');
});

it('localizes footer content and destination navigation', function (string $locale, string $cta, string $social, string $allDestinations) {
    createFooterDestination('Bangkok', 'bangkok');

    get("/{$locale}")
        ->assertOk()
        ->assertSee($cta)
        ->assertSee($social)
        ->assertSee($allDestinations)
        ->assertSee('<html lang="'.$locale.'"', false);
})->with([
    'Indonesian' => ['id', 'Butuh bantuan memilih perjalanan?', 'Ikuti perjalanan kami', 'Semua destinasi'],
    'English' => ['en', 'Need help choosing a trip?', 'Follow our journeys', 'All destinations'],
    'Malay' => ['ms', 'Perlukan bantuan memilih perjalanan?', 'Ikuti perjalanan kami', 'Semua destinasi'],
]);

it('omits unavailable contact and social channels without breaking the footer', function () {
    $generalSettings = app(GeneralSettings::class);
    $generalSettings->whatsapp_number = '';
    $generalSettings->email = '';
    $generalSettings->address = ['id' => '', 'en' => '', 'ms' => ''];
    $generalSettings->save();

    $socialSettings = app(SocialSettings::class);
    $socialSettings->instagram = null;
    $socialSettings->facebook = null;
    $socialSettings->tiktok = null;
    $socialSettings->youtube = null;
    $socialSettings->save();

    get('/id')
        ->assertOk()
        ->assertDontSee('https://wa.me/', false)
        ->assertDontSee('mailto:', false)
        ->assertDontSee('Media sosial Baharsyah Jelajah')
        ->assertSee('Baharsyah Jelajah membantu merencanakan tour halal');
});

it('reflects managed footer settings on the public website', function () {
    $settings = app(FooterSettings::class);
    $settings->cta_title = [
        'id' => 'Rencanakan perjalanan khusus Anda',
        'en' => 'Plan your custom journey',
        'ms' => 'Rancang perjalanan khas anda',
    ];
    $settings->save();

    get('/id')
        ->assertOk()
        ->assertSee('Rencanakan perjalanan khusus Anda');
});
