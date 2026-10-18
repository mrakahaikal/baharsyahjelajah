<?php

use App\Mcp\Servers\BaharsyahServer;
use App\Mcp\Tools\Categories\CreateArticleCategoryTool;
use App\Mcp\Tools\Categories\DeleteArticleCategoryTool;
use App\Mcp\Tools\Categories\GetArticleCategoryTool;
use App\Mcp\Tools\Categories\ListArticleCategoriesTool;
use App\Mcp\Tools\Categories\UpdateArticleCategoryTool;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Mcp\Server\Transport\FakeTransporter;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-secret-mcp-token-123');
});

test('mcp web endpoint rejects requests without bearer token', function () {
    $response = $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/list',
        'params' => [],
    ]);

    $response->assertStatus(401);
    $response->assertJsonPath('error.message', 'Unauthorized: Invalid or missing MCP bearer token.');
});

test('mcp web endpoint rejects requests with invalid bearer token', function () {
    $response = $this->withToken('wrong-invalid-token')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/list',
        'params' => [],
    ]);

    $response->assertStatus(401);
    $response->assertJsonPath('error.message', 'Unauthorized: Invalid or missing MCP bearer token.');
});

test('mcp web endpoint accepts authorized initialize request with valid bearer token', function () {
    $response = $this->withToken('test-secret-mcp-token-123')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2024-11-05',
            'capabilities' => [],
            'clientInfo' => ['name' => 'TestClient', 'version' => '1.0'],
        ],
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('result.serverInfo.name', 'Baharsyah Jelajah Management & Content Server');
});

test('all article category tools are registered in baharsyah mcp server', function () {
    $server = app(BaharsyahServer::class, ['transport' => new FakeTransporter]);
    $reflection = new ReflectionClass($server);
    $toolsProperty = $reflection->getProperty('tools');
    $tools = $toolsProperty->getValue($server);

    expect($tools)->toContain(
        ListArticleCategoriesTool::class,
        GetArticleCategoryTool::class,
        CreateArticleCategoryTool::class,
        UpdateArticleCategoryTool::class,
        DeleteArticleCategoryTool::class,
    );
});

test('list article categories tool returns paginated category list with article counts and multilingual support', function () {
    $catA = PostCategory::create([
        'name' => ['id' => 'Tips Wisata', 'en' => 'Travel Tips', 'ms' => 'Petua Pelancongan'],
        'slug' => ['id' => 'tips-wisata', 'en' => 'travel-tips', 'ms' => 'petua-pelancongan'],
        'description' => ['id' => 'Panduan berlibur', 'en' => 'Holiday guide', 'ms' => 'Panduan percutian'],
    ]);

    $catB = PostCategory::create([
        'name' => ['id' => 'Budaya Dayak', 'en' => 'Dayak Culture', 'ms' => 'Budaya Dayak'],
        'slug' => ['id' => 'budaya-dayak', 'en' => 'dayak-culture', 'ms' => 'budaya-dayak'],
    ]);

    $user = User::factory()->create(['email' => 'author@baharsyahjelajah.com']);
    Post::create([
        'user_id' => $user->id,
        'post_category_id' => $catA->id,
        'title' => ['id' => 'Tips Packing ke Derawan', 'en' => 'Packing Tips for Derawan', 'ms' => 'Petua Mengemas ke Derawan'],
        'slug' => ['id' => 'tips-packing-ke-derawan', 'en' => 'packing-tips-for-derawan', 'ms' => 'petua-mengemas-ke-derawan'],
        'content' => ['id' => '<p>Konten panduan.</p>', 'en' => '<p>Guide content.</p>', 'ms' => '<p>Kandungan panduan.</p>'],
        'status' => 'published',
    ]);

    BaharsyahServer::tool(ListArticleCategoriesTool::class, ['with_counts' => true, 'locale' => 'id'])
        ->assertSee('Tips Wisata')
        ->assertSee('Budaya Dayak');

    BaharsyahServer::tool(ListArticleCategoriesTool::class, ['with_counts' => true, 'locale' => 'en'])
        ->assertSee('Travel Tips')
        ->assertSee('Dayak Culture');
});

test('list article categories tool filters results by search query', function () {
    PostCategory::create([
        'name' => ['id' => 'Kuliner Nusantara', 'en' => 'Indonesian Culinary', 'ms' => 'Kulinari Nusantara'],
        'slug' => ['id' => 'kuliner-nusantara', 'en' => 'indonesian-culinary', 'ms' => 'kulinari-nusantara'],
    ]);

    PostCategory::create([
        'name' => ['id' => 'Transportasi Sungai', 'en' => 'River Transportation', 'ms' => 'Pengangkutan Sungai'],
        'slug' => ['id' => 'transportasi-sungai', 'en' => 'river-transportation', 'ms' => 'pengangkutan-sungai'],
    ]);

    BaharsyahServer::tool(ListArticleCategoriesTool::class, ['search' => 'Kuliner'])
        ->assertSee('Kuliner Nusantara')
        ->assertDontSee('Transportasi Sungai');
});

