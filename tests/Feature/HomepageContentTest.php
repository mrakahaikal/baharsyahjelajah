<?php

use App\Enums\BannerCtaType;
use App\Enums\BannerPlacement;
use App\Enums\FaqCategory;
use App\Enums\FaqContext;
use App\Filament\Resources\Banners\Pages\ManageBanners;
use App\Filament\Resources\Faqs\Pages\ManageFaqs;
use App\Models\Banner;
use App\Models\Faq;
use App\Models\User;
use Database\Seeders\BannerSeeder;
use Database\Seeders\FaqSeeder;
use Filament\Actions\CreateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('renders the eligible banner placements and homepage faqs', function () {
    $now = now()->startOfMinute();

    Banner::create([
        'title' => localizedContent('Future hero'),
        'subtitle' => localizedContent('Not eligible yet'),
        'image_path' => 'https://example.com/future.jpg',
        'placement' => BannerPlacement::HomeHero,
        'sort_order' => 0,
        'starts_at' => $now->copy()->addDay(),
        'is_active' => true,
    ]);

    Banner::create([
        'title' => localizedContent('Current hero'),
        'subtitle' => localizedContent('Visible hero subtitle'),
        'cta_label' => localizedContent('Explore tours'),
        'image_path' => 'https://example.com/hero.jpg',
        'placement' => BannerPlacement::HomeHero,
        'cta_type' => BannerCtaType::Route,
        'cta_value' => 'tour.index',
        'sort_order' => 2,
        'starts_at' => $now->copy()->subDay(),
        'ends_at' => $now->copy()->addDay(),
        'is_active' => true,
    ]);

    Banner::create([
        'title' => localizedContent('Homepage promotion'),
        'subtitle' => localizedContent('A focused promotion'),
        'cta_label' => localizedContent('Read more'),
        'image_path' => 'https://example.com/promo.jpg',
        'placement' => BannerPlacement::HomePromo,
        'cta_type' => BannerCtaType::Url,
        'cta_value' => 'https://example.com/promo',
        'is_active' => true,
    ]);

    Faq::create([
        'question' => localizedContent('Visible homepage question?'),
        'answer' => localizedContent('Visible homepage answer.'),
        'category' => FaqCategory::General,
        'contexts' => [FaqContext::Home->value, FaqContext::Tour->value],
        'is_active' => true,
    ]);

    Faq::create([
        'question' => localizedContent('Tour only question?'),
        'answer' => localizedContent('Tour only answer.'),
        'category' => FaqCategory::Tour,
        'contexts' => [FaqContext::Tour->value],
        'is_active' => true,
    ]);

    Faq::create([
        'question' => localizedContent('Inactive homepage question?'),
        'answer' => localizedContent('Inactive homepage answer.'),
        'category' => FaqCategory::General,
        'contexts' => [FaqContext::Home->value],
        'is_active' => false,
    ]);

    get('/en')
        ->assertSuccessful()
        ->assertSee('Current hero')
        ->assertDontSee('Future hero')
        ->assertSee('Homepage promotion')
        ->assertSee('Visible homepage question?')
        ->assertDontSee('Tour only question?')
        ->assertDontSee('Inactive homepage question?')
        ->assertSee('href="'.route('tour.index', ['locale' => 'en']).'"', false)
        ->assertSee('"@type":"FAQPage"', false)
        ->assertSee('"name":"Visible homepage question?"', false)
        ->assertDontSee('"name":"Tour only question?"', false);
});

it('resolves only supported and safe banner cta targets', function () {
    $banner = new Banner([
        'cta_type' => BannerCtaType::Route,
        'cta_value' => 'tour.index',
    ]);

    expect($banner->ctaUrl('ms'))->toBe(route('tour.index', ['locale' => 'ms']))
        ->and($banner->opensCtaInNewTab())->toBeFalse();

    $banner->cta_value = 'filament.admin.resources.banners.index';

    expect($banner->cta_url)->toBeNull();

    $banner->cta_type = BannerCtaType::Url;
    $banner->cta_value = 'javascript:alert(1)';

    expect($banner->cta_url)->toBeNull();

    $banner->cta_value = 'https://example.com/offers';

    expect($banner->cta_url)->toBe('https://example.com/offers')
        ->and($banner->opensCtaInNewTab())->toBeTrue();
});

it('creates reusable faq content from filament', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(ManageFaqs::class)
        ->callAction(CreateAction::class, data: [
            'category' => FaqCategory::Payment->value,
            'contexts' => [FaqContext::Home->value, FaqContext::Booking->value],
            'question' => localizedContent('How do I pay?'),
            'answer' => localizedContent('Follow the payment instructions.'),
            'sort_order' => 3,
            'is_active' => true,
        ])
        ->assertHasNoActionErrors()
        ->assertNotified();

    $faq = Faq::query()->sole();

    expect($faq->category)->toBe(FaqCategory::Payment)
        ->and($faq->contexts)->toBe([FaqContext::Home->value, FaqContext::Booking->value]);
});

it('creates a scheduled media banner from filament', function () {
    $this->actingAs(User::factory()->create());
    Storage::fake('public');

    Livewire::test(ManageBanners::class)
        ->callAction(CreateAction::class, data: [
            'title' => localizedContent('Seasonal promotion'),
            'subtitle' => localizedContent('Available for a limited period.'),
            'cta_label' => localizedContent('View tours'),
            'image' => [UploadedFile::fake()->image('banner.jpg', 1600, 900)],
            'placement' => BannerPlacement::HomePromo->value,
            'cta_type' => BannerCtaType::Route->value,
            'cta_value' => 'tour.index',
            'sort_order' => 1,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ])
        ->assertHasNoActionErrors()
        ->assertNotified();

    $banner = Banner::query()->sole();

    expect($banner->placement)->toBe(BannerPlacement::HomePromo)
        ->and($banner->getMedia(Banner::MEDIA_COLLECTION_IMAGE))->toHaveCount(1);
});

it('seeds banners and faqs idempotently', function () {
    $this->seed([BannerSeeder::class, FaqSeeder::class]);

    $bannerCount = Banner::query()->count();
    $faqCount = Faq::query()->count();

    $this->seed([BannerSeeder::class, FaqSeeder::class]);

    expect($bannerCount)->toBeGreaterThan(0)
        ->and($faqCount)->toBeGreaterThan(0)
        ->and(Banner::query()->count())->toBe($bannerCount)
        ->and(Faq::query()->count())->toBe($faqCount);
});

/** @return array{id: string, en: string, ms: string} */
function localizedContent(string $value): array
{
    return [
        'id' => $value,
        'en' => $value,
        'ms' => $value,
    ];
}
