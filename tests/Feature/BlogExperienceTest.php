<?php

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

function createBlogExperienceCategory(string $idName, string $idSlug): PostCategory
{
    return PostCategory::create([
        'name' => [
            'id' => $idName,
            'en' => $idName.' EN',
            'ms' => $idName.' MS',
        ],
        'slug' => [
            'id' => $idSlug,
            'en' => $idSlug.'-en',
            'ms' => $idSlug.'-ms',
        ],
        'description' => [
            'id' => 'Deskripsi '.$idName,
            'en' => 'Description '.$idName,
            'ms' => 'Penerangan '.$idName,
        ],
    ]);
}

function createBlogExperiencePost(
    User $author,
    PostCategory $category,
    string $title,
    string $slug,
    array $attributes = [],
): Post {
    return Post::create(array_merge([
        'post_category_id' => $category->id,
        'user_id' => $author->id,
        'title' => [
            'id' => $title,
            'en' => $title.' EN',
            'ms' => $title.' MS',
        ],
        'slug' => [
            'id' => $slug,
            'en' => $slug.'-en',
            'ms' => $slug.'-ms',
        ],
        'excerpt' => [
            'id' => 'Ringkasan '.$title,
            'en' => 'Summary '.$title,
            'ms' => 'Ringkasan '.$title,
        ],
        'content' => [
            'id' => '<h2>Persiapan Utama</h2><p>Isi panduan perjalanan yang jelas.</p><h3>Perlengkapan</h3><p>Daftar perlengkapan perjalanan.</p>',
            'en' => '<h2>Main Preparation</h2><p>Clear travel guidance.</p><h3>Equipment</h3><p>Travel equipment list.</p>',
            'ms' => '<h2>Persiapan Utama</h2><p>Panduan perjalanan yang jelas.</p><h3>Peralatan</h3><p>Senarai peralatan perjalanan.</p>',
        ],
        'status' => 'published',
        'published_at' => now()->subDay(),
    ], $attributes));
}

it('renders an editorial index with a featured post and only currently published content', function () {
    $author = User::factory()->create(['name' => 'Editor Baharsyah']);
    $guides = createBlogExperienceCategory('Panduan Wisata', 'panduan-wisata');
    $emptyCategory = createBlogExperienceCategory('Kategori Kosong', 'kategori-kosong');

    createBlogExperiencePost($author, $guides, 'Artikel Terbaru Unggulan', 'artikel-terbaru', [
        'published_at' => now()->subHour(),
    ]);
    createBlogExperiencePost($author, $guides, 'Artikel Pendamping', 'artikel-pendamping', [
        'published_at' => now()->subDays(2),
    ]);
    createBlogExperiencePost($author, $guides, 'Artikel Draft Rahasia', 'artikel-draft', [
        'status' => 'draft',
    ]);
    createBlogExperiencePost($author, $guides, 'Artikel Terjadwal', 'artikel-terjadwal', [
        'published_at' => now()->addDay(),
    ]);

    get('/id/blog')
        ->assertSuccessful()
        ->assertSee('Panduan utama untuk dibaca')
        ->assertSee('Artikel Terbaru Unggulan')
        ->assertSee('Artikel Pendamping')
        ->assertSee('2</span>', false)
        ->assertDontSee('Artikel Draft Rahasia')
        ->assertDontSee('Artikel Terjadwal')
        ->assertDontSee($emptyCategory->name)
        ->assertSee('name="q"', false)
        ->assertSee('og:type" content="website"', false);
});

it('combines search and localized category filters while preserving recoverable state', function () {
    $author = User::factory()->create();
    $guides = createBlogExperienceCategory('Panduan Wisata', 'panduan-wisata');
    $news = createBlogExperienceCategory('Berita', 'berita');

    createBlogExperiencePost($author, $guides, 'Panduan Orangutan Kalimantan', 'panduan-orangutan');
    createBlogExperiencePost($author, $guides, 'Panduan Pantai Derawan', 'panduan-derawan');
    createBlogExperiencePost($author, $news, 'Berita Orangutan Terbaru', 'berita-orangutan');

    get('/id/blog?q=orangutan&category=panduan-wisata')
        ->assertSuccessful()
        ->assertSee('Panduan Orangutan Kalimantan')
        ->assertDontSee('Panduan Pantai Derawan')
        ->assertDontSee('Berita Orangutan Terbaru')
        ->assertSee('value="orangutan"', false)
        ->assertSee('value="panduan-wisata"', false)
        ->assertSee('Pencarian: “orangutan”')
        ->assertSee('Kategori: Panduan Wisata')
        ->assertSee('Hapus filter');
});

