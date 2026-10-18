<?php

use App\Mcp\Servers\BaharsyahServer;
use App\Mcp\Tools\Categories\MergeArticleCategoriesTool;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\ClientRepository;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-secret-mcp-token-123');

    $clientRepository = app(ClientRepository::class);
    $clientRepository->createPersonalAccessGrantClient('Test Personal Client', 'users');

    $this->author = User::factory()->create([
        'email' => 'editor@baharsyahjelajah.com',
    ]);
});

test('merge article categories tool moves articles to target category and deletes source category', function () {
    $source = PostCategory::create([
        'name' => ['id' => 'Kategori Usang', 'en' => 'Obsolete Category'],
        'slug' => ['id' => 'kategori-usang', 'en' => 'obsolete-category'],
    ]);

    $target = PostCategory::create([
        'name' => ['id' => 'Kategori Utama', 'en' => 'Main Category'],
        'slug' => ['id' => 'kategori-utama', 'en' => 'main-category'],
    ]);

    $post1 = Post::create([
        'user_id' => $this->author->id,
        'post_category_id' => $source->id,
        'title' => ['id' => 'Artikel A'],
        'slug' => ['id' => 'artikel-a'],
        'content' => ['id' => '<p>Konten A</p>'],
        'status' => 'published',
    ]);

    $post2 = Post::create([
        'user_id' => $this->author->id,
        'post_category_id' => $source->id,
        'title' => ['id' => 'Artikel B'],
        'slug' => ['id' => 'artikel-b'],
        'content' => ['id' => '<p>Konten B</p>'],
        'status' => 'draft',
    ]);

    BaharsyahServer::tool(MergeArticleCategoriesTool::class, [
        'source_category_id' => $source->id,
        'target_category_id' => $target->id,
    ])
        ->assertSee('Successfully merged category')
        ->assertSee('Moved 2 articles');

    expect(PostCategory::find($source->id))->toBeNull();
    expect(PostCategory::find($target->id))->not->toBeNull();
    expect($post1->fresh()->post_category_id)->toBe($target->id);
    expect($post2->fresh()->post_category_id)->toBe($target->id);
});

test('merge article categories tool rejects merging category into itself', function () {
    $category = PostCategory::create([
        'name' => ['id' => 'Kategori Tunggal'],
        'slug' => ['id' => 'kategori-tunggal'],
    ]);

    BaharsyahServer::tool(MergeArticleCategoriesTool::class, [
        'source_category_id' => $category->id,
        'target_category_id' => $category->id,
    ])
        ->assertSee('Source and target categories cannot be the same category.');
});

test('merge_article_categories requires mcp:admin scope over oauth', function () {
    $user = User::factory()->create([
        'email' => 'admin@baharsyahjelajah.com',
    ]);

    // Token with only mcp:write scope (insufficient for merge tool which requires mcp:admin)
    $token = $user->createToken('Editor Token', ['mcp:write'])->accessToken;

    $response = $this->withToken($token)->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => [
            'name' => 'merge_article_categories',
            'arguments' => [
                'source_category_id' => 1,
                'target_category_id' => 2,
            ],
        ],
    ]);

    $response->assertStatus(403);
    $response->assertJsonPath('error.message', "Forbidden: OAuth token lacks required scope 'mcp:admin' for tool 'merge_article_categories'.");
});
