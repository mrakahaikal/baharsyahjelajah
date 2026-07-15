<?php

use App\Enums\TourType;
use App\Filament\Pages\ManageGeneralSettings;
use App\Models\Country;
use App\Models\Destination;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tour;
use App\Models\UmrahPackage;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VisaService;
use App\Services\SitemapService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('generates a localized sitemap index from public routes and records', function () {
    config(['app.url' => 'https://baharsyahjelajah.test']);

    $publishedPost = createSitemapPost([
        'slug' => [
            'id' => 'artikel-publik',
            'ms' => '',
            'en' => 'public-article',
        ],
    ]);
    $draftPost = createSitemapPost([
        'slug' => localizedSitemapValue('draft-post'),
        'status' => 'draft',
    ]);
    $scheduledPost = createSitemapPost([
        'slug' => localizedSitemapValue('scheduled-post'),
        'published_at' => now()->addDay(),
    ]);

    $activeTour = createSitemapTour();
    $activePackage = $activeTour->packages()->create([
        'name' => localizedSitemapValue('Public Package'),
        'slug' => [
            'id' => 'paket-publik',
            'ms' => 'pakej-awam',
            'en' => 'public-package',
        ],
        'duration_days' => 3,
        'duration_nights' => 2,
    ]);
    $inactiveTour = createSitemapTour([
        'slug' => localizedSitemapValue('inactive-tour'),
        'is_active' => false,
    ]);
    $inactivePackage = $inactiveTour->packages()->create([
        'name' => localizedSitemapValue('Inactive Package'),
        'slug' => localizedSitemapValue('inactive-package'),
        'duration_days' => 3,
        'duration_nights' => 2,
    ]);
    $deletedTour = createSitemapTour([
        'slug' => localizedSitemapValue('deleted-tour'),
    ]);
    $deletedTour->packages()->create([
        'name' => localizedSitemapValue('Deleted Tour Package'),
        'slug' => localizedSitemapValue('deleted-tour-package'),
        'duration_days' => 2,
        'duration_nights' => 1,
    ]);
    Tour::query()->whereKey($deletedTour)->update(['deleted_at' => now()]);

    $activeUmrah = UmrahPackage::factory()->create([
        'slug' => localizedSitemapValue('public-umrah'),
    ]);
    $inactiveUmrah = UmrahPackage::factory()->create([
        'slug' => localizedSitemapValue('inactive-umrah'),
        'is_active' => false,
    ]);
    $deletedUmrah = UmrahPackage::factory()->create([
        'slug' => localizedSitemapValue('deleted-umrah'),
    ]);
    $deletedUmrah->delete();

    $activeDestination = Destination::factory()->create(['slug' => 'public-destination']);
    $inactiveDestination = Destination::factory()->create([
        'slug' => 'inactive-destination',
        'is_active' => false,
    ]);

    $activeVehicle = Vehicle::factory()->create([
        'slug' => localizedSitemapValue('public-vehicle'),
    ]);
    $inactiveVehicle = Vehicle::factory()->create([
        'slug' => localizedSitemapValue('inactive-vehicle'),
        'is_active' => false,
    ]);
    $deletedVehicle = Vehicle::factory()->create([
        'slug' => localizedSitemapValue('deleted-vehicle'),
    ]);
    $deletedVehicle->delete();

    $activeVisa = VisaService::factory()->create(['slug' => 'public-visa']);
    $inactiveVisa = VisaService::factory()->create([
        'slug' => 'inactive-visa',
        'is_active' => false,
    ]);
    $inactiveCountry = Country::factory()->create(['is_active' => false]);
    $visaForInactiveCountry = VisaService::factory()->for($inactiveCountry)->create([
        'slug' => 'inactive-country-visa',
    ]);

    $originalPublicPath = public_path();
    $temporaryPublicPath = storage_path('framework/testing/sitemap-'.Str::uuid());
    File::ensureDirectoryExists($temporaryPublicPath);
    app()->usePublicPath($temporaryPublicPath);

    try {
        $generatedPath = app(SitemapService::class)->generate();

        $filenames = [
            'sitemap-pages.xml',
            'sitemap-posts.xml',
            'sitemap-tours.xml',
            'sitemap-umrah.xml',
            'sitemap-destinations.xml',
            'sitemap-vehicles.xml',
            'sitemap-visas.xml',
        ];

        expect($generatedPath)->toBe($temporaryPublicPath.DIRECTORY_SEPARATOR.'sitemap.xml')
            ->and(File::exists($generatedPath))->toBeTrue();

        $indexXml = File::get($generatedPath);

        foreach ($filenames as $filename) {
            expect(File::exists($temporaryPublicPath.DIRECTORY_SEPARATOR.$filename))->toBeTrue()
                ->and($indexXml)->toContain("<loc>https://baharsyahjelajah.test/{$filename}</loc>");
        }

        $pagesXml = File::get($temporaryPublicPath.DIRECTORY_SEPARATOR.'sitemap-pages.xml');
        expect($pagesXml)
            ->toContain('<loc>https://baharsyahjelajah.test/id</loc>')
            ->toContain('<loc>https://baharsyahjelajah.test/en</loc>')
            ->toContain('hreflang="ms" href="https://baharsyahjelajah.test/ms"')
            ->toContain('<loc>https://baharsyahjelajah.test/en/kontak</loc>')
            ->toContain('<loc>https://baharsyahjelajah.test/id/gallery</loc>')
            ->toContain('<loc>https://baharsyahjelajah.test/ms/testimonials</loc>')
            ->not->toContain('/shop')
            ->not->toContain('/design-system')
            ->not->toContain('set-currency')
            ->not->toContain('?page=')
            ->not->toContain('?type=')
            ->not->toContain('<priority>')
            ->not->toContain('<changefreq>');

        $postsXml = File::get($temporaryPublicPath.DIRECTORY_SEPARATOR.'sitemap-posts.xml');
        expect($postsXml)
            ->toContain('https://baharsyahjelajah.test/en/blog/'.$publishedPost->getTranslation('slug', 'en'))
            ->toContain('https://baharsyahjelajah.test/ms/blog/'.$publishedPost->getTranslation('slug', 'id'))
            ->toContain('<lastmod>'.$publishedPost->updated_at->toAtomString().'</lastmod>')
            ->not->toContain($draftPost->getTranslation('slug', 'id'))
            ->not->toContain($scheduledPost->getTranslation('slug', 'id'));

        $toursXml = File::get($temporaryPublicPath.DIRECTORY_SEPARATOR.'sitemap-tours.xml');
        expect($toursXml)
            ->toContain('https://baharsyahjelajah.test/en/tour/'.$activeTour->getTranslation('slug', 'en'))
            ->toContain('https://baharsyahjelajah.test/ms/tour/'
                .$activeTour->getTranslation('slug', 'ms')
                .'/package/'
                .$activePackage->getTranslation('slug', 'ms'))
            ->not->toContain($inactiveTour->getTranslation('slug', 'id'))
            ->not->toContain($inactivePackage->getTranslation('slug', 'id'))
            ->not->toContain('deleted-tour');

        $dynamicXml = collect([
            'sitemap-umrah.xml',
            'sitemap-destinations.xml',
            'sitemap-vehicles.xml',
            'sitemap-visas.xml',
        ])->map(fn (string $filename): string => File::get(
            $temporaryPublicPath.DIRECTORY_SEPARATOR.$filename,
        ))->implode("\n");

        expect($dynamicXml)
            ->toContain($activeUmrah->getTranslation('slug', 'en'))
            ->toContain($activeDestination->slug)
            ->toContain($activeVehicle->getTranslation('slug', 'ms'))
            ->toContain($activeVisa->slug)
            ->not->toContain($inactiveUmrah->getTranslation('slug', 'id'))
            ->not->toContain($deletedUmrah->getTranslation('slug', 'id'))
            ->not->toContain($inactiveDestination->slug)
            ->not->toContain($inactiveVehicle->getTranslation('slug', 'id'))
            ->not->toContain($deletedVehicle->getTranslation('slug', 'id'))
            ->not->toContain($inactiveVisa->slug)
            ->not->toContain($visaForInactiveCountry->slug)
            ->not->toContain('/booking')
            ->not->toContain('?page=');
    } finally {
        app()->usePublicPath($originalPublicPath);
        File::deleteDirectory($temporaryPublicPath);
    }
});

