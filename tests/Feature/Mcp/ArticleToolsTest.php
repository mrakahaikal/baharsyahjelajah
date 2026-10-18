<?php

use App\Mcp\Servers\BaharsyahServer;
use App\Mcp\Tools\Articles\CreateArticleTool;
use App\Mcp\Tools\Articles\DeleteArticleTool;
use App\Mcp\Tools\Articles\GetArticleTool;
use App\Mcp\Tools\Articles\ListArticlesTool;
use App\Mcp\Tools\Articles\PublishArticleTool;
use App\Mcp\Tools\Articles\UpdateArticleTool;
use App\Models\Destination;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Mcp\Server\Transport\FakeTransporter;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->author = User::factory()->create([
        'name' => 'Raka Travel Writer',
        'email' => 'writer@baharsyahjelajah.com',
    ]);

    $this->category = PostCategory::create([
        'name' => ['id' => 'Destinasi Eksotis', 'en' => 'Exotic Destinations', 'ms' => 'Destinasi Eksotik'],
        'slug' => ['id' => 'destinasi-eksotis', 'en' => 'exotic-destinations', 'ms' => 'destinasi-eksotik'],
        'description' => ['id' => 'Koleksi tempat wisata unik.', 'en' => 'Unique travel spots.', 'ms' => 'Tempat pelancongan unik.'],
    ]);

    $this->destination = Destination::create([
        'name' => ['id' => 'Pulau Kakaban', 'en' => 'Kakaban Island'],
        'slug' => 'pulau-kakaban',
        'description' => ['id' => 'Danau ubur-ubur tanpa sengat'],
        'is_active' => true,
        'is_featured' => true,
    ]);
});

test('article tools are properly registered in baharsyah server', function () {
    $server = app(BaharsyahServer::class, ['transport' => new FakeTransporter]);
    $reflection = new ReflectionClass($server);
    $tools = $reflection->getProperty('tools')->getValue($server);

    expect($tools)->toContain(
        ListArticlesTool::class,
        GetArticleTool::class,
        CreateArticleTool::class,
        UpdateArticleTool::class,
        PublishArticleTool::class,
        DeleteArticleTool::class,
    );
});

