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

#[Name('featured_countries')]
#[Title('Daftar Negara Tujuan Unggulan')]
#[Description('Katalog kurasi negara-negara tujuan unggulan (is_featured=true) beserta metadata perjalanan ringkas dan tautan cepat.')]
#[Uri('countries://featured')]
#[MimeType('application/json')]
class FeaturedCountriesResource extends Resource
{
    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $countries = Country::query()
            ->active()
            ->featured()
            ->withCount(['destinations', 'visaServices', 'tourPackages'])
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
                'capital_city' => $c->getTranslation('capital_city', 'id', false),
                'cover_url' => $c->cover_url,
                'flag_url' => $c->flag_url,
                'connected_counts' => [
                    'destinations' => $c->destinations_count,
                    'visa_services' => $c->visa_services_count,
                    'tour_packages' => $c->tour_packages_count,
                ],
            ])
            ->all();

        return Response::json([
            'resource' => 'countries://featured',
            'total_featured_countries' => count($countries),
            'featured_countries' => $countries,
        ]);
    }
}
