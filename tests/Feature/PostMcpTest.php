<?php

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('authenticated user can create a post via mcp tool', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $category = PostCategory::create([
        'name' => ['id' => 'Wisata', 'en' => 'Tourism', 'ms' => 'Wisata'],
        'slug' => ['id' => 'wisata', 'en' => 'tourism', 'ms' => 'wisata'],
    ]);

    $payload = [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => [
            'name' => 'create-post-tool',
            'arguments' => [
                'title' => 'Panduan Liburan ke Danau Sentarum',
                'title_en' => 'Sentarum Lake Vacation Guide',
                'title_ms' => 'Panduan Bercuti ke Tasik Sentarum',
                'excerpt' => 'Eksplorasi keindahan alam Danau Sentarum.',
                'content' => 'Danau Sentarum adalah kawasan konservasi lahan basah di Kalimantan Barat.',
                'post_category_id' => $category->id,
                'status' => 'published',
            ],
        ],
    ];

    $response = $this->postJson('/mcp', $payload);

    $response->assertSuccessful();

    $post = Post::first();
    expect($post)->not->toBeNull();
    expect($post->getTranslation('title', 'id'))->toBe('Panduan Liburan ke Danau Sentarum');
    expect($post->getTranslation('title', 'en'))->toBe('Sentarum Lake Vacation Guide');
    expect($post->getTranslation('slug', 'id'))->toBe('panduan-liburan-ke-danau-sentarum');
    expect($post->post_category_id)->toBe($category->id);
    expect($post->status)->toBe('published');
});

test('authenticated user can update a post via mcp tool', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $category = PostCategory::create([
        'name' => ['id' => 'Wisata', 'en' => 'Tourism', 'ms' => 'Wisata'],
        'slug' => ['id' => 'wisata', 'en' => 'tourism', 'ms' => 'wisata'],
    ]);

    $post = Post::create([
        'post_category_id' => $category->id,
        'user_id' => $user->id,
        'title' => ['id' => 'Judul Draf', 'en' => 'Draft Title', 'ms' => 'Judul Draf'],
        'slug' => ['id' => 'judul-draf', 'en' => 'draft-title', 'ms' => 'judul-draf'],
        'content' => ['id' => 'Konten draf', 'en' => 'Draft content', 'ms' => 'Konten draf'],
        'status' => 'draft',
    ]);

    $payload = [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/call',
        'params' => [
            'name' => 'update-post-tool',
            'arguments' => [
                'id' => $post->id,
                'title' => 'Judul Artikel Final',
                'status' => 'published',
            ],
        ],
    ];

    $response = $this->postJson('/mcp', $payload);

    $response->assertSuccessful();

    $post->refresh();
    expect($post->getTranslation('title', 'id'))->toBe('Judul Artikel Final');
    expect($post->status)->toBe('published');
});

test('authenticated user can delete a post via mcp tool', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $category = PostCategory::create([
        'name' => ['id' => 'Wisata', 'en' => 'Tourism', 'ms' => 'Wisata'],
        'slug' => ['id' => 'wisata', 'en' => 'tourism', 'ms' => 'wisata'],
    ]);

    $post = Post::create([
        'post_category_id' => $category->id,
        'user_id' => $user->id,
        'title' => ['id' => 'Artikel Hapus', 'en' => 'Delete Article', 'ms' => 'Artikel Hapus'],
        'slug' => ['id' => 'artikel-hapus', 'en' => 'delete-article', 'ms' => 'artikel-hapus'],
        'content' => ['id' => 'Konten hapus', 'en' => 'Delete content', 'ms' => 'Konten hapus'],
    ]);

    $payload = [
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'tools/call',
        'params' => [
            'name' => 'delete-post-tool',
            'arguments' => [
                'id' => $post->id,
            ],
        ],
    ];

    $response = $this->postJson('/mcp', $payload);

    $response->assertSuccessful();
    expect(Post::find($post->id))->toBeNull();
});

test('authenticated user can list and search posts via mcp tool', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $category = PostCategory::create([
        'name' => ['id' => 'Wisata', 'en' => 'Tourism', 'ms' => 'Wisata'],
        'slug' => ['id' => 'wisata', 'en' => 'tourism', 'ms' => 'wisata'],
    ]);

    Post::create([
        'post_category_id' => $category->id,
        'user_id' => $user->id,
        'title' => ['id' => 'Eksplorasi Derawan', 'en' => 'Exploring Derawan', 'ms' => 'Eksplorasi Derawan'],
        'slug' => ['id' => 'eksplorasi-derawan', 'en' => 'exploring-derawan', 'ms' => 'eksplorasi-derawan'],
        'content' => ['id' => 'Konten Derawan', 'en' => 'Derawan content', 'ms' => 'Konten Derawan'],
        'status' => 'published',
    ]);

    Post::create([
        'post_category_id' => $category->id,
        'user_id' => $user->id,
        'title' => ['id' => 'Kuliner Pontianak', 'en' => 'Pontianak Culinary', 'ms' => 'Kuliner Pontianak'],
        'slug' => ['id' => 'kuliner-pontianak', 'en' => 'pontianak-culinary', 'ms' => 'kuliner-pontianak'],
        'content' => ['id' => 'Konten Pontianak', 'en' => 'Pontianak content', 'ms' => 'Konten Pontianak'],
        'status' => 'published',
    ]);

    $payload = [
        'jsonrpc' => '2.0',
        'id' => 4,
        'method' => 'tools/call',
        'params' => [
            'name' => 'list-posts-tool',
            'arguments' => [
                'search' => 'Derawan',
            ],
        ],
    ];

    $response = $this->postJson('/mcp', $payload);

    $response->assertSuccessful();
    $content = json_decode($response->json('result.content.0.text'), true);

    expect($content['success'])->toBeTrue();
    expect($content['total'])->toBe(1);
    expect($content['posts'][0]['title']['id'])->toBe('Eksplorasi Derawan');
});
