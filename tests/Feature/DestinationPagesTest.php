<?php

use App\Enums\TourType;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\UmrahPackages\Pages\EditUmrahPackage;
use App\Models\Destination;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tour;
use App\Models\UmrahPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('lists only active destinations with localized seo metadata', function () {
    $active = Destination::factory()->create([
        'name' => destinationTranslations('Tanjung Puting', 'Tanjung Puting', 'Tanjung Puting'),
        'slug' => 'tanjung-puting',
        'is_active' => true,
        'is_featured' => true,
    ]);
    Destination::factory()->create([
        'name' => destinationTranslations('Destinasi Tersembunyi', 'Hidden Destination', 'Destinasi Tersembunyi'),
        'slug' => 'hidden-destination',
        'is_active' => false,
    ]);

    get('/en/destinasi')
        ->assertSuccessful()
        ->assertSee($active->getTranslation('name', 'en'))
        ->assertDontSee('Hidden Destination')
        ->assertSee('<link rel="canonical" href="'.route('destination.index', ['locale' => 'en']).'">', false)
        ->assertSee('<link rel="alternate" hreflang="id" href="'.route('destination.index', ['locale' => 'id']).'">', false)
        ->assertSee('"@type":"CollectionPage"', false)
        ->assertSee('"@type":"ItemList"', false);
});

