<?php

use App\Models\CurrencyRate;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('renders the tour index as an editorial catalog and hides inactive tours', function () {
    $category = TourCategory::create([
        'name' => ['en' => 'Nature Tour', 'id' => 'Wisata Alam', 'ms' => 'Pelancongan Alam'],
        'slug' => ['en' => 'nature-tour', 'id' => 'wisata-alam', 'ms' => 'pelancongan-alam'],
        'icon' => 'heroicon-o-sparkles',
        'sort_order' => 1,
    ]);

    $activeTour = new Tour([
        'name' => ['en' => 'River Forest Escape', 'id' => 'Jelajah Sungai Hutan', 'ms' => 'Jelajah Sungai Hutan'],
        'slug' => ['en' => 'river-forest-escape', 'id' => 'jelajah-sungai-hutan', 'ms' => 'jelajah-sungai-hutan'],
        'description' => ['en' => 'A calm nature trip.', 'id' => 'Perjalanan alam yang tenang.', 'ms' => 'Perjalanan alam yang tenang.'],
        'tour_type' => 'domestic',
        'duration_days' => 2,
        'duration_nights' => 1,
        'price' => 1500000,
        'currency' => 'IDR',
        'difficulty' => 'easy',
        'max_pax' => 8,
        'is_active' => true,
    ]);
    $activeTour->category_id = $category->id;
    $activeTour->save();

    $inactiveTour = new Tour([
        'name' => ['en' => 'Hidden Draft Tour', 'id' => 'Tour Draft Tersembunyi', 'ms' => 'Tour Draft Tersembunyi'],
        'slug' => ['en' => 'hidden-draft-tour', 'id' => 'tour-draft-tersembunyi', 'ms' => 'tour-draft-tersembunyi'],
        'tour_type' => 'domestic',
        'duration_days' => 1,
        'duration_nights' => 0,
        'price' => 500000,
        'currency' => 'IDR',
        'difficulty' => 'easy',
        'max_pax' => 4,
        'is_active' => false,
    ]);
    $inactiveTour->category_id = $category->id;
    $inactiveTour->save();

    get('/id/tour')
        ->assertSuccessful()
        ->assertSee('Katalog Perjalanan')
        ->assertSee('Paket aktif')
        ->assertSee('Jelajah Sungai Hutan')
        ->assertSee('Perjalanan alam yang tenang.')
        ->assertSee('Buat Custom Trip')
        ->assertDontSee('Tour Draft Tersembunyi');
});

