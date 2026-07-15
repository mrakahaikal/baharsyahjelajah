<?php

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Testimonial;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('redirects from root to default locale when no supported language is detected', function () {
    get('/', ['Accept-Language' => 'fr-FR,fr;q=0.9'])
        ->assertRedirect('/id');
});

it('redirects from root to the detected supported locale', function () {
    get('/', ['Accept-Language' => 'ms-MY,ms;q=0.9,en;q=0.8'])
        ->assertRedirect('/ms');
});

it('redirects the contact shortcut to the detected supported locale', function () {
    get('/kontak', ['Accept-Language' => 'en-US,en;q=0.9,id;q=0.8'])
        ->assertRedirect('/en/kontak');
});

it('preserves the contact inquiry context while detecting the locale', function () {
    get('/kontak?tour=danau-labuan-cermin&type=b2b', ['Accept-Language' => 'en-US,en;q=0.9'])
        ->assertRedirect('/en/kontak?tour=danau-labuan-cermin&type=b2b');
});

it('redirects the tour shortcut to the default locale', function () {
    get('/tour')
        ->assertRedirect('/id/tour');
});

it('redirects tour child paths to the default locale while preserving the query string', function (string $path, string $destination) {
    get($path)
        ->assertRedirect($destination);
})->with([
    'tour show' => ['/tour/ekspedisi-orangutan', '/id/tour/ekspedisi-orangutan'],
    'package show' => ['/tour/ekspedisi-orangutan/package/klotok-4-hari', '/id/tour/ekspedisi-orangutan/package/klotok-4-hari'],
    'package booking' => [
        '/tour/ekspedisi-orangutan/package/klotok-4-hari/booking?tier=standard&pax=4',
        '/id/tour/ekspedisi-orangutan/package/klotok-4-hari/booking?pax=4&tier=standard',
    ],
]);

it('redirects the blog shortcut to the default locale', function () {
    get('/blog')
        ->assertRedirect('/id/blog');
});

it('redirects blog child paths to the default locale while preserving the query string', function (string $path, string $destination) {
    get($path)
        ->assertRedirect($destination);
})->with([
    'blog show' => ['/blog/panduan-tanjung-puting', '/id/blog/panduan-tanjung-puting'],
    'blog show with query' => [
        '/blog/panduan-tanjung-puting?ref=homepage&utm_source=search',
        '/id/blog/panduan-tanjung-puting?ref=homepage&utm_source=search',
    ],
]);

it('redirects visa paths to the default locale while preserving the query string', function (string $path, string $destination) {
    get($path)
        ->assertRedirect($destination);
})->with([
    'visa index' => ['/visa', '/id/visa'],
    'visa detail' => ['/visa/visa-kunjungan-mesir', '/id/visa/visa-kunjungan-mesir'],
    'visa detail with query' => [
        '/visa/visa-kunjungan-mesir?ref=homepage&utm_source=search',
        '/id/visa/visa-kunjungan-mesir?ref=homepage&utm_source=search',
    ],
]);

it('sets the application locale based on the URL prefix', function (string $locale) {
    get("/{$locale}")
        ->assertStatus(200)
        ->assertSee("<html lang=\"{$locale}\"", false);
})->with(['id', 'ms', 'en']);

it('renders language and currency switchers with local flag images', function () {
    get('/id')
        ->assertOk()
        ->assertSee('id="language-switcher"', false)
        ->assertSee('id="currency-switcher"', false)
        ->assertSee('images/flags/indonesia-flag.webp', false)
        ->assertSee('images/flags/malaysia-flag.webp', false)
        ->assertSee('images/flags/uk-flag.webp', false)
        ->assertSee('href="'.route('home', ['locale' => 'ms']).'"', false)
        ->assertSee('href="'.route('home', ['locale' => 'en']).'"', false)
        ->assertSee('href="'.route('set.currency', ['currency' => 'MYR']).'"', false)
        ->assertSee('href="'.route('set.currency', ['currency' => 'SGD']).'"', false)
        ->assertSee('href="'.route('set.currency', ['currency' => 'USD']).'"', false)
        ->assertSee('href="'.route('set.currency', ['currency' => 'EUR']).'"', false)
        ->assertSee('href="'.route('set.currency', ['currency' => 'SAR']).'"', false)
        ->assertSee('href="'.route('set.currency', ['currency' => 'JPY']).'"', false);
});

it('only switches to a supported currency', function () {
    $this->withSession(['app_currency' => 'IDR'])
        ->from('/id')
        ->get('/set-currency/USD')
        ->assertRedirect('/id')
        ->assertSessionHas('app_currency', 'USD');

    $this->from('/id')
        ->get('/set-currency/EUR')
        ->assertRedirect('/id')
        ->assertSessionHas('app_currency', 'EUR');

    $this->from('/id')
        ->get('/set-currency/CAD')
        ->assertRedirect('/id')
        ->assertSessionHas('app_currency', 'EUR');
});