it('renders deduplicated public packages and published articles for a destination', function () {
    $destination = Destination::factory()->create([
        'name' => destinationTranslations('Makkah dan Madinah', 'Makkah and Madinah', 'Makkah dan Madinah'),
        'slug' => 'makkah-madinah',
        'description' => destinationTranslations(
            'Panduan perjalanan menuju dua kota suci.',
            'A guide to journeys across the two holy cities.',
            'Panduan perjalanan ke dua kota suci.',
        ),
    ]);

    $activeTour = createDestinationPageTour('Tour Aktif', true);
    $sharedPackage = $activeTour->packages()->create([
        'name' => destinationTranslations('Paket Tour Terkait', 'Related Tour Package', 'Pakej Lawatan Berkaitan'),
        'slug' => destinationTranslations('paket-tour-terkait', 'related-tour-package', 'pakej-lawatan-berkaitan'),
        'duration_days' => 4,
        'duration_nights' => 3,
    ]);
    $itinerary = $sharedPackage->itineraries()->create([
        'day_number' => 1,
        'title' => destinationTranslations('Hari Pertama', 'First Day', 'Hari Pertama'),
    ]);
    $sharedPackage->destinations()->attach($destination);
    $itinerary->destinations()->attach($destination);

    $inactiveTour = createDestinationPageTour('Tour Nonaktif', false);
    $inactiveTour->packages()->create([
        'name' => destinationTranslations('Paket Tidak Tampil', 'Hidden Tour Package', 'Pakej Tidak Dipaparkan'),
        'slug' => destinationTranslations('paket-tidak-tampil', 'hidden-tour-package', 'pakej-tidak-dipaparkan'),
        'duration_days' => 3,
        'duration_nights' => 2,
    ])->destinations()->attach($destination);

    $activeUmrah = UmrahPackage::factory()->create([
        'name' => destinationTranslations('Umrah Pilihan', 'Selected Umrah', 'Umrah Pilihan'),
        'slug' => destinationTranslations('umrah-pilihan', 'selected-umrah', 'umrah-pilihan'),
        'is_active' => true,
    ]);
    $activeUmrah->destinations()->attach($destination);
    $inactiveUmrah = UmrahPackage::factory()->create([
        'name' => destinationTranslations('Umrah Nonaktif', 'Inactive Umrah', 'Umrah Tidak Aktif'),
        'slug' => destinationTranslations('umrah-nonaktif', 'inactive-umrah', 'umrah-tidak-aktif'),
        'is_active' => false,
    ]);
    $inactiveUmrah->destinations()->attach($destination);

    $category = PostCategory::create([
        'name' => destinationTranslations('Panduan', 'Guides', 'Panduan'),
        'slug' => destinationTranslations('panduan', 'guides', 'panduan'),
    ]);
    $author = User::factory()->create();
    $publishedPost = Post::create([
        'post_category_id' => $category->id,
        'user_id' => $author->id,
        'title' => destinationTranslations('Panduan Kota Suci', 'Holy Cities Guide', 'Panduan Kota Suci'),
        'slug' => destinationTranslations('panduan-kota-suci', 'holy-cities-guide', 'panduan-kota-suci'),
        'content' => destinationTranslations('Isi panduan.', 'Guide content.', 'Kandungan panduan.'),
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $publishedPost->destinations()->attach($destination);
    $draftPost = Post::create([
        'post_category_id' => $category->id,
        'user_id' => $author->id,
        'title' => destinationTranslations('Artikel Draf', 'Draft Article', 'Artikel Draf'),
        'slug' => destinationTranslations('artikel-draf', 'draft-article', 'artikel-draf'),
        'content' => destinationTranslations('Draf.', 'Draft.', 'Draf.'),
        'status' => 'draft',
    ]);
    $draftPost->destinations()->attach($destination);

    get('/en/destinasi/makkah-madinah')
        ->assertSuccessful()
        ->assertViewHas('tourPackages', fn ($packages): bool => $packages->total() === 1 && $packages->first()->is($sharedPackage))
        ->assertViewHas('umrahPackages', fn ($packages): bool => $packages->total() === 1 && $packages->first()->is($activeUmrah))
        ->assertViewHas('posts', fn ($posts): bool => $posts->total() === 1 && $posts->first()->is($publishedPost))
        ->assertSee('Related Tour Package')
        ->assertSee('data-destination-tour-layout="single-column"', false)
        ->assertDontSee('Hidden Tour Package')
        ->assertSee('Selected Umrah')
        ->assertDontSee('Inactive Umrah')
        ->assertSee('Holy Cities Guide')
        ->assertDontSee('Draft Article')
        ->assertSee('"@type":"TouristDestination"', false)
        ->assertSee('"@type":"BreadcrumbList"', false)
        ->assertSee('<link rel="alternate" hreflang="ms" href="'.route('destination.show', [
            'locale' => 'ms',
            'destination' => $destination,
        ]).'">', false);
});

it('returns not found for an inactive destination', function () {
    $destination = Destination::factory()->create([
        'slug' => 'inactive-destination',
        'is_active' => false,
    ]);

    get('/id/destinasi/'.$destination->slug)->assertNotFound();
});

it('redirects unlocalized destination paths to Indonesian while preserving the query', function () {
    get('/destinasi/tanjung-puting?ref=search')
        ->assertRedirect('/id/destinasi/tanjung-puting?ref=search');
});

it('associates destinations from the umrah and post filament forms', function () {
    $this->actingAs(User::factory()->create());

    $destination = Destination::factory()->create();
    $umrahPackage = UmrahPackage::factory()->create();
    $category = PostCategory::create([
        'name' => destinationTranslations('Panduan', 'Guides', 'Panduan'),
        'slug' => destinationTranslations('panduan', 'guides', 'panduan'),
    ]);
    $post = Post::create([
        'post_category_id' => $category->id,
        'user_id' => auth()->id(),
        'title' => destinationTranslations('Artikel', 'Article', 'Artikel'),
        'slug' => destinationTranslations('artikel', 'article', 'artikel'),
        'content' => destinationTranslations('Isi.', 'Content.', 'Kandungan.'),
        'status' => 'draft',
    ]);

    Livewire::test(EditUmrahPackage::class, ['record' => $umrahPackage->id])
        ->fillForm(['destinations' => [$destination->id]])
        ->call('save')
        ->assertHasNoFormErrors();

    Livewire::test(EditPost::class, ['record' => $post->id])
        ->fillForm(['destinations' => [$destination->id]])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($umrahPackage->fresh()->destinations->sole()->is($destination))->toBeTrue()
        ->and($post->fresh()->destinations->sole()->is($destination))->toBeTrue();
});

it('does not expose missing media files as destination images', function () {
    Storage::fake('public');

    $destination = Destination::factory()->create();
    $media = $destination
        ->addMedia(UploadedFile::fake()->image('destination.jpg', 1200, 800))
        ->toMediaCollection(Destination::MEDIA_COLLECTION_GALLERY, 'public');

    expect($destination->fresh()->cover_url)->not->toBeNull();

    Storage::disk('public')->delete($media->getPathRelativeToRoot());

    expect($destination->fresh()->cover_url)->toBeNull()
        ->and($destination->fresh()->gallery_urls)->toBeEmpty();
});

/** @return array{id: string, en: string, ms: string} */
function destinationTranslations(string $id, string $en, string $ms): array
{
    return compact('id', 'en', 'ms');
}

function createDestinationPageTour(string $name, bool $isActive): Tour
{
    return Tour::create([
        'name' => destinationTranslations($name, $name, $name),
        'slug' => destinationTranslations(str($name)->slug()->toString(), str($name)->slug()->toString(), str($name)->slug()->toString()),
        'tour_type' => TourType::Domestic,
        'currency' => 'IDR',
        'is_active' => $isActive,
        'is_featured' => false,
    ]);
}
