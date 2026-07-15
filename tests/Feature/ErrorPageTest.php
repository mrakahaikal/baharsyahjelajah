<?php

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tour;
use App\Models\TourPackage;
use App\Models\User;
use App\Support\ErrorPageRecommendations;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;

uses(LazilyRefreshDatabase::class);

it('renders the branded 4xx error page for missing localized pages', function () {
    config(['app.debug' => false]);

    $tour = new Tour([
        'name' => ['id' => 'Jelajah Labuan Bajo', 'en' => 'Explore Labuan Bajo'],
        'slug' => ['id' => 'jelajah-labuan-bajo', 'en' => 'explore-labuan-bajo'],
        'is_active' => true,
    ]);
    $tour->id = 10;

    $package = new TourPackage([
        'name' => ['id' => 'Paket Komodo Ringkas', 'en' => 'Komodo Short Trip'],
        'slug' => ['id' => 'paket-komodo-ringkas', 'en' => 'komodo-short-trip'],
        'duration_days' => 3,
        'duration_nights' => 2,
    ]);
    $package->id = 20;
    $package->setRelation('tour', $tour);
    $package->setRelation('media', new Collection);

    $post = new Post([
        'title' => ['id' => 'Panduan Pertama ke Komodo', 'en' => 'First Guide to Komodo'],
        'slug' => ['id' => 'panduan-pertama-ke-komodo', 'en' => 'first-guide-to-komodo'],
        'excerpt' => ['id' => 'Persiapan singkat sebelum berangkat.', 'en' => 'A short guide before departure.'],
        'content' => ['id' => 'Konten panduan.', 'en' => 'Guide content.'],
        'status' => 'published',
        'published_at' => Carbon::parse('2026-07-13'),
    ]);
    $post->id = 30;
    $post->created_at = Carbon::parse('2026-07-13');
    $post->setRelation('author', null);
    $post->setRelation('category', null);

    $recommendations = Mockery::mock(ErrorPageRecommendations::class);
    $recommendations->shouldReceive('get')->once()->andReturn([
        'packages' => new Collection([$package]),
        'posts' => new Collection([$post]),
    ]);
    $this->app->instance(ErrorPageRecommendations::class, $recommendations);

    get('/id/halaman-tidak-ada')
        ->assertNotFound()
        ->assertSee('Baharsyah Jelajah')
        ->assertSee('Rute ini tidak ada, tetapi perjalanan Anda bisa berlanjut.')
        ->assertSee('Paket Komodo Ringkas')
        ->assertSee('Panduan Pertama ke Komodo')
        ->assertSee('href="'.route('home', ['locale' => 'id']).'"', false)
        ->assertSee('href="'.route('tour.index', ['locale' => 'id']).'"', false)
        ->assertSee('href="'.route('blog.index', ['locale' => 'id']).'"', false)
        ->assertSee('href="'.route('contact.index', ['locale' => 'id']).'"', false)
        ->assertSee('href="'.route('tour.package.show', [
            'locale' => 'id',
            'tour' => 'jelajah-labuan-bajo',
            'package' => 'paket-komodo-ringkas',
        ]).'"', false)
        ->assertSee('href="'.route('blog.show', [
            'locale' => 'id',
            'post' => 'panduan-pertama-ke-komodo',
        ]).'"', false);
});

it('falls back to Indonesian recovery links for invalid locale errors', function () {
    config(['app.debug' => false]);

    get('/fr/transport')
        ->assertNotFound()
        ->assertSee('Rute ini tidak ada, tetapi perjalanan Anda bisa berlanjut.')
        ->assertSee('href="'.route('home', ['locale' => 'id']).'"', false)
        ->assertSee('href="'.route('contact.index', ['locale' => 'id']).'"', false);
});

it('uses the requested locale and remains useful without recommendations', function () {
    config(['app.debug' => false]);

    $recommendations = Mockery::mock(ErrorPageRecommendations::class);
    $recommendations->shouldReceive('get')->once()->andReturn([
        'packages' => new Collection,
        'posts' => new Collection,
    ]);
    $this->app->instance(ErrorPageRecommendations::class, $recommendations);

    get('/en/missing-page')
        ->assertNotFound()
        ->assertSee('This route is missing, but your journey can continue.')
        ->assertSee('Start again from the main catalog')
        ->assertSee('href="'.route('home', ['locale' => 'en']).'"', false)
        ->assertSee('href="'.route('tour.index', ['locale' => 'en']).'"', false)
        ->assertSee('href="'.route('blog.index', ['locale' => 'en']).'"', false);
});

it('only recommends packages from active tours and currently published posts', function () {
    $activeTour = Tour::query()->create([
        'name' => ['id' => 'Tour Aktif'],
        'slug' => ['id' => 'tour-aktif-rekomendasi'],
        'tour_type' => 'domestic',
        'currency' => 'IDR',
        'is_active' => true,
        'is_featured' => false,
    ]);
    $inactiveTour = Tour::query()->create([
        'name' => ['id' => 'Tour Nonaktif'],
        'slug' => ['id' => 'tour-nonaktif-rekomendasi'],
        'tour_type' => 'domestic',
        'currency' => 'IDR',
        'is_active' => false,
        'is_featured' => false,
    ]);

    $activePackage = TourPackage::query()->create([
        'tour_id' => $activeTour->id,
        'name' => ['id' => 'Paket Aktif Rekomendasi'],
        'slug' => ['id' => 'paket-aktif-rekomendasi'],
        'duration_days' => 3,
        'duration_nights' => 2,
    ]);
    $inactivePackage = TourPackage::query()->create([
        'tour_id' => $inactiveTour->id,
        'name' => ['id' => 'Paket Nonaktif Rekomendasi'],
        'slug' => ['id' => 'paket-nonaktif-rekomendasi'],
        'duration_days' => 2,
        'duration_nights' => 1,
    ]);

    $author = User::factory()->create();
    $category = PostCategory::query()->create([
        'name' => ['id' => 'Panduan Error'],
        'slug' => ['id' => 'panduan-error'],
    ]);
    $publishedPost = Post::query()->create([
        'post_category_id' => $category->id,
        'user_id' => $author->id,
        'title' => ['id' => 'Artikel Terbit Rekomendasi'],
        'slug' => ['id' => 'artikel-terbit-rekomendasi'],
        'content' => ['id' => 'Konten artikel terbit.'],
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $draftPost = Post::query()->create([
        'post_category_id' => $category->id,
        'user_id' => $author->id,
        'title' => ['id' => 'Artikel Draft Rekomendasi'],
        'slug' => ['id' => 'artikel-draft-rekomendasi'],
        'content' => ['id' => 'Konten artikel draft.'],
        'status' => 'draft',
        'published_at' => now()->addYears(2),
    ]);

    $recommendations = app(ErrorPageRecommendations::class)->get();

    expect($recommendations['packages']->modelKeys())
        ->toContain($activePackage->id)
        ->not->toContain($inactivePackage->id)
        ->and($recommendations['posts']->modelKeys())
        ->toContain($publishedPost->id)
        ->not->toContain($draftPost->id);
});

it('renders the branded 5xx error page with recovery actions', function () {
    config(['app.debug' => false]);

    Route::get('/id/__server-error-preview', fn () => abort(500));

    get('/id/__server-error-preview')
        ->assertStatus(500)
        ->assertSee('Ada kendala sementara di sisi sistem.')
        ->assertSee('Muat ulang halaman')
        ->assertDontSee('Paket yang mungkin Anda cari')
        ->assertSee('href="'.route('contact.index', ['locale' => 'id']).'"', false);
});
