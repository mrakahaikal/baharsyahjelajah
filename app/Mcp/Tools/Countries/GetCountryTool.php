<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Countries;

use App\Models\Country;
use App\Models\Destination;
use App\Models\TourPackage;
use App\Models\VisaService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('get_country')]
#[Title('Get Country')]
#[Description('Retrieve detailed country profile, translations, travel metadata, associated destinations, and visa services by ID, slug, or ISO code.')]
class GetCountryTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The numeric ID of the country.'),
            'slug' => $schema->string()->description('The URL slug of the country (used if "id" is omitted).'),
            'iso_code' => $schema->string()->description('The 2-letter or 3-letter ISO code (e.g. "ID", "SA", "EGY", "MYS"). Case-insensitive.'),
            'locale' => $schema->string()->description('Preferred display locale ("id", "en", or "ms"). Default is "id".'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $slug = $request->get('slug');
        $isoCode = $request->get('iso_code');

        if (empty($id) && empty($slug) && empty($isoCode)) {
            return Response::error('At least one of "id", "slug", or "iso_code" must be provided.');
        }

        $query = Country::query()->with([
            'destinations' => fn ($q) => $q->latest('id')->limit(10),
            'visaServices' => fn ($q) => $q->latest('id')->limit(10),
            'tourPackages' => fn ($q) => $q->latest('id')->limit(5),
        ]);

        /** @var Country|null $country */
        if (! empty($id)) {
            $country = $query->find((int) $id);
        } elseif (! empty($slug)) {
            $country = $query->where('slug', (string) $slug)->first();
        } else {
            $code = Str::upper(trim((string) $isoCode));
            $country = $query->where(function ($q) use ($code): void {
                $q->where('iso_alpha_2', $code)
                    ->orWhere('iso_alpha_3', $code);
            })->first();
        }

        if (! $country) {
            $identifier = ! empty($id)
                ? "ID {$id}"
                : (! empty($slug) ? "slug '{$slug}'" : "ISO code '{$isoCode}'");

            return Response::error("Country with {$identifier} not found.");
        }

        $locale = (string) $request->get('locale', 'id');
        if (! in_array($locale, ['id', 'en', 'ms'], true)) {
            $locale = 'id';
        }

        return Response::json([
            'country' => [
                'id' => $country->id,
                'name' => $country->getTranslation('name', $locale, false) ?: $country->getTranslation('name', 'id', false),
                'slug' => $country->slug,
                'iso_alpha_2' => $country->iso_alpha_2,
                'iso_alpha_3' => $country->iso_alpha_3,
                'currency_code' => $country->currency_code,
                'capital_city' => $country->getTranslation('capital_city', $locale, false),
                'language' => $country->getTranslation('language', $locale, false),
                'best_time_to_visit' => $country->getTranslation('best_time_to_visit', $locale, false),
                'travel_requirements_summary' => $country->getTranslation('travel_requirements_summary', $locale, false),
                'description' => $country->getTranslation('description', $locale, false),
                'is_active' => $country->is_active,
                'is_featured' => $country->is_featured,
                'sort_order' => $country->sort_order,
                'flag_url' => $country->flag_url,
                'cover_url' => $country->cover_url,
                'connected_content' => [
                    'destinations' => $country->destinations->map(fn (Destination $d) => [
                        'id' => $d->id,
                        'name' => $d->getTranslation('name', $locale, false) ?: $d->getTranslation('name', 'id', false),
                        'slug' => $d->slug,
                    ])->values()->all(),
                    'visa_services' => $country->visaServices->map(fn (VisaService $v) => [
                        'id' => $v->id,
                        'name' => is_array($v->name) ? ($v->name[$locale] ?? $v->name['id'] ?? '') : $v->name,
                    ])->values()->all(),
                    'tours' => $country->tourPackages->map(fn (TourPackage $tp) => [
                        'id' => $tp->id,
                        'name' => is_array($tp->name) ? ($tp->name[$locale] ?? $tp->name['id'] ?? '') : $tp->name,
                        'slug' => $tp->slug,
                    ])->values()->all(),
                ],
                'translations' => [
                    'name' => $country->getTranslations('name'),
                    'description' => $country->getTranslations('description'),
                    'capital_city' => $country->getTranslations('capital_city'),
                    'language' => $country->getTranslations('language'),
                    'best_time_to_visit' => $country->getTranslations('best_time_to_visit'),
                    'travel_requirements_summary' => $country->getTranslations('travel_requirements_summary'),
                ],
                'created_at' => $country->created_at?->toIso8601String(),
                'updated_at' => $country->updated_at?->toIso8601String(),
            ],
        ]);
    }
}