it('generates the sitemap from the artisan command', function () {
    $service = Mockery::mock(SitemapService::class);
    $service->shouldReceive('generate')
        ->once()
        ->andReturn(public_path('sitemap.xml'));
    app()->instance(SitemapService::class, $service);

    $this->artisan('sitemap:generate')
        ->expectsOutputToContain('Sitemap berhasil dibuat')
        ->assertSuccessful();
});

it('allows an administrator to generate the sitemap from general settings', function () {
    $this->actingAs(User::factory()->create([
        'email' => 'admin@baharsyahjelajah.com',
    ]));

    $service = Mockery::mock(SitemapService::class);
    $service->shouldReceive('generate')
        ->once()
        ->andReturn(public_path('sitemap.xml'));
    app()->instance(SitemapService::class, $service);

    Livewire::test(ManageGeneralSettings::class)
        ->callAction('generateSitemap')
        ->assertNotified('Sitemap berhasil dibuat');
});

it('schedules sitemap generation daily without overlapping', function () {
    $event = collect(app(Schedule::class)->events())
        ->first(fn ($event): bool => str_contains($event->command ?? '', 'sitemap:generate'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('0 3 * * *')
        ->and($event->timezone)->toBe('Asia/Jakarta')
        ->and($event->withoutOverlapping)->toBeTrue();
});

/** @return array<string, string> */
function localizedSitemapValue(string $value): array
{
    return [
        'id' => $value.'-id',
        'ms' => $value.'-ms',
        'en' => $value.'-en',
    ];
}

/** @param array<string, mixed> $attributes */
function createSitemapPost(array $attributes = []): Post
{
    $category = PostCategory::create([
        'name' => localizedSitemapValue('Sitemap Category '.Str::uuid()),
        'slug' => localizedSitemapValue('sitemap-category-'.Str::uuid()),
    ]);

    return Post::create(array_replace([
        'post_category_id' => $category->getKey(),
        'user_id' => User::factory()->create()->getKey(),
        'title' => localizedSitemapValue('Sitemap Post'),
        'slug' => [
            'id' => 'artikel-publik',
            'ms' => 'artikel-awam',
            'en' => 'public-article',
        ],
        'content' => localizedSitemapValue('Content'),
        'status' => 'published',
        'published_at' => now()->subDay(),
    ], $attributes));
}

/** @param array<string, mixed> $attributes */
function createSitemapTour(array $attributes = []): Tour
{
    return Tour::create(array_replace([
        'name' => localizedSitemapValue('Public Tour'),
        'slug' => [
            'id' => 'tour-publik',
            'ms' => 'lawatan-awam',
            'en' => 'public-tour',
        ],
        'tour_type' => TourType::Domestic,
        'currency' => 'IDR',
        'is_active' => true,
        'is_featured' => false,
    ], $attributes));
}