test('get article category tool retrieves category details by id and localized slug', function () {
    $user = User::factory()->create(['email' => 'guide@baharsyahjelajah.com']);
    $cat = PostCategory::create([
        'name' => ['id' => 'Satwa Liar', 'en' => 'Wildlife', 'ms' => 'Hidupan Liar'],
        'slug' => ['id' => 'satwa-liar', 'en' => 'wildlife', 'ms' => 'hidupan-liar'],
    ]);

    Post::create([
        'user_id' => $user->id,
        'post_category_id' => $cat->id,
        'title' => ['id' => 'Habitat Orangutan Borneo', 'en' => 'Borneo Orangutan Habitat', 'ms' => 'Habitat Orangutan Borneo'],
        'slug' => ['id' => 'habitat-orangutan-borneo', 'en' => 'borneo-orangutan-habitat', 'ms' => 'habitat-orangutan-borneo'],
        'content' => ['id' => '<p>Konten orangutan.</p>', 'en' => '<p>Orangutan content.</p>', 'ms' => '<p>Kandungan orangutan.</p>'],
        'status' => 'published',
    ]);

    // Test by ID
    BaharsyahServer::tool(GetArticleCategoryTool::class, ['id' => $cat->id])
        ->assertSee('Satwa Liar')
        ->assertSee('Habitat Orangutan Borneo');

    // Test by English slug
    BaharsyahServer::tool(GetArticleCategoryTool::class, ['slug' => 'wildlife', 'locale' => 'en'])
        ->assertSee('Wildlife')
        ->assertSee('Borneo Orangutan Habitat');
});

test('get article category tool returns error when category is not found', function () {
    BaharsyahServer::tool(GetArticleCategoryTool::class, ['id' => 99999])
        ->assertSee('Category not found with the provided identifier.');
});

test('create article category tool creates category with single string input', function () {
    BaharsyahServer::tool(CreateArticleCategoryTool::class, [
        'name' => 'Petualangan Rimba',
    ])
        ->assertSee('Category created successfully.')
        ->assertSee('petualangan-rimba');

    $cat = PostCategory::whereJsonContains('slug->id', 'petualangan-rimba')->first();
    expect($cat)->not->toBeNull()
        ->and($cat->getTranslation('name', 'id', false))->toBe('Petualangan Rimba');
});

test('create article category tool creates category with multilingual object input', function () {
    BaharsyahServer::tool(CreateArticleCategoryTool::class, [
        'name' => [
            'id' => 'Warisan Sejarah',
            'en' => 'Historical Heritage',
            'ms' => 'Warisan Sejarah',
        ],
        'slug' => [
            'id' => 'warisan-sejarah',
            'en' => 'historical-heritage',
            'ms' => 'warisan-sejarah',
        ],
        'description' => [
            'id' => 'Wisata sejarah di Kalimantan Timur',
            'en' => 'Historical tourism in East Borneo',
            'ms' => 'Pelancongan sejarah di Kalimantan Timur',
        ],
    ])
        ->assertSee('Category created successfully.')
        ->assertSee('warisan-sejarah');

    $cat = PostCategory::whereJsonContains('slug->id', 'warisan-sejarah')->first();
    expect($cat)->not->toBeNull()
        ->and($cat->getTranslation('name', 'en', false))->toBe('Historical Heritage');
});

test('create article category tool prevents duplicate slugs in id locale', function () {
    PostCategory::create([
        'name' => ['id' => 'Kategori Lama', 'en' => 'Old Category', 'ms' => 'Kategori Lama'],
        'slug' => ['id' => 'kategori-lama', 'en' => 'old-category', 'ms' => 'kategori-lama'],
    ]);

    BaharsyahServer::tool(CreateArticleCategoryTool::class, [
        'name' => 'Kategori Duplikat Baru',
        'slug' => 'kategori-lama',
    ])
        ->assertSee("A category with the slug 'kategori-lama' already exists.");
});

test('update article category tool updates name, slug, and description', function () {
    $cat = PostCategory::create([
        'name' => ['id' => 'Nama Awal', 'en' => 'Initial Name', 'ms' => 'Nama Awal'],
        'slug' => ['id' => 'nama-awal', 'en' => 'initial-name', 'ms' => 'nama-awal'],
    ]);

    BaharsyahServer::tool(UpdateArticleCategoryTool::class, [
        'id' => $cat->id,
        'name' => 'Nama Terkini',
        'slug' => 'nama-terkini',
        'description' => 'Deskripsi diperbarui',
    ])
        ->assertSee('Category updated successfully.')
        ->assertSee('Nama Terkini')
        ->assertSee('nama-terkini');

    expect($cat->fresh()->getTranslation('name', 'id', false))->toBe('Nama Terkini')
        ->and($cat->fresh()->getTranslation('slug', 'id', false))->toBe('nama-terkini');
});