it('renders the mvp homepage focused on tours, guides, and consultation', function () {
    $user = User::factory()->create();
    $category = PostCategory::create([
        'name' => ['id' => 'Panduan Wisata', 'en' => 'Travel Guide', 'ms' => 'Panduan Pelancongan'],
        'slug' => ['id' => 'panduan-wisata', 'en' => 'travel-guide', 'ms' => 'panduan-pelancongan'],
        'description' => ['id' => 'Panduan perjalanan', 'en' => 'Travel guides', 'ms' => 'Panduan perjalanan'],
    ]);

    Post::create([
        'post_category_id' => $category->id,
        'user_id' => $user->id,
        'title' => ['id' => 'Panduan Tour Kalimantan', 'en' => 'Borneo Tour Guide', 'ms' => 'Panduan Tour Borneo'],
        'slug' => ['id' => 'panduan-tour-kalimantan', 'en' => 'borneo-tour-guide', 'ms' => 'panduan-tour-borneo'],
        'excerpt' => ['id' => 'Panduan singkat untuk memilih rute tour Kalimantan.', 'en' => 'A short guide to choosing Borneo tour routes.', 'ms' => 'Panduan ringkas memilih laluan tour Borneo.'],
        'content' => ['id' => '<p>Konten artikel.</p>', 'en' => '<p>Article content.</p>', 'ms' => '<p>Kandungan artikel.</p>'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    Testimonial::create([
        'reviewer_name' => ['id' => 'Pelanggan Baharsyah', 'ms' => 'Pelanggan Baharsyah', 'en' => 'Baharsyah Customer'],
        'content' => ['id' => 'Layanan responsif dan perjalanan nyaman.', 'ms' => 'Layanan responsif dan perjalanan nyaman.', 'en' => 'Responsive service and comfortable travel.'],
        'reviewer_country' => 'Indonesia',
        'product_type' => 'tour',
        'product_id' => 1,
        'rating' => 5,
        'is_featured' => true,
        'is_active' => true,
    ]);

    get('/id')
        ->assertOk()
        ->assertSee('href="'.route('contact.index', ['locale' => 'id']).'"', false)
        ->assertSee('Hubungi Kami')
        ->assertDontSee('Book Trip')
        ->assertSee('id="mobile-navigation"', false)
        ->assertSee('id="tour-mega-menu"', false)
        ->assertSee('href="#main-content"', false)
        ->assertSee('method="GET" action="'.route('tour.index', ['locale' => 'id']).'"', false)
        ->assertSee('name="destination"', false)
        ->assertSee('name="type"', false)
        ->assertDontSee('name="pax"', false)
        ->assertSee('aria-label="Testimoni sebelumnya"', false)
        ->assertSee('aria-label="Testimoni berikutnya"', false)
        ->assertSee('Mulai pencarian')
        ->assertSee('Tour unggulan')
        ->assertSee('Perjalanan ibadah')
        ->assertSee('Pendampingan perjalanan')
        ->assertSee('Mulai merencanakan')
        ->assertSee('Baharsyah Jelajah')
        ->assertSee('Panduan Tour Kalimantan')
        ->assertSee('Butuh bantuan memilih perjalanan?')
        ->assertSee('Paket Tour')
        ->assertSee('Kontak')
        ->assertDontSee('Fokus MVP')
        ->assertDontSee('Home sekarang')
        ->assertSee('href="'.route('transport.index', ['locale' => 'id']).'"', false)
        ->assertSee('Armada pilihan, perjalanan lebih tenang')
        ->assertSee('Layanan Visa untuk WNI')
        ->assertSee('href="'.route('visa.index', ['locale' => 'id']).'"', false)
        ->assertDontSee('search-panel-transport')
        ->assertDontSee('search-panel-umroh')
        ->assertDontSee("Where's your next adventure?")
        ->assertDontSee('Travel Agents')
        ->assertDontSee('Photo Contests');
});

it('renders blog index and show pages with reusable post card content', function () {
    $user = User::factory()->create();
    $category = PostCategory::create([
        'name' => ['id' => 'Destinasi', 'en' => 'Destinations', 'ms' => 'Destinasi'],
        'slug' => ['id' => 'destinasi', 'en' => 'destinations', 'ms' => 'destinasi'],
        'description' => ['id' => 'Destinasi pilihan', 'en' => 'Featured destinations', 'ms' => 'Destinasi pilihan'],
    ]);

    $post = Post::create([
        'post_category_id' => $category->id,
        'user_id' => $user->id,
        'title' => ['id' => 'Rute Favorit Tanjung Puting', 'en' => 'Favorite Tanjung Puting Route', 'ms' => 'Laluan Favorit Tanjung Puting'],
        'slug' => ['id' => 'rute-favorit-tanjung-puting', 'en' => 'favorite-tanjung-puting-route', 'ms' => 'laluan-favorit-tanjung-puting'],
        'excerpt' => ['id' => 'Rute singkat untuk perjalanan pertama.', 'en' => 'A short route for the first trip.', 'ms' => 'Laluan ringkas untuk perjalanan pertama.'],
        'content' => ['id' => '<p>Detail rute Tanjung Puting.</p>', 'en' => '<p>Tanjung Puting route details.</p>', 'ms' => '<p>Butiran laluan Tanjung Puting.</p>'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    get('/id/blog')
        ->assertOk()
        ->assertSee('Rute Favorit Tanjung Puting')
        ->assertSee('Baca Selengkapnya')
        ->assertDontSee('Blog Index - Locale');

    get('/id/blog/'.$post->slug)
        ->assertOk()
        ->assertSee('Rute Favorit Tanjung Puting')
        ->assertSee('Detail rute Tanjung Puting.', false)
        ->assertDontSee('Blog Show - Locale');
});

it('renders page content directly below the sticky header without legacy spacing', function () {
    get('/id/tour')
        ->assertOk()
        ->assertSee('id="main-content" class="grow"', false)
        ->assertDontSee('class="grow pt-8"', false)
        ->assertDontSee('[&amp;&gt;*:first-child]:pt-28', false);

    get('/id')
        ->assertOk()
        ->assertSee('id="main-content" class="grow"', false)
        ->assertDontSee('class="grow pt-8"', false)
        ->assertDontSee('[&amp;&gt;*:first-child]:pt-28', false);
});

it('renders the contact page as an accessible whatsapp consultation workspace', function () {
    get('/id/kontak?tour=danau-labuan-cermin&type=b2b')
        ->assertOk()
        ->assertSee('Mulai perjalanan Anda dengan rencana yang lebih jelas.')
        ->assertSee('Apa yang sedang Anda rencanakan?')
        ->assertSee('Konsultasi Trip')
        ->assertSee('Kerja Sama B2B')
        ->assertSee('Buka petunjuk arah')
        ->assertSee('action="https://wa.me/6281234567890"', false)
        ->assertSee('role="tablist"', false)
        ->assertSee('role="tabpanel"', false)
        ->assertSee('data-initial-tab="b2b"', false)
        ->assertSee('name="text"', false)
        ->assertSee('name="destination_interest"', false)
        ->assertSee('value="Danau Labuan Cermin"', false)
        ->assertSee('type="date" name="estimated_date"', false)
        ->assertSee('name="organization_name"', false)
        ->assertSee('name="business_email"', false)
        ->assertSee('name="partnership_needs" rows="4" autocomplete="off" required', false)
        ->assertSee('title="Peta lokasi Baharsyah Jelajah"', false)
        ->assertSee('<link rel="canonical" href="'.route('contact.index', ['locale' => 'id']).'">', false)
        ->assertSee('<link rel="alternate" hreflang="en" href="'.route('contact.index', ['locale' => 'en']).'">', false)
        ->assertDontSee('fixed bottom-4 right-4', false);
});

it('localizes contact content and metadata', function (string $locale, string $title, string $tabLabel) {
    get("/{$locale}/kontak")
        ->assertOk()
        ->assertSee($title)
        ->assertSee($tabLabel)
        ->assertSee('<html lang="'.$locale.'"', false)
        ->assertSee('<link rel="canonical" href="'.route('contact.index', ['locale' => $locale]).'">', false);
})->with([
    'Indonesian' => ['id', 'Mulai perjalanan Anda dengan rencana yang lebih jelas.', 'Konsultasi Trip'],
    'English' => ['en', 'Start your journey with a clearer plan.', 'Trip Consultation'],
    'Malay' => ['ms', 'Mulakan perjalanan anda dengan rancangan yang lebih jelas.', 'Konsultasi Lawatan'],
]);

it('offers email as a fallback when whatsapp consultation is unavailable', function () {
    $settings = app(GeneralSettings::class);
    $settings->whatsapp_number = '';
    $settings->save();

    get('/id/kontak')
        ->assertOk()
        ->assertSee('Konsultasi WhatsApp sedang tidak tersedia')
        ->assertSee('href="mailto:'.$settings->email.'"', false)
        ->assertDontSee('role="tablist"', false)
        ->assertDontSee('action="https://wa.me/', false);
});

it('returns 404 for invalid locales', function () {
    get('/fr/transport')
        ->assertStatus(404);
});

it('renders the correct page view', function (string $path, string $content) {
    get("/id/{$path}")
        ->assertStatus(200)
        ->assertSee($content);
})->with([
    ['transport', 'Armada yang tepat untuk setiap perjalanan'],
    ['tour', 'Tour halal untuk perjalanan yang lebih terarah'],
    ['umroh', 'Persiapan yang baik untuk perjalanan ibadah yang lebih tenang.'],
    ['blog', 'Blog'],
    ['visa', 'Persiapan dokumen yang lebih jelas sebelum keberangkatan.'],
    ['shop', 'Shop Index'],
    ['gallery', 'Gallery Page'],
    ['testimonials', 'Testimonials Page'],
    ['kontak', 'Mulai perjalanan Anda dengan rencana yang lebih jelas.'],
]);
