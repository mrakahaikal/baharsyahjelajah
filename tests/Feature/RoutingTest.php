<?php

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Testimonial;
use App\Models\User;
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

it('sets the application locale based on the URL prefix', function (string $locale) {
    get("/{$locale}")
        ->assertStatus(200)
        ->assertSee("<html lang=\"{$locale}\"", false);
})->with(['id', 'ms', 'en']);

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
        ->assertSee('class="fixed inset-x-0 top-3', false)
        ->assertSee('href="'.route('contact.index', ['locale' => 'id']).'"', false)
        ->assertSee('Hubungi Kami')
        ->assertDontSee('Book Trip')
        ->assertSee('aria-label="Pilih bahasa"', false)
        ->assertSee('aria-label="Pilih mata uang"', false)
        ->assertSee('aria-controls="mobile-navigation"', false)
        ->assertSee('aria-controls="tour-mega-menu"', false)
        ->assertSee('href="#main-content"', false)
        ->assertSee('method="GET" action="'.route('tour.index', ['locale' => 'id']).'"', false)
        ->assertSee('name="destination"', false)
        ->assertSee('name="type"', false)
        ->assertSee('name="pax"', false)
        ->assertSee('lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)_minmax(0,0.85fr)_minmax(10rem,auto)]', false)
        ->assertSee('whitespace-nowrap rounded-2xl bg-slate-900', false)
        ->assertSee('lg:grid-cols-[1.1fr_0.9fr] lg:items-start', false)
        ->assertSee('fetchpriority="high"', false)
        ->assertSee('width="1800" height="1100"', false)
        ->assertSee('aria-label="Testimoni sebelumnya"', false)
        ->assertSee('aria-label="Testimoni berikutnya"', false)
        ->assertSee('Cari Tour Pilihan')
        ->assertSee('Rancang perjalanan')
        ->assertSee('Jelajahi Paket Tour')
        ->assertSee('Baca Panduan Destinasi')
        ->assertSee('Diskusikan Itinerary')
        ->assertSee('Mulai diskusi')
        ->assertSee('Baharsyah Jelajah')
        ->assertSee('Panduan Tour Kalimantan')
        ->assertSee('Butuh bantuan memilih perjalanan?')
        ->assertSee('Paket Tour')
        ->assertSee('Kontak')
        ->assertDontSee('Fokus MVP')
        ->assertDontSee('Home sekarang')
        ->assertDontSee('Paket Umroh')
        ->assertDontSee('Sewa Kendaraan')
        ->assertDontSee('Layanan Visa')
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

it('applies floating header spacing to the first section on standard pages only', function () {
    get('/id/tour')
        ->assertOk()
        ->assertSee('class="flex-grow [&amp;&gt;*:first-child]:pt-28 lg:[&amp;&gt;*:first-child]:pt-32"', false);

    get('/id')
        ->assertOk()
        ->assertSee('class="flex-grow "', false)
        ->assertDontSee('[&amp;&gt;*:first-child]:pt-28', false);
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
    ['transport', 'Transport Index'],
    ['tour', 'Paket Tour Wisata Halal'],
    ['umroh', 'Umroh Index'],
    ['blog', 'Blog'],
    ['visa', 'Visa Page'],
    ['shop', 'Shop Index'],
    ['gallery', 'Gallery Page'],
    ['testimonials', 'Testimonials Page'],
    ['kontak', 'Hubungi Tim Baharsyah Jelajah'],
]);
