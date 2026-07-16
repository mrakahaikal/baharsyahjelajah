<?php

use App\Enums\StaticSeoPage;
use App\Filament\Pages\ManageSeoSettings;
use App\Models\User;
use App\Services\SeoMetadataResolver;
use App\Settings\SeoSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('resolves localized page overrides with the configured fallback locale', function (): void {
    Storage::fake('public');
    $values = seoSettingsValues([
        'home' => [
            'title' => ['id' => 'Judul Indonesia', 'en' => 'Custom English Title', 'ms' => null],
            'description' => ['id' => null, 'en' => 'Custom English Description', 'ms' => null],
            'og_title' => ['id' => null, 'en' => 'English Social Title', 'ms' => null],
            'og_description' => ['id' => null, 'en' => null, 'ms' => null],
            'og_image' => 'seo/pages/home.webp',
        ],
    ]);
    $values['og_image'] = 'seo/global.webp';
    SeoSettings::fake($values, false);

    $resolver = app(SeoMetadataResolver::class);
    $english = $resolver->resolve(
        page: StaticSeoPage::Home,
        locale: 'en',
        fallbackTitle: 'Default English Title',
        fallbackDescription: 'Default English Description',
        fallbackOgImage: '/images/default.webp',
        canonicalUrl: 'https://example.com/en',
    );
    $malay = $resolver->resolve(
        page: StaticSeoPage::Home,
        locale: 'ms',
        fallbackTitle: 'Tajuk Bawaan Melayu',
        fallbackDescription: 'Penerangan Bawaan Melayu',
    );
    $dynamic = $resolver->resolve(
        page: null,
        locale: 'en',
        fallbackTitle: 'Dynamic Model Title',
        fallbackOgImage: 'https://cdn.example.com/model.webp',
    );
    $globalFallback = $resolver->resolve(
        page: StaticSeoPage::ContactIndex,
        locale: 'en',
    );

    expect($english->title)->toBe('Custom English Title')
        ->and($english->description)->toBe('Custom English Description')
        ->and($english->ogTitle)->toBe('English Social Title')
        ->and($english->ogDescription)->toBe('Custom English Description')
        ->and($english->ogImage)->toEndWith('/storage/seo/pages/home.webp')
        ->and($english->canonicalUrl)->toBe('https://example.com/en')
        ->and($malay->title)->toBe('Judul Indonesia')
        ->and($malay->description)->toBe('Penerangan Bawaan Melayu')
        ->and($dynamic->title)->toBe('Dynamic Model Title')
        ->and($dynamic->ogImage)->toBe('https://cdn.example.com/model.webp')
        ->and($globalFallback->ogImage)->toEndWith('/storage/seo/global.webp');
});

it('falls back managed metadata to Indonesian before generic defaults', function (): void {
    SeoSettings::fake(seoSettingsValues([
        'tour_index' => [
            'title' => ['id' => 'Katalog Tour Indonesia', 'en' => null, 'ms' => null],
            'description' => ['id' => 'Deskripsi katalog Indonesia.', 'en' => null, 'ms' => null],
            'og_title' => ['id' => 'Bagikan Katalog Tour', 'en' => null, 'ms' => null],
            'og_description' => ['id' => 'Deskripsi berbagi Indonesia.', 'en' => null, 'ms' => null],
        ],
    ]), false);

    $metadata = app(SeoMetadataResolver::class)->resolve(
        page: StaticSeoPage::TourIndex,
        locale: 'en',
        fallbackTitle: 'Default English Tour Title',
        fallbackDescription: 'Default English tour description.',
    );

    expect($metadata->title)->toBe('Katalog Tour Indonesia')
        ->and($metadata->description)->toBe('Deskripsi katalog Indonesia.')
        ->and($metadata->ogTitle)->toBe('Bagikan Katalog Tour')
        ->and($metadata->ogDescription)->toBe('Deskripsi berbagi Indonesia.');
});

it('renders one complete set of managed metadata on a static page', function (): void {
    SeoSettings::fake(seoSettingsValues([
        'home' => [
            'title' => ['id' => 'Beranda SEO Terkelola', 'en' => null, 'ms' => null],
            'description' => ['id' => 'Deskripsi SEO terkelola untuk beranda.', 'en' => null, 'ms' => null],
            'og_title' => ['id' => 'Judul Berbagi Beranda', 'en' => null, 'ms' => null],
            'og_description' => ['id' => null, 'en' => null, 'ms' => null],
            'og_image' => null,
        ],
    ]), false);

    $response = get('/id')
        ->assertSuccessful()
        ->assertSee('<title>Beranda SEO Terkelola</title>', false)
        ->assertSee('<meta name="description" content="Deskripsi SEO terkelola untuk beranda.">', false)
        ->assertSee('<meta property="og:title" content="Judul Berbagi Beranda">', false)
        ->assertSee('<meta property="og:description" content="Deskripsi SEO terkelola untuk beranda.">', false)
        ->assertSee('<meta name="twitter:title" content="Judul Berbagi Beranda">', false)
        ->assertSee('<link rel="canonical" href="'.route('home', ['locale' => 'id']).'">', false);

    expect(substr_count($response->getContent(), '<title>'))->toBe(1)
        ->and(substr_count($response->getContent(), 'property="og:title"'))->toBe(1)
        ->and(substr_count($response->getContent(), 'name="twitter:title"'))->toBe(1);
});

it('persists localized page metadata from the filament settings page', function (): void {
    $this->actingAs(User::factory()->create([
        'email' => 'seo-admin@baharsyahjelajah.com',
    ]));

    $settings = app(SeoSettings::class);
    $pages = $settings->pages;
    data_set($pages, 'tour_index.title.en', 'Managed Tour SEO');
    data_set($pages, 'tour_index.description.en', 'Managed English tour listing description.');
    data_set($pages, 'tour_index.og_title.en', 'Share Our Tours');

    Livewire::test(ManageSeoSettings::class)
        ->fillForm(['pages' => $pages])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $savedPages = $settings->refresh()->pages;

    expect(data_get($savedPages, 'tour_index.title.en'))->toBe('Managed Tour SEO')
        ->and(data_get($savedPages, 'tour_index.description.en'))->toBe('Managed English tour listing description.')
        ->and(data_get($savedPages, 'tour_index.og_title.en'))->toBe('Share Our Tours');
});

/**
 * @param  array<string, array<string, mixed>>  $pageOverrides
 * @return array<string, mixed>
 */
function seoSettingsValues(array $pageOverrides = []): array
{
    $emptyPage = [
        'title' => ['id' => null, 'en' => null, 'ms' => null],
        'description' => ['id' => null, 'en' => null, 'ms' => null],
        'og_title' => ['id' => null, 'en' => null, 'ms' => null],
        'og_description' => ['id' => null, 'en' => null, 'ms' => null],
        'og_image' => null,
    ];
    $pages = collect(StaticSeoPage::cases())
        ->mapWithKeys(fn (StaticSeoPage $page): array => [$page->value => $emptyPage])
        ->all();

    foreach ($pageOverrides as $page => $values) {
        $pages[$page] = array_replace_recursive($pages[$page], $values);
    }

    return [
        'og_title' => ['id' => 'Global Indonesia', 'en' => 'Global English', 'ms' => 'Global Melayu'],
        'og_description' => ['id' => 'Deskripsi global', 'en' => 'Global description', 'ms' => 'Penerangan global'],
        'og_image' => null,
        'pages' => $pages,
        'google_analytics_id' => null,
        'meta_pixel_id' => null,
    ];
}
