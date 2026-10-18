<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Countries;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Country;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('create_country')]
#[Title('Create Country')]
#[Description('Create a new country with multilingual details, ISO alpha codes, currency, and travel requirements.')]
class CreateCountryTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Country name. Can be a string (e.g. "Arab Saudi") or a JSON string / object with keys "id", "en", "ms".')->required(),
            'iso_alpha_2' => $schema->string()->description('2-letter ISO country code (e.g. "SA", "ID", "EG", "MY").')->required(),
            'slug' => $schema->string()->description('Optional custom URL slug. If omitted, generated automatically from Indonesian name.'),
            'iso_alpha_3' => $schema->string()->description('Optional 3-letter ISO country code (e.g. "SAU", "IDN", "EGY", "MYS").'),
            'currency_code' => $schema->string()->description('Optional currency ISO code (e.g. "SAR", "IDR", "USD", "MYR").'),
            'capital_city' => $schema->string()->description('Capital city name. String or JSON object with keys "id", "en", "ms".'),
            'language' => $schema->string()->description('Official language. String or JSON object with keys "id", "en", "ms".'),
            'best_time_to_visit' => $schema->string()->description('Best time to visit. String or JSON object with keys "id", "en", "ms".'),
            'travel_requirements_summary' => $schema->string()->description('Brief visa and travel requirements summary. String or JSON object with keys "id", "en", "ms".'),
            'description' => $schema->string()->description('Country description or tourism overview. String or JSON object with keys "id", "en", "ms".'),
            'is_active' => $schema->boolean()->description('Whether the country is active and selectable (default: true).'),
            'is_featured' => $schema->boolean()->description('Whether the country is featured on homepage / bento grid (default: false).'),
            'sort_order' => $schema->integer()->description('Display sorting order (default: 0).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $rawName = $request->get('name');
        if (empty($rawName)) {
            return Response::error('The "name" parameter is required.');
        }

        $rawIso2 = $request->get('iso_alpha_2');
        if (empty($rawIso2) || strlen(trim((string) $rawIso2)) !== 2) {
            return Response::error('The "iso_alpha_2" parameter is required and must be exactly 2 letters (e.g. "SA", "ID").');
        }
        $isoAlpha2 = Str::upper(trim((string) $rawIso2));

        // Check iso_alpha_2 uniqueness
        if (Country::query()->where('iso_alpha_2', $isoAlpha2)->exists()) {
            return Response::error("A country with ISO Alpha-2 code '{$isoAlpha2}' already exists.");
        }

        // Validate iso_alpha_3 if provided
        $rawIso3 = $request->get('iso_alpha_3');
        $isoAlpha3 = null;
        if (! empty($rawIso3)) {
            $isoAlpha3 = Str::upper(trim((string) $rawIso3));
            if (strlen($isoAlpha3) !== 3) {
                return Response::error('The "iso_alpha_3" parameter must be exactly 3 letters (e.g. "SAU", "IDN").');
            }
            if (Country::query()->where('iso_alpha_3', $isoAlpha3)->exists()) {
                return Response::error("A country with ISO Alpha-3 code '{$isoAlpha3}' already exists.");
            }
        }

        $name = $this->normalizeMultilingualInput($rawName);
        if (empty($name['id'])) {
            return Response::error('Country name for Indonesian (id) cannot be empty.');
        }

        // Resolve slug
        $rawSlug = $request->get('slug');
        $slug = ! empty($rawSlug) ? Str::slug((string) $rawSlug) : Str::slug($name['id']);
        if (empty($slug)) {
            $slug = 'country-'.strtolower($isoAlpha2);
        }

        if (Country::query()->where('slug', $slug)->exists()) {
            if (! empty($rawSlug)) {
                return Response::error("A country with the slug '{$slug}' already exists.");
            }
            $slug .= '-'.strtolower($isoAlpha2);
        }

        $currencyCode = filled($request->get('currency_code')) ? Str::upper(trim((string) $request->get('currency_code'))) : null;
        $description = filled($request->get('description')) ? $this->normalizeMultilingualInput($request->get('description')) : null;
        $capitalCity = filled($request->get('capital_city')) ? $this->normalizeMultilingualInput($request->get('capital_city')) : null;
        $language = filled($request->get('language')) ? $this->normalizeMultilingualInput($request->get('language')) : null;
        $bestTime = filled($request->get('best_time_to_visit')) ? $this->normalizeMultilingualInput($request->get('best_time_to_visit')) : null;
        $requirements = filled($request->get('travel_requirements_summary')) ? $this->normalizeMultilingualInput($request->get('travel_requirements_summary')) : null;

        $isActive = (bool) $request->get('is_active', true);
        $isFeatured = (bool) $request->get('is_featured', false);
        $sortOrder = (int) $request->get('sort_order', 0);

        /** @var Country $country */
        $country = Country::create([
            'name' => $name,
            'slug' => $slug,
            'iso_alpha_2' => $isoAlpha2,
            'iso_alpha_3' => $isoAlpha3,
            'currency_code' => $currencyCode,
            'capital_city' => $capitalCity,
            'language' => $language,
            'best_time_to_visit' => $bestTime,
            'travel_requirements_summary' => $requirements,
            'description' => $description,
            'is_active' => $isActive,
            'is_featured' => $isFeatured,
            'sort_order' => $sortOrder,
        ]);

        $countryName = $country->getTranslation('name', 'id', false);

        return Response::json([
            'message' => "Country '{$countryName}' created successfully.",
            'country' => [
                'id' => $country->id,
                'name' => $country->getTranslation('name', 'id', false),
                'slug' => $country->slug,
                'iso_alpha_2' => $country->iso_alpha_2,
                'iso_alpha_3' => $country->iso_alpha_3,
                'currency_code' => $country->currency_code,
                'is_active' => $country->is_active,
                'is_featured' => $country->is_featured,
                'translations' => [
                    'name' => $country->getTranslations('name'),
                    'capital_city' => $country->getTranslations('capital_city'),
                    'description' => $country->getTranslations('description'),
                ],
                'created_at' => $country->created_at?->toIso8601String(),
            ],
        ]);
    }
}
