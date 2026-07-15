<?php

namespace App\Http\Controllers;

use App\Enums\BannerPlacement;
use App\Enums\FaqContext;
use App\Models\Banner;
use App\Models\Faq;
use App\Models\Post;
use App\Models\Testimonial;
use App\Models\Tour;
use App\Models\UmrahPackage;
use App\Models\Vehicle;
use App\Models\VisaService;
use App\Settings\GeneralSettings;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(private GeneralSettings $generalSettings) {}

    public function __invoke(): View
    {
        $featuredTours = Tour::query()
            ->where('is_featured', true)
            ->where('is_active', true)
            ->with([
                'category',
                'packages' => fn ($query) => $query
                    ->oldest('id')
                    ->with(['media', 'tiers.priceTiers']),
            ])
            ->withCount('packages')
            ->latest()
            ->limit(3)
            ->get();

        $featuredUmrahPackages = UmrahPackage::query()
            ->active()
            ->with([
                'media',
                'prices',
                'upcomingDepartures' => fn ($query) => $query->limit(1),
            ])
            ->orderByDesc('is_featured')
            ->latest()
            ->limit(3)
            ->get();

        $featuredVehicles = Vehicle::query()
            ->active()
            ->with('media')
            ->orderByDesc('is_featured')
            ->orderBy('capacity_pax')
            ->limit(3)
            ->get();

        $featuredVisaServices = VisaService::query()
            ->publiclyAvailable()
            ->with(['country.media', 'media'])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(2)
            ->get();

        $testimonials = Testimonial::query()
            ->active()
            ->orderByDesc('is_featured')
            ->latest()
            ->limit(3)
            ->get();

        $latestPosts = Post::query()
            ->where('status', 'published')
            ->with('category')
            ->latest()
            ->limit(3)
            ->get();

        $heroBanner = Banner::query()
            ->with('media')
            ->active()
            ->withImage()
            ->currentlyVisible()
            ->forPlacement(BannerPlacement::HomeHero)
            ->ordered()
            ->first();

        $promoBanner = Banner::query()
            ->with('media')
            ->active()
            ->withImage()
            ->currentlyVisible()
            ->forPlacement(BannerPlacement::HomePromo)
            ->ordered()
            ->first();

        $faqs = Faq::query()
            ->active()
            ->forContext(FaqContext::Home)
            ->ordered()
            ->limit(8)
            ->get();

        $alternateUrls = collect(['id', 'ms', 'en'])
            ->mapWithKeys(fn (string $locale): array => [
                $locale => route('home', ['locale' => $locale]),
            ])
            ->all();
        $canonicalUrl = $alternateUrls[app()->getLocale()];
        $schemaGraph = [[
            '@type' => 'TravelAgency',
            '@id' => $canonicalUrl.'#organization',
            'name' => $this->localized($this->generalSettings->site_name),
            'url' => $canonicalUrl,
            'email' => $this->generalSettings->email,
            'telephone' => filled($this->generalSettings->whatsapp_number)
                ? '+'.$this->generalSettings->whatsapp_number
                : null,
            'address' => $this->localized($this->generalSettings->address),
        ]];

        if ($faqs->isNotEmpty()) {
            $schemaGraph[] = [
                '@type' => 'FAQPage',
                '@id' => $canonicalUrl.'#faq',
                'mainEntity' => $faqs->map(fn (Faq $faq): array => [
                    '@type' => 'Question',
                    'name' => $faq->question,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq->answer,
                    ],
                ])->all(),
            ];
        }

        $schemaJson = json_encode([
            '@context' => 'https://schema.org',
            '@graph' => $schemaGraph,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $whatsappNumber = $this->generalSettings->whatsapp_number;

        return view('pages.home', compact(
            'alternateUrls',
            'featuredTours',
            'featuredUmrahPackages',
            'featuredVehicles',
            'featuredVisaServices',
            'testimonials',
            'latestPosts',
            'heroBanner',
            'canonicalUrl',
            'faqs',
            'promoBanner',
            'schemaJson',
            'whatsappNumber',
        ));
    }

    /** @param array<string, string> $translations */
    private function localized(array $translations): string
    {
        return $translations[app()->getLocale()]
            ?? $translations['id']
            ?? $translations['en']
            ?? '';
    }
}
