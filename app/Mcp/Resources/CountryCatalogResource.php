<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Models\Country;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('country_catalog')]
#[Title('Katalog Negara & Profil Wisata')]
#[Description('Katalog terstruktur seluruh negara aktif, kode ISO Alpha-2 & Alpha-3, mata uang, ibukota, bahasa, musim terbaik berkunjung, syarat perjalanan, dan statistik konten terhubung.')]
#[Uri('countries://catalog')]
#[MimeType('application/json')]
class CountryCatalogResource extends Resource
{
    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $countries = Country::query()
            ->active()
            ->withCount(['destinations', 'visaServices', 'tourPackages', 'umrahPackages', 'posts'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Country $c): array => [
                'id' => $c->id,
                'name' => [
                    'id' => $c->getTranslation('name', 'id', false),
                    'en' => $c->getTranslation('name', 'en', false),
                    'ms' => $c->getTranslation('name', 'ms', false),
                ],
                'slug' => $c->slug,
                'iso_alpha_2' => $c->iso_alpha_2,
                'iso_alpha_3' => $c->iso_alpha_3,
                'currency_code' => $c->currency_code,
                'capital_city' => [
                    'id' => $c->getTranslation('capital_city', 'id', false),
                    'en' => $c->getTranslation('capital_city', 'en', false),
                    'ms' => $c->getTranslation('capital_city', 'ms', false),
                ],
                'language' => [
                    'id' => $c->getTranslation('language', 'id', false),
                    'en' => $c->getTranslation('language', 'en', false),
                    'ms' => $c->getTranslation('language', 'ms', false),
                ],
                'best_time_to_visit' => [
                    'id' => $c->getTranslation('best_time_to_visit', 'id', false),
                    'en' => $c->getTranslation('best_time_to_visit', 'en', false),
                    'ms' => $c->getTranslation('best_time_to_visit', 'ms', false),
                ],
                'travel_requirements_summary' => [
                    'id' => $c->getTranslation('travel_requirements_summary', 'id', false),
                    'en' => $c->getTranslation('travel_requirements_summary', 'en', false),
                    'ms' => $c->getTranslation('travel_requirements_summary', 'ms', false),
                ],
                'is_featured' => $c->is_featured,
                'sort_order' => $c->sort_order,
                'cover_url' => $c->cover_url,
                'flag_url' => $c->flag_url,
                'connected_counts' => [
                    'destinations' => $c->destinations_count,
                    'visa_services' => $c->visa_services_count,
                    'tour_packages' => $c->tour_packages_count,
                    'umrah_packages' => $c->umrah_packages_count,
                    'articles' => $c->posts_count,
                ],
            ])
            ->all();

        return Response::json([
            'resource' => 'countries://catalog',
            'total_active_countries' => count($countries),
            'countries' => $countries,
        ]);
    }
}