test('list articles tool returns articles with category, author, destinations, and pagination', function () {
    $post = Post::create([
        'user_id' => $this->author->id,
        'post_category_id' => $this->category->id,
        'title' => ['id' => 'Sensasi Berenang Bersama Ubur-ubur', 'en' => 'Swimming with Stingless Jellyfish', 'ms' => 'Berenang Bersama Obor-obor'],
        'slug' => ['id' => 'sensasi-berenang-ubur-ubur', 'en' => 'swimming-with-jellyfish', 'ms' => 'berenang-obor-obor'],
        'excerpt' => ['id' => 'Panduan lengkap Kakaban', 'en' => 'Kakaban guide', 'ms' => 'Panduan Kakaban'],
        'content' => ['id' => '<p>Danau Kakaban sangat mempesona...</p>', 'en' => '<p>Kakaban lake is breathtaking...</p>', 'ms' => '<p>Tasik Kakaban sungguh mempesona...</p>'],
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $post->destinations()->attach($this->destination->id);

    // List in Indonesian
    BaharsyahServer::tool(ListArticlesTool::class, ['locale' => 'id'])
        ->assertSee('Sensasi Berenang Bersama Ubur-ubur')
        ->assertSee('Destinasi Eksotis')
        ->assertSee('Pulau Kakaban');

    // List in English
    BaharsyahServer::tool(ListArticlesTool::class, ['locale' => 'en'])
        ->assertSee('Swimming with Stingless Jellyfish')
        ->assertSee('Exotic Destinations');
});

test('list articles tool filters by status, search keyword, and category', function () {
    $publishedPost = Post::create([
        'user_id' => $this->author->id,
        'post_category_id' => $this->category->id,
        'title' => ['id' => 'Wisata Hutan Mangrove Balikpapan', 'en' => 'Balikpapan Mangrove Forest', 'ms' => 'Hutan Bakau Balikpapan'],
        'slug' => ['id' => 'wisata-hutan-mangrove-balikpapan', 'en' => 'balikpapan-mangrove-forest', 'ms' => 'hutan-bakau-balikpapan'],
        'content' => ['id' => '<p>Keindahan hutan mangrove alami di Kalimantan Timur.</p>'],
        'status' => 'published',
        'published_at' => now()->subHours(5),
    ]);

    $draftPost = Post::create([
        'user_id' => $this->author->id,
        'post_category_id' => $this->category->id,
        'title' => ['id' => 'Draf Ekspedisi Pegunungan Meratus', 'en' => 'Draft Meratus Mountains', 'ms' => 'Draf Pegunungan Meratus'],
        'slug' => ['id' => 'draf-ekspedisi-pegunungan-meratus', 'en' => 'draft-meratus-mountains', 'ms' => 'draf-pegunungan-meratus'],
        'content' => ['id' => '<p>Rencana rute trekking rimba.</p>'],
        'status' => 'draft',
    ]);

    // Status filter: draft
    BaharsyahServer::tool(ListArticlesTool::class, ['status' => 'draft'])
        ->assertSee('Draf Ekspedisi Pegunungan Meratus')
        ->assertDontSee('Wisata Hutan Mangrove Balikpapan');

    // Search keyword
    BaharsyahServer::tool(ListArticlesTool::class, ['search' => 'Mangrove'])
        ->assertSee('Wisata Hutan Mangrove Balikpapan')
        ->assertDontSee('Draf Ekspedisi Pegunungan Meratus');
});

test('get article tool retrieves full details by id or slug', function () {
    $post = Post::create([
        'user_id' => $this->author->id,
        'post_category_id' => $this->category->id,
        'title' => ['id' => 'Eksplorasi Goa Purba Sangkulirang', 'en' => 'Exploring Ancient Sangkulirang Caves', 'ms' => 'Eksplorasi Gua Sangkulirang'],
        'slug' => ['id' => 'eksplorasi-goa-sangkulirang', 'en' => 'exploring-sangkulirang-caves', 'ms' => 'eksplorasi-gua-sangkulirang'],
        'excerpt' => ['id' => 'Peninggalan telapak tangan purba ribuan tahun lalu.'],
        'content' => ['id' => '<p>Ekspedisi menyusuri karst Sangkulirang penuh misteri.</p>'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    // Get by ID
    BaharsyahServer::tool(GetArticleTool::class, ['id' => $post->id])
        ->assertSee('Eksplorasi Goa Purba Sangkulirang')
        ->assertSee('Destinasi Eksotis');

    // Get by English slug
    BaharsyahServer::tool(GetArticleTool::class, ['slug' => 'exploring-sangkulirang-caves', 'locale' => 'en'])
        ->assertSee('Exploring Ancient Sangkulirang Caves');
});

test('create article tool creates article with string inputs and auto-generates slug and excerpt', function () {
    BaharsyahServer::tool(CreateArticleTool::class, [
        'title' => 'Panduan Menyelam di Maratua',
        'content' => '<p>Pulau Maratua menawarkan terumbu karang luar biasa indah dan penyu raksasa.</p>',
        'category_id' => $this->category->id,
        'destination_slugs' => ['pulau-kakaban'],
        'status' => 'published',
    ])
        ->assertSee('Article created successfully.')
        ->assertSee('panduan-menyelam-di-maratua');

    $post = Post::whereJsonContains('slug->id', 'panduan-menyelam-di-maratua')->first();
    expect($post)->not->toBeNull()
        ->and($post->status)->toBe('published')
        ->and($post->published_at)->not->toBeNull()
        ->and($post->destinations()->pluck('slug')->all())->toContain('pulau-kakaban');
});

test('create article tool supports multilingual object input', function () {
    BaharsyahServer::tool(CreateArticleTool::class, [
        'title' => [
            'id' => 'Kuliner Kepiting Asap Tarakan',
            'en' => 'Tarakan Smoked Crab Culinary',
            'ms' => 'Kulinari Ketam Asap Tarakan',
        ],
        'content' => [
            'id' => '<p>Kepiting soka dengan bumbu khas.</p>',
            'en' => '<p>Soft-shell crab with authentic spice.</p>',
            'ms' => '<p>Ketam soka berperisa asli.</p>',
        ],
        'category_slug' => 'destinasi-eksotis',
        'status' => 'draft',
    ])
        ->assertSee('Article created successfully.')
        ->assertSee('kuliner-kepiting-asap-tarakan');

    $post = Post::whereJsonContains('slug->id', 'kuliner-kepiting-asap-tarakan')->first();
    expect($post)->not->toBeNull()
        ->and($post->getTranslation('title', 'en', false))->toBe('Tarakan Smoked Crab Culinary')
        ->and($post->status)->toBe('draft');
});

test('update article tool updates article fields and syncs destinations', function () {
    $post = Post::create([
        'user_id' => $this->author->id,
        'post_category_id' => $this->category->id,
        'title' => ['id' => 'Judul Lama', 'en' => 'Old Title'],
        'slug' => ['id' => 'judul-lama', 'en' => 'old-title'],
        'content' => ['id' => '<p>Konten awal.</p>'],
        'status' => 'draft',
    ]);

    BaharsyahServer::tool(UpdateArticleTool::class, [
        'id' => $post->id,
        'title' => 'Judul Baru Diperbarui',
        'content' => '<p>Konten yang baru saja direvisi secara lengkap.</p>',
        'status' => 'published',
        'destination_slugs' => ['pulau-kakaban'],
    ])
        ->assertSee('Article updated successfully.')
        ->assertSee('Judul Baru Diperbarui');

    $post->refresh();
    expect($post->getTranslation('title', 'id', false))->toBe('Judul Baru Diperbarui')
        ->and($post->status)->toBe('published')
        ->and($post->destinations()->count())->toBe(1);
});

test('publish article tool handles publish, schedule, unpublish, and archive workflows', function () {
    $post = Post::create([
        'user_id' => $this->author->id,
        'post_category_id' => $this->category->id,
        'title' => ['id' => 'Status Workflow Test'],
        'slug' => ['id' => 'status-workflow-test'],
        'content' => ['id' => '<p>Testing workflows.</p>'],
        'status' => 'draft',
    ]);

    // 1. Publish
    BaharsyahServer::tool(PublishArticleTool::class, [
        'id' => $post->id,
        'action' => 'publish',
    ])
        ->assertSee('Article published successfully.');

    expect($post->fresh()->status)->toBe('published')
        ->and($post->fresh()->published_at)->not->toBeNull();

    // 2. Schedule for next month
    $futureDate = Carbon::now()->addMonth()->startOfHour();
    BaharsyahServer::tool(PublishArticleTool::class, [
        'id' => $post->id,
        'action' => 'publish',
        'published_at' => $futureDate->toIso8601String(),
    ])
        ->assertSee('Article scheduled to publish');

    expect($post->fresh()->published_at->toIso8601String())->toBe($futureDate->toIso8601String());

    // 3. Unpublish to draft
    BaharsyahServer::tool(PublishArticleTool::class, [
        'id' => $post->id,
        'action' => 'unpublish',
    ])
        ->assertSee('Article unpublished and reverted to draft status.');

    expect($post->fresh()->status)->toBe('draft');

    // 4. Archive
    BaharsyahServer::tool(PublishArticleTool::class, [
        'id' => $post->id,
        'action' => 'archive',
    ])
        ->assertSee('Article archived successfully.');

    expect($post->fresh()->status)->toBe('archived');
});

test('delete article tool requires force=true when article is published', function () {
    $post = Post::create([
        'user_id' => $this->author->id,
        'post_category_id' => $this->category->id,
        'title' => ['id' => 'Artikel Terbit Dilindungi'],
        'slug' => ['id' => 'artikel-terbit-dilindungi'],
        'content' => ['id' => '<p>Konten artikel penting.</p>'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    // Attempt delete without force
    BaharsyahServer::tool(DeleteArticleTool::class, [
        'id' => $post->id,
        'force' => false,
    ])
        ->assertSee('is currently published. Set \'force\' => true to confirm permanent deletion.');

    expect(Post::find($post->id))->not->toBeNull();

    // Delete with force=true
    BaharsyahServer::tool(DeleteArticleTool::class, [
        'id' => $post->id,
        'force' => true,
    ])
        ->assertSee('deleted successfully.');

    expect(Post::find($post->id))->toBeNull();
});

test('create article tool converts plain text and markdown into semantic richtext html', function () {
    // 1. Plain text textarea-style with newlines
    BaharsyahServer::tool(CreateArticleTool::class, [
        'title' => 'Wisata Air Terjun Doyam Seriam',
        'content' => "Air terjun tersembunyi di Paser.\n\nMemiliki kolam alami yang sangat jernih dan segar.",
        'category_id' => $this->category->id,
    ])
        ->assertSee('Article created successfully.');

    $postA = Post::whereJsonContains('slug->id', 'wisata-air-terjun-doyam-seriam')->first();
    expect($postA)->not->toBeNull();
    $savedContentA = $postA->getTranslation('content', 'id', false);
    // Must be wrapped in HTML paragraph tags
    expect($savedContentA)->toContain('<p>Air terjun tersembunyi di Paser.</p>')
        ->and($savedContentA)->toContain('<p>Memiliki kolam alami yang sangat jernih dan segar.</p>');

    // 2. Markdown formatting (headings, bullet points, bold)
    BaharsyahServer::tool(CreateArticleTool::class, [
        'title' => 'Panduan Trekking Bukit Bangkirai',
        'content' => "## Persiapan\n\n- Sepatu gunung\n- Air mineral\n\nJembatan kanopi berada pada **ketinggian 30 meter**.",
        'category_id' => $this->category->id,
    ])
        ->assertSee('Article created successfully.');

    $postB = Post::whereJsonContains('slug->id', 'panduan-trekking-bukit-bangkirai')->first();
    expect($postB)->not->toBeNull();
    $savedContentB = $postB->getTranslation('content', 'id', false);
    // Must be formatted into semantic RichText HTML
    expect($savedContentB)->toContain('<h2>Persiapan</h2>')
        ->and($savedContentB)->toContain('<ul>')
        ->and($savedContentB)->toContain('<li>Sepatu gunung</li>')
        ->and($savedContentB)->toContain('<strong>ketinggian 30 meter</strong>');
});

test('update article tool ensures updated content becomes semantic richtext html', function () {
    $post = Post::create([
        'user_id' => $this->author->id,
        'post_category_id' => $this->category->id,
        'title' => ['id' => 'Artikel Uji Update Richtext'],
        'slug' => ['id' => 'artikel-uji-update-richtext'],
        'content' => ['id' => '<p>Konten awal dalam tag HTML.</p>'],
        'status' => 'draft',
    ]);

    // Update with plain textarea text
    BaharsyahServer::tool(UpdateArticleTool::class, [
        'id' => $post->id,
        'content' => "Paragraf revisi pertama.\n\nParagraf revisi kedua.",
    ])
        ->assertSee('Article updated successfully.');

    $post->refresh();
    $updatedContent = $post->getTranslation('content', 'id', false);
    expect($updatedContent)->toContain('<p>Paragraf revisi pertama.</p>')
        ->and($updatedContent)->toContain('<p>Paragraf revisi kedua.</p>');
});