it('renders the tour show page with correct database data and no emojis', function () {
    // 1. Create category and tour
    $category = TourCategory::create([
        'name' => ['en' => 'Adventure Tour', 'id' => 'Tur Petualangan', 'ms' => 'Tur Petualangan'],
        'slug' => ['en' => 'adventure-tour', 'id' => 'tur-petualangan', 'ms' => 'tur-petualangan'],
        'icon' => 'heroicon-o-map',
        'sort_order' => 1,
    ]);

    $tour = new Tour([
        'name' => ['en' => 'Amazing Jungle Hike', 'id' => 'Pendakian Hutan Menakjubkan', 'ms' => 'Pendakian Hutan Menakjubkan'],
        'slug' => ['en' => 'amazing-jungle-hike', 'id' => 'pendakian-hutan-menakjubkan', 'ms' => 'pendakian-hutan-menakjubkan'],
        'description' => ['en' => 'Explore the deep jungle.', 'id' => 'Jelajahi hutan belantara.', 'ms' => 'Jelajahi hutan belantara.'],
        'highlights' => ['en' => "Jungle view\nWild animals", 'id' => "Pemandangan hutan\nHewan liar", 'ms' => "Pemandangan hutan\nHewan liar"],
        'tour_type' => 'domestic',
        'duration_days' => 3,
        'duration_nights' => 2,
        'price' => 2500000,
        'currency' => 'IDR',
        'difficulty' => 'moderate',
        'max_pax' => 10,
        'is_active' => true,
        'is_featured' => true,
    ]);
    $tour->category_id = $category->id;
    $tour->save();

    // 2. Create includes
    $tour->includes()->create([
        'item' => ['en' => 'Professional Guide', 'id' => 'Pemandu Profesional', 'ms' => 'Pemandu Profesional'],
        'type' => 'include',
        'sort_order' => 1,
    ]);
    $tour->includes()->create([
        'item' => ['en' => 'Flights', 'id' => 'Tiket Pesawat', 'ms' => 'Tiket Pesawat'],
        'type' => 'exclude',
        'sort_order' => 2,
    ]);

    // 3. Create itineraries
    $tour->itineraries()->create([
        'day_number' => 1,
        'title' => ['en' => 'Jungle Entry', 'id' => 'Masuk Hutan', 'ms' => 'Masuk Hutan'],
        'description' => ['en' => 'Walk into the jungle.', 'id' => 'Berjalan masuk hutan.', 'ms' => 'Berjalan masuk hutan.'],
        'meals_included' => ['en' => 'breakfast, lunch', 'id' => 'breakfast, lunch', 'ms' => 'breakfast, lunch'],
        'accommodation' => 'Camp A',
    ]);

    $postCategory = PostCategory::create([
        'name' => ['en' => 'Travel Guide', 'id' => 'Panduan Perjalanan', 'ms' => 'Panduan Perjalanan'],
        'slug' => ['en' => 'travel-guide', 'id' => 'panduan-perjalanan', 'ms' => 'panduan-perjalanan'],
    ]);

    Post::create([
        'post_category_id' => $postCategory->id,
        'user_id' => User::factory()->create()->id,
        'title' => ['en' => 'Jungle Travel Guide', 'id' => 'Panduan Wisata Hutan', 'ms' => 'Panduan Wisata Hutan'],
        'slug' => ['en' => 'jungle-travel-guide', 'id' => 'panduan-wisata-hutan', 'ms' => 'panduan-wisata-hutan'],
        'excerpt' => ['en' => 'Guide for jungle trips.', 'id' => 'Panduan untuk wisata hutan.', 'ms' => 'Panduan untuk wisata hutan.'],
        'content' => ['en' => '<p>Prepare well.</p>', 'id' => '<p>Persiapkan perjalanan.</p>', 'ms' => '<p>Persiapkan perjalanan.</p>'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    // 4. Request the show page in Indonesian
    $response = get('/id/tour/pendakian-hutan-menakjubkan');
    $response->assertSuccessful()
        ->assertSee('Pendakian Hutan Menakjubkan')
        ->assertSee('Jelajahi hutan belantara.')
        ->assertSee('Tur Petualangan')
        ->assertSee('Sedang') // difficulty 'moderate' translated to Indonesian
        ->assertSee('Maks. 10 Pax')
        ->assertSee('Domestik') // tour_type 'domestic' default
        ->assertSee('Pemandu Profesional')
        ->assertSee('Tiket Pesawat')
        ->assertSee('Masuk Hutan')
        ->assertSee('Sarapan') // meals_included 'breakfast' mapped to 'Sarapan' (no emoji)
        ->assertSee('Camp A')
        ->assertSee('Konsultasi Tour Ini')
        ->assertSee(route('contact.index', ['locale' => 'id', 'tour' => 'pendakian-hutan-menakjubkan']), false)
        ->assertSee('Artikel panduan terkait')
        ->assertSee('Panduan Wisata Hutan')
        ->assertSee('application/ld+json')
        ->assertDontSee('🏨')
        ->assertDontSee('🌅')
        ->assertDontSee('☀️')
        ->assertDontSee('🌙');

    // 5. Request the show page in English
    get('/en/tour/amazing-jungle-hike')
        ->assertSuccessful()
        ->assertSee('Amazing Jungle Hike')
        ->assertSee('Explore the deep jungle.')
        ->assertSee('Adventure Tour')
        ->assertSee('Moderate')
        ->assertSee('Consult This Tour')
        ->assertSee('https://wa.me/')
        ->assertDontSee('🏨');
});

it('renders a USD-based tour converted to IDR and MYR correctly', function () {
    // 1. Seed currency rates (relative to IDR)
    CurrencyRate::updateRate('USD', 0.00006250); // 1 USD = 16000 IDR
    CurrencyRate::updateRate('MYR', 0.00029200); // 1 MYR = 3424.65 IDR

    // 2. Create category and tour in USD
    $category = TourCategory::create([
        'name' => ['en' => 'Adventure Tour', 'id' => 'Tur Petualangan', 'ms' => 'Tur Petualangan'],
        'slug' => ['en' => 'adventure-tour', 'id' => 'tur-petualangan', 'ms' => 'tur-petualangan'],
        'icon' => 'heroicon-o-map',
        'sort_order' => 1,
    ]);

    $tour = new Tour([
        'name' => ['en' => 'USD Budget Tour', 'id' => 'Tur Murah USD', 'ms' => 'Tur Murah USD'],
        'slug' => ['en' => 'usd-budget-tour', 'id' => 'tur-murah-usd', 'ms' => 'tur-murah-usd'],
        'description' => ['en' => 'USD budget tour.', 'id' => 'Tur murah USD.', 'ms' => 'Tur murah USD.'],
        'tour_type' => 'domestic',
        'duration_days' => 3,
        'duration_nights' => 2,
        'price' => 100, // 100 USD
        'currency' => 'USD',
        'difficulty' => 'moderate',
        'max_pax' => 10,
        'is_active' => true,
    ]);
    $tour->category_id = $category->id;
    $tour->save();

    // 3. Request the page in IDR session (default currency)
    // 100 USD -> 100 / 0.0000625 = 1,600,000 IDR -> Rp 1.600.000
    get('/id/tour/tur-murah-usd')
        ->assertStatus(200)
        ->assertSee('Rp 1.600.000');

    // 4. Request page in MYR session using withSession
    // 100 USD = 1,600,000 IDR * 0.000292 = 467.20 MYR -> RM 467.20
    $this->withSession(['app_currency' => 'MYR'])
        ->get('/id/tour/tur-murah-usd')
        ->assertStatus(200)
        ->assertSee('RM 467.20');
});