test('delete article category tool prevents deletion without force when articles exist', function () {
    $user = User::factory()->create(['email' => 'admin@baharsyahjelajah.com']);
    $cat = PostCategory::create([
        'name' => ['id' => 'Kategori Terkunci', 'en' => 'Locked Category', 'ms' => 'Kategori Terkunci'],
        'slug' => ['id' => 'kategori-terkunci', 'en' => 'locked-category', 'ms' => 'kategori-terkunci'],
    ]);

    Post::create([
        'user_id' => $user->id,
        'post_category_id' => $cat->id,
        'title' => ['id' => 'Artikel Terikat', 'en' => 'Attached Article', 'ms' => 'Artikel Terikat'],
        'slug' => ['id' => 'artikel-terikat', 'en' => 'attached-article', 'ms' => 'artikel-terikat'],
        'content' => ['id' => '<p>Konten artikel.</p>', 'en' => '<p>Article content.</p>', 'ms' => '<p>Kandungan artikel.</p>'],
        'status' => 'published',
    ]);

    BaharsyahServer::tool(DeleteArticleCategoryTool::class, [
        'id' => $cat->id,
        'force' => false,
    ])
        ->assertSee('because 1 article(s) are assigned to it.');

    expect(PostCategory::find($cat->id))->not->toBeNull();
});

test('delete article category tool reassigns articles and deletes category when reassign_to_category_id is provided', function () {
    $user = User::factory()->create(['email' => 'admin@baharsyahjelajah.com']);
    $catA = PostCategory::create([
        'name' => ['id' => 'Kategori Asal', 'en' => 'Origin Category', 'ms' => 'Kategori Asal'],
        'slug' => ['id' => 'kategori-asal', 'en' => 'origin-category', 'ms' => 'kategori-asal'],
    ]);
    $catB = PostCategory::create([
        'name' => ['id' => 'Kategori Tujuan', 'en' => 'Target Category', 'ms' => 'Kategori Tujuan'],
        'slug' => ['id' => 'kategori-tujuan', 'en' => 'target-category', 'ms' => 'kategori-tujuan'],
    ]);

    $post = Post::create([
        'user_id' => $user->id,
        'post_category_id' => $catA->id,
        'title' => ['id' => 'Artikel Pindah', 'en' => 'Moved Article', 'ms' => 'Artikel Pindah'],
        'slug' => ['id' => 'artikel-pindah', 'en' => 'moved-article', 'ms' => 'artikel-pindah'],
        'content' => ['id' => '<p>Konten artikel.</p>', 'en' => '<p>Article content.</p>', 'ms' => '<p>Kandungan artikel.</p>'],
        'status' => 'published',
    ]);

    BaharsyahServer::tool(DeleteArticleCategoryTool::class, [
        'id' => $catA->id,
        'reassign_to_category_id' => $catB->id,
    ])
        ->assertSee('was deleted successfully.');

    expect(PostCategory::find($catA->id))->toBeNull();
    expect($post->fresh()->post_category_id)->toBe($catB->id);
});

test('delete article category tool deletes articles and category when force is true', function () {
    $user = User::factory()->create(['email' => 'admin@baharsyahjelajah.com']);
    $cat = PostCategory::create([
        'name' => ['id' => 'Kategori Dihapus', 'en' => 'Deleted Category', 'ms' => 'Kategori Dihapus'],
        'slug' => ['id' => 'kategori-dihapus', 'en' => 'deleted-category', 'ms' => 'kategori-dihapus'],
    ]);

    $post = Post::create([
        'user_id' => $user->id,
        'post_category_id' => $cat->id,
        'title' => ['id' => 'Artikel Musnah', 'en' => 'Deleted Article', 'ms' => 'Artikel Musnah'],
        'slug' => ['id' => 'artikel-musnah', 'en' => 'deleted-article', 'ms' => 'artikel-musnah'],
        'content' => ['id' => '<p>Konten artikel.</p>', 'en' => '<p>Article content.</p>', 'ms' => '<p>Kandungan artikel.</p>'],
        'status' => 'published',
    ]);

    BaharsyahServer::tool(DeleteArticleCategoryTool::class, [
        'id' => $cat->id,
        'force' => true,
    ])
        ->assertSee('was deleted successfully.');

    expect(PostCategory::find($cat->id))->toBeNull();
    expect(Post::find($post->id))->toBeNull();
});
