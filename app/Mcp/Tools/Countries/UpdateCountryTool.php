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

#[Name('update_country')]
#[Title('Update Country')]
#[Description('Update an existing country details: multilingual metadata, ISO codes, currency, travel guidelines, or active/featured status.')]
class UpdateCountryTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The numeric ID of the country to update.'),
            'slug' => $schema->string()->description('The current slug of the country to update (used if "id" is omitted).'),
            'iso_code' => $schema->string()->description('The current ISO Alpha-2 or Alpha-3 code of the country to update (used if "id" and "slug" are omitted).'),
            'name' => $schema->string()->description('Updated name. String or JSON object with keys "id", "en", "ms".'),
            'custom_slug' => $schema->string()->description('Updated URL slug.'),
            'iso_alpha_2' => $schema->string()->description('Updated 2-letter ISO code.'),
            'iso_alpha_3' => $schema->string()->description('Updated 3-letter ISO code.'),
            'currency_code' => $schema->string()->description('Updated currency code.'),
            'capital_city' => $schema->string()->description('Updated capital city name.'),
            'language' => $schema->string()->description('Updated official language.'),
            'best_time_to_visit' => $schema->string()->description('Updated best time to visit.'),
            'travel_requirements_summary' => $schema->string()->description('Updated travel and visa requirements.'),
            'description' => $schema->string()->description('Updated overview description.'),
            'is_active' => $schema->boolean()->description('Updated active status.'),
            'is_featured' => $schema->boolean()->description('Updated featured status.'),
            'sort_order' => $schema->integer()->description('Updated sorting order.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $slug = $request->get('slug');
        $isoCode = $request->get('iso_code');

        if (empty($id) && empty($slug) && empty($isoCode)) {
            return Response::error('At least one of "id", "slug", or "iso_code" must be provided to identify the country.');
        }

        $query = Country::query();

        /** @var Country|null $country */
        if (! empty($id)) {
            $country = $query->find((int) $id);
        } elseif (! empty($slug)) {
            $country = $query->where('slug', (string) $slug)->first();
        } else {
            $code = Str::upper(trim((string) $isoCode));
            $country = $query->where(function ($q) use ($code): void {
                $q->where('iso_alpha_2', $code)->orWhere('iso_alpha_3', $code);
            })->first();
        }

        if (! $country) {
            $identifier = ! empty($id)
                ? "ID {$id}"
                : (! empty($slug) ? "slug '{$slug}'" : "ISO code '{$isoCode}'");

            return Response::error("Country with {$identifier} not found.");
        }

        $attributesToUpdate = [];

        // Name
        if ($request->has('name')) {
            $currentName = $country->getTranslations('name');
            $newName = $this->mergeTranslations($currentName, $request->get('name'));
            $attributesToUpdate['name'] = $newName;
        }

        // Slug
        if ($request->has('custom_slug')) {
            $newSlug = Str::slug((string) $request->get('custom_slug'));
            if (empty($newSlug)) {
                return Response::error('Custom slug cannot be empty.');
            }
            $slugConflict = Country::query()
                ->where('slug', $newSlug)
                ->where('id', '!=', $country->id)
                ->exists();
            if ($slugConflict) {
                return Response::error("A country with the slug '{$newSlug}' already exists.");
            }
            $attributesToUpdate['slug'] = $newSlug;
        }

        // ISO Alpha-2
        if ($request->has('iso_alpha_2')) {
            $iso2 = Str::upper(trim((string) $request->get('iso_alpha_2')));
            if (strlen($iso2) !== 2) {
                return Response::error('ISO Alpha-2 code must be exactly 2 letters.');
            }
            $iso2Conflict = Country::query()
                ->where('iso_alpha_2', $iso2)
                ->where('id', '!=', $country->id)
                ->exists();
            if ($iso2Conflict) {
                return Response::error("A country with ISO Alpha-2 code '{$iso2}' already exists.");
            }
            $attributesToUpdate['iso_alpha_2'] = $iso2;
        }

        // ISO Alpha-3
        if ($request->has('iso_alpha_3')) {
            $rawIso3 = $request->get('iso_alpha_3');
            if (empty($rawIso3)) {
                $attributesToUpdate['iso_alpha_3'] = null;
            } else {
                $iso3 = Str::upper(trim((string) $rawIso3));
                if (strlen($iso3) !== 3) {
                    return Response::error('ISO Alpha-3 code must be exactly 3 letters.');
                }
                $iso3Conflict = Country::query()
                    ->where('iso_alpha_3', $iso3)
                    ->where('id', '!=', $country->id)
                    ->exists();
                if ($iso3Conflict) {
                    return Response::error("A country with ISO Alpha-3 code '{$iso3}' already exists.");
                }
                $attributesToUpdate['iso_alpha_3'] = $iso3;
            }
        }

        // Currency Code
        if ($request->has('currency_code')) {
            $attributesToUpdate['currency_code'] = filled($request->get('currency_code'))
                ? Str::upper(trim((string) $request->get('currency_code')))
                : null;
        }

        // Multilingual attributes
        foreach ([
            'capital_city',
            'language',
            'best_time_to_visit',
            'travel_requirements_summary',
            'description',
        ] as $field) {
            if ($request->has($field)) {
                $currentVal = $country->getTranslations($field);
                $attributesToUpdate[$field] = $this->mergeTranslations($currentVal, $request->get($field));
            }
        }

        // Status & sorting
        if ($request->has('is_active')) {
            $attributesToUpdate['is_active'] = (bool) $request->get('is_active');
        }
        if ($request->has('is_featured')) {
            $attributesToUpdate['is_featured'] = (bool) $request->get('is_featured');
        }
        if ($request->has('sort_order')) {
            $attributesToUpdate['sort_order'] = (int) $request->get('sort_order');
        }

        if (! empty($attributesToUpdate)) {
            $country->update($attributesToUpdate);
        }

        $country->refresh();

        $countryName = $country->getTranslation('name', 'id', false);

        return Response::json([
            'message' => "Country '{$countryName}' updated successfully.",
            'country' => [
                'id' => $country->id,
                'name' => $country->getTranslation('name', 'id', false),
                'slug' => $country->slug,
                'iso_alpha_2' => $country->iso_alpha_2,
                'iso_alpha_3' => $country->iso_alpha_3,
                'currency_code' => $country->currency_code,
                'is_active' => $country->is_active,
                'is_featured' => $country->is_featured,
                'updated_at' => $country->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Merge current translations with input.
     *
     * @param  array<string, mixed>  $current
     * @return array<string, string>
     */
    protected function mergeTranslations(array $current, mixed $input): array
    {
        $normalized = $this->normalizeMultilingualInput($input);

        return array_merge($current, array_filter($normalized, fn ($val): bool => $val !== ''));
    }
}
