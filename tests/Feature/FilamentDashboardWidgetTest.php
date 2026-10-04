<?php

use App\Enums\TourType;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\UmrahPackage;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can render the stats overview widget on the dashboard', function () {
    $tourCategory = TourCategory::create([
        'name' => ['id' => 'Kategori Tur', 'en' => 'Tour Category', 'ms' => 'Kategori Tur'],
        'slug' => ['id' => 'kategori-tur', 'en' => 'tour-category', 'ms' => 'kategori-tur'],
    ]);

    Tour::create([
        'tour_category_id' => $tourCategory->id,
        'name' => ['id' => 'Paket Bromo', 'en' => 'Bromo Tour', 'ms' => 'Pakej Bromo'],
        'slug' => ['id' => 'paket-bromo', 'en' => 'bromo-tour', 'ms' => 'pakej-bromo'],
        'tour_type' => TourType::Domestic,
        'currency' => 'IDR',
        'is_active' => true,
        'is_featured' => false,
    ]);

    UmrahPackage::factory()->create(['is_active' => true]);
    Vehicle::factory()->create(['is_active' => true]);

    $postCategory = PostCategory::create([
        'name' => ['id' => 'Berita', 'en' => 'News', 'ms' => 'Berita'],
        'slug' => ['id' => 'berita', 'en' => 'news', 'ms' => 'berita'],
    ]);

    Post::create([
        'post_category_id' => $postCategory->id,
        'user_id' => $this->user->id,
        'title' => ['id' => 'Tips Liburan', 'en' => 'Holiday Tips', 'ms' => 'Tips Percutian'],
        'slug' => ['id' => 'tips-liburan', 'en' => 'holiday-tips', 'ms' => 'tips-percutian'],
        'content' => ['id' => 'Konten tips', 'en' => 'Content tips', 'ms' => 'Kandungan tips'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    Livewire::test(StatsOverviewWidget::class)
        ->assertSuccessful()
        ->assertSee('Paket Tur')
        ->assertSee('Paket Umrah')
        ->assertSee('Armada Rental')
        ->assertSee('Artikel Blog')
        ->assertSee('1 / 1');
});

