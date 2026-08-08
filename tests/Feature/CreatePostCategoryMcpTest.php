<?php

use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('unauthenticated request to mcp endpoint is rejected', function () {
    $response = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/list',
    ]);

    $response->assertUnauthorized();
});

test('authenticated user via sanctum can create a post category through mcp tool', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $payload = [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => [
            'name' => 'create-post-category-tool',
            'arguments' => [
                'name' => 'Wisata Kuliner',
                'name_en' => 'Culinary Travel',
                'name_ms' => 'Pelancongan Kuliner',
                'description' => 'Panduan tempat makan dan makanan khas.',
                'description_en' => 'Guide to local culinary destinations.',
                'description_ms' => 'Panduan tempat makan tempatan.',
            ],
        ],
    ];

    $response = $this->postJson('/mcp', $payload);

    $response->assertSuccessful();

    $category = PostCategory::first();
    expect($category)->not->toBeNull();
    expect($category->getTranslation('name', 'id'))->toBe('Wisata Kuliner');
    expect($category->getTranslation('name', 'en'))->toBe('Culinary Travel');
    expect($category->getTranslation('name', 'ms'))->toBe('Pelancongan Kuliner');
    expect($category->getTranslation('slug', 'id'))->toBe('wisata-kuliner');
    expect($category->getTranslation('slug', 'en'))->toBe('culinary-travel');
    expect($category->getTranslation('slug', 'ms'))->toBe('pelancongan-kuliner');
    expect($category->getTranslation('description', 'id'))->toBe('Panduan tempat makan dan makanan khas.');
});

test('authenticated user via sanctum can update a post category through mcp tool', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $category = PostCategory::create([
        'name' => ['id' => 'Berita Lama', 'en' => 'Old News', 'ms' => 'Berita Lama'],
        'slug' => ['id' => 'berita-lama', 'en' => 'old-news', 'ms' => 'berita-lama'],
        'description' => ['id' => 'Deskripsi lama', 'en' => 'Old description', 'ms' => 'Deskripsi lama'],
    ]);

    $payload = [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/call',
        'params' => [
            'name' => 'update-post-category-tool',
            'arguments' => [
                'id' => $category->id,
                'name' => 'Berita Terkini & Update',
                'name_en' => 'Latest News & Updates',
                'description' => 'Deskripsi yang telah diperbarui.',
            ],
        ],
    ];

    $response = $this->postJson('/mcp', $payload);

    $response->assertSuccessful();

    $category->refresh();
    expect($category->getTranslation('name', 'id'))->toBe('Berita Terkini & Update');
    expect($category->getTranslation('name', 'en'))->toBe('Latest News & Updates');
    expect($category->getTranslation('slug', 'id'))->toBe('berita-terkini-update');
    expect($category->getTranslation('description', 'id'))->toBe('Deskripsi yang telah diperbarui.');
});

test('authenticated user via sanctum can delete a post category through mcp tool', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $category = PostCategory::create([
        'name' => ['id' => 'Kategori Hapus', 'en' => 'Delete Category', 'ms' => 'Kategori Hapus'],
        'slug' => ['id' => 'kategori-hapus', 'en' => 'delete-category', 'ms' => 'kategori-hapus'],
    ]);

    $payload = [
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'tools/call',
        'params' => [
            'name' => 'delete-post-category-tool',
            'arguments' => [
                'id' => $category->id,
            ],
        ],
    ];

    $response = $this->postJson('/mcp', $payload);

    $response->assertSuccessful();
    expect(PostCategory::find($category->id))->toBeNull();
});
