<?php

use App\Mcp\Prompts\DraftArticlePrompt;
use App\Mcp\Prompts\ReviewArticleSeoPrompt;
use App\Mcp\Resources\ArticleCatalogOverviewResource;
use App\Mcp\Resources\RecentArticlesResource;
use App\Mcp\Servers\BaharsyahServer;
use App\Models\Destination;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Mcp\Server\Transport\FakeTransporter;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-static-agent-token-xyz');

    $this->author = User::factory()->create([
        'email' => 'writer@baharsyahjelajah.com',
    ]);

    $this->category = PostCategory::create([
        'name' => ['id' => 'Wisata Alam', 'en' => 'Nature Tourism', 'ms' => 'Pelancongan Alam Semula Jadi'],
        'slug' => ['id' => 'wisata-alam', 'en' => 'nature-tourism', 'ms' => 'pelancongan-alam'],
    ]);

    $this->destination = Destination::create([
        'name' => ['id' => 'Kepulauan Derawan', 'en' => 'Derawan Islands'],
        'slug' => 'kepulauan-derawan',
        'is_active' => true,
    ]);
});

test('article resources and prompts are registered in baharsyah server', function () {
    $server = app(BaharsyahServer::class, ['transport' => new FakeTransporter]);
    $reflection = new ReflectionClass($server);

    $resources = $reflection->getProperty('resources')->getValue($server);
    expect($resources)->toContain(
        RecentArticlesResource::class,
        ArticleCatalogOverviewResource::class,
    );

    $prompts = $reflection->getProperty('prompts')->getValue($server);
    expect($prompts)->toContain(
        DraftArticlePrompt::class,
        ReviewArticleSeoPrompt::class,
    );
});

test('recent articles resource returns published articles snapshot via jsonrpc', function () {
    $publishedPost = Post::create([
        'user_id' => $this->author->id,
        'post_category_id' => $this->category->id,
        'title' => ['id' => 'Keajaiban Bahari Derawan', 'en' => 'Derawan Marine Wonders'],
        'slug' => ['id' => 'keajaiban-bahari-derawan', 'en' => 'derawan-marine-wonders'],
        'content' => ['id' => '<p>Air laut sebening kristal dan terumbu karang indah.</p>'],
        'status' => 'published',
        'published_at' => now()->subHours(2),
    ]);
    $publishedPost->destinations()->attach($this->destination->id);

    $draftPost = Post::create([
        'user_id' => $this->author->id,
        'post_category_id' => $this->category->id,
        'title' => ['id' => 'Artikel Rahasia Draf'],
        'slug' => ['id' => 'artikel-rahasia-draf'],
        'content' => ['id' => '<p>Draf rahasia.</p>'],
        'status' => 'draft',
    ]);

    $response = $this->withToken('test-static-agent-token-xyz')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'resources/read',
        'params' => [
            'uri' => 'articles://recent',
        ],
    ]);

    $response->assertOk();
    $result = $response->json('result.contents.0.text');
    expect($result)->not->toBeNull();

    $data = json_decode($result, true);
    expect($data['resource'])->toBe('articles://recent')
        ->and($data['count'])->toBe(1)
        ->and($data['articles'][0]['title']['id'])->toBe('Keajaiban Bahari Derawan')
        ->and($data['articles'][0]['destinations'][0]['slug'])->toBe('kepulauan-derawan');
});

test('article catalog overview resource returns statistics and breakdown via jsonrpc', function () {
    Post::create([
        'user_id' => $this->author->id,
        'post_category_id' => $this->category->id,
        'title' => ['id' => 'Artikel A'],
        'slug' => ['id' => 'artikel-a'],
        'content' => ['id' => '<p>Konten A</p>'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    Post::create([
        'user_id' => $this->author->id,
        'post_category_id' => $this->category->id,
        'title' => ['id' => 'Artikel B'],
        'slug' => ['id' => 'artikel-b'],
        'content' => ['id' => '<p>Konten B</p>'],
        'status' => 'draft',
    ]);

    $response = $this->withToken('test-static-agent-token-xyz')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'resources/read',
        'params' => [
            'uri' => 'articles://catalog-overview',
        ],
    ]);

    $response->assertOk();
    $result = $response->json('result.contents.0.text');
    $data = json_decode($result, true);

    expect($data['overview']['total_articles'])->toBe(2)
        ->and($data['overview']['status_breakdown']['published'])->toBe(1)
        ->and($data['overview']['status_breakdown']['draft'])->toBe(1)
        ->and($data['overview']['categories_distribution'][0]['name'])->toBe('Wisata Alam');
});

test('draft article prompt generates comprehensive guidelines with richtext and multilingual requirements', function () {
    $response = $this->withToken('test-static-agent-token-xyz')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'prompts/get',
        'params' => [
            'name' => 'draft_article',
            'arguments' => [
                'topic' => 'Eksplorasi Hiu Paus Talisayan',
                'target_destination' => 'Talisayan',
            ],
        ],
    ]);

    $response->assertOk();
    $text = $response->json('result.messages.0.content.text');

    expect($text)->toContain('Eksplorasi Hiu Paus Talisayan')
        ->and($text)->toContain('Format Konten Wajib RichText HTML')
        ->and($text)->toContain('Kelengkapan Multilingual')
        ->and($text)->toContain('Wisata Alam')
        ->and($text)->toContain('create_article');
});

test('review article seo prompt generates audit guidelines with article context', function () {
    $post = Post::create([
        'user_id' => $this->author->id,
        'post_category_id' => $this->category->id,
        'title' => ['id' => 'Pesona Danau Labuan Cermin', 'en' => 'Labuan Cermin Lake'],
        'slug' => ['id' => 'pesona-danau-labuan-cermin', 'en' => 'labuan-cermin-lake'],
        'content' => ['id' => '<p>Danau dua rasa di Biduk-Biduk Berau.</p>'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $response = $this->withToken('test-static-agent-token-xyz')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 4,
        'method' => 'prompts/get',
        'params' => [
            'name' => 'review_article_seo',
            'arguments' => [
                'article_id' => $post->id,
            ],
        ],
    ]);

    $response->assertOk();
    $text = $response->json('result.messages.0.content.text');

    expect($text)->toContain('Pesona Danau Labuan Cermin')
        ->and($text)->toContain('Audit Format RichText HTML')
        ->and($text)->toContain('Audit Keterbacaan & On-Page SEO')
        ->and($text)->toContain('update_article');
});

test('resources/list and prompts/list endpoints include new article resources and prompts', function () {
    // 1. List resources
    $resourcesRes = $this->withToken('test-static-agent-token-xyz')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 5,
        'method' => 'resources/list',
        'params' => [],
    ]);

    $resourcesRes->assertOk();
    $uris = collect($resourcesRes->json('result.resources'))->pluck('uri')->all();
    expect($uris)->toContain('categories://taxonomy')
        ->and($uris)->toContain('articles://recent')
        ->and($uris)->toContain('articles://catalog-overview');

    // 2. List prompts
    $promptsRes = $this->withToken('test-static-agent-token-xyz')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 6,
        'method' => 'prompts/list',
        'params' => [],
    ]);

    $promptsRes->assertOk();
    $names = collect($promptsRes->json('result.prompts'))->pluck('name')->all();
    expect($names)->toContain('audit_category_taxonomy')
        ->and($names)->toContain('suggest_category_for_topic')
        ->and($names)->toContain('draft_article')
        ->and($names)->toContain('review_article_seo');
});