it('renders article metadata, reading utilities, resilient media, and relevant posts', function () {
    $author = User::factory()->create(['name' => 'Admin Baharsyah']);
    $guides = createBlogExperienceCategory('Panduan Wisata', 'panduan-wisata');
    $news = createBlogExperienceCategory('Berita', 'berita');
    $post = createBlogExperiencePost($author, $guides, 'Panduan Pedalaman Kalimantan', 'panduan-pedalaman', [
        'cover_image' => url('/storage/posts/covers/missing-cover.webp'),
        'published_at' => now()->subHour(),
    ]);
    createBlogExperiencePost($author, $guides, 'Panduan Satu Kategori', 'panduan-satu-kategori');
    createBlogExperiencePost($author, $news, 'Berita Sebagai Cadangan', 'berita-cadangan');

    $response = get('/id/blog/'.$post->slug)
        ->assertSuccessful()
        ->assertSee('<title>Panduan Pedalaman Kalimantan |', false)
        ->assertSee('og:type" content="article"', false)
        ->assertSee('twitter:card" content="summary"', false)
        ->assertSee('"@type":"Article"', false)
        ->assertSee('1 menit baca')
        ->assertSee('Dalam artikel ini')
        ->assertSee('href="#persiapan-utama"', false)
        ->assertSee('id="persiapan-utama"', false)
        ->assertSee('Bagikan artikel')
        ->assertSee('Gambar artikel belum tersedia')
        ->assertSee('Panduan Satu Kategori')
        ->assertSee('Berita Sebagai Cadangan');

    $html = $response->getContent();

    expect(strpos($html, 'Panduan Satu Kategori'))
        ->toBeLessThan(strpos($html, 'Berita Sebagai Cadangan'));
});

it('redirects fallback slugs to the canonical localized article URL', function () {
    $author = User::factory()->create();
    $category = createBlogExperienceCategory('Destinasi', 'destinasi');
    $post = createBlogExperiencePost($author, $category, 'Panduan Danau', 'panduan-danau');

    get('/en/blog/'.$post->getTranslation('slug', 'id'))
        ->assertRedirect(route('blog.show', [
            'locale' => 'en',
            'post' => $post->getTranslation('slug', 'en'),
        ]))
        ->assertStatus(301);
});

it('uses the Indonesian post slug when localized slugs are missing', function () {
    $author = User::factory()->create();
    $category = createBlogExperienceCategory('Destinasi', 'destinasi');
    $post = createBlogExperiencePost($author, $category, 'Panduan Danau', 'panduan-danau');
    $post->forgetTranslations('slug')
        ->setTranslation('slug', 'id', 'panduan-danau')
        ->save();

    get('/en/blog/panduan-danau')
        ->assertSuccessful()
        ->assertSee('<link rel="alternate" hreflang="ms" href="'.route('blog.show', [
            'locale' => 'ms',
            'post' => 'panduan-danau',
        ]).'">', false);
});

it('does not expose a future article through its direct URL', function () {
    $author = User::factory()->create();
    $category = createBlogExperienceCategory('Destinasi', 'destinasi');
    $post = createBlogExperiencePost($author, $category, 'Artikel Masa Depan', 'artikel-masa-depan', [
        'published_at' => now()->addDay(),
    ]);

    get('/id/blog/'.$post->slug)->assertNotFound();
});

it('localizes the editorial interface', function (string $locale, string $heading, string $searchLabel) {
    get("/{$locale}/blog")
        ->assertSuccessful()
        ->assertSee($heading)
        ->assertSee('aria-label="'.$searchLabel.'"', false);
})->with([
    'Indonesian' => ['id', 'Artikel & Berita Terbaru', 'Cari artikel'],
    'English' => ['en', 'Latest Articles & News', 'Search articles'],
    'Malay' => ['ms', 'Artikel & Berita Terkini', 'Cari artikel'],
]);
