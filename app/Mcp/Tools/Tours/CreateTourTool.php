<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Tours;

use App\Enums\TourType;
use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Country;
use App\Models\Tour;
use App\Models\TourCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('create_tour')]
#[Title('Create Tour')]
#[Description('Create a new tour with multilingual name, slug, short description, RichText HTML description, category, destination type (domestic/international), base currency, and associated countries.')]
class CreateTourTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'tour_category_id' => $schema->integer()->description('ID of the tour category.')->required(),
            'name' => $schema->union(['string', 'object'])->description('Tour name (plain string or multilingual map {"id": "...", "en": "...", "ms": "..."}). Required.'),
            'slug' => $schema->union(['string', 'object'])->description('Optional custom URL slug (plain string or multilingual map). Auto-generated from name if omitted.'),
            'short_description' => $schema->union(['string', 'object'])->description('Short summary (2-3 sentences) as plain string or multilingual map. Required.'),
            'description' => $schema->union(['string', 'object'])->description('Comprehensive tour description (plain text, Markdown, or HTML; auto-converted to semantic HTML) as string or multilingual map. Required.'),
            'tour_type' => $schema->string()->description('Tour destination type: "domestic" or "international". Default: "domestic".'),
            'currency' => $schema->string()->description('Base 3-letter currency code (e.g., "IDR", "MYR", "USD"). Default: "IDR".'),
            'country_ids' => $schema->array()->description('Optional array of numeric country IDs visited in this tour.'),
            'is_active' => $schema->boolean()->description('Whether the tour is active and publicly visible (default: true).'),
            'is_featured' => $schema->boolean()->description('Whether the tour is highlighted as featured (default: false).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $categoryId = $request->get('tour_category_id');
        if (empty($categoryId)) {
            return Response::error('The "tour_category_id" parameter is required.');
        }

        $category = TourCategory::find((int) $categoryId);
        if (! $category) {
            return Response::error("Tour category with ID {$categoryId} not found.");
        }

        $rawName = $request->get('name');
        if (empty($rawName)) {
            return Response::error('The "name" parameter is required.');
        }

        $name = $this->normalizeMultilingualInput($rawName);
        if (empty($name['id'])) {
            return Response::error('Tour name for Indonesian (id) cannot be empty.');
        }

        $rawShortDesc = $request->get('short_description');
        if (empty($rawShortDesc)) {
            return Response::error('The "short_description" parameter is required.');
        }
        $shortDescription = $this->normalizeMultilingualInput($rawShortDesc);

        $rawDesc = $request->get('description');
        if (empty($rawDesc)) {
            return Response::error('The "description" parameter is required.');
        }
        $description = $this->normalizeRichTextMultilingual($rawDesc);

        $slug = $this->resolveSlugTranslations($name, $request->get('slug'));

        $rawType = (string) $request->get('tour_type', 'domestic');
        $tourType = TourType::tryFrom($rawType);
        if (! $tourType) {
            return Response::error("Invalid tour_type '{$rawType}'. Allowed values: 'domestic', 'international'.");
        }

        $currency = strtoupper(trim((string) $request->get('currency', 'IDR')));
        if (strlen($currency) !== 3) {
            return Response::error('The "currency" parameter must be a valid 3-letter ISO currency code (e.g. IDR, MYR, USD).');
        }

        $countryIds = [];
        if ($request->has('country_ids') && is_array($request->get('country_ids'))) {
            $rawCountryIds = array_values(array_unique(array_map('intval', $request->get('country_ids'))));
            if (! empty($rawCountryIds)) {
                $existingCount = Country::whereIn('id', $rawCountryIds)->count();
                if ($existingCount !== count($rawCountryIds)) {
                    return Response::error('One or more provided country_ids do not exist.');
                }
                $countryIds = $rawCountryIds;
            }
        }

        $tour = Tour::create([
            'tour_category_id' => $category->id,
            'name' => $name,
            'slug' => $slug,
            'short_description' => $shortDescription,
            'description' => $description,
            'tour_type' => $tourType,
            'currency' => $currency,
            'is_active' => $request->has('is_active') ? (bool) $request->get('is_active') : true,
            'is_featured' => $request->has('is_featured') ? (bool) $request->get('is_featured') : false,
        ]);

        if (! empty($countryIds)) {
            $tour->countries()->sync($countryIds);
        }

        $tour->load(['category', 'countries']);

        return Response::json([
            'message' => 'Tur berhasil dibuat.',
            'tour' => [
                'id' => $tour->id,
                'tour_category_id' => $tour->tour_category_id,
                'name' => $tour->getTranslations('name'),
                'slug' => $tour->getTranslations('slug'),
                'short_description' => $tour->getTranslations('short_description'),
                'description' => $tour->getTranslations('description'),
                'tour_type' => $tour->tour_type?->value,
                'currency' => $tour->currency,
                'is_active' => (bool) $tour->is_active,
                'is_featured' => (bool) $tour->is_featured,
                'countries' => $tour->countries->map(fn (Country $c): array => [
                    'id' => $c->id,
                    'name' => $c->getTranslation('name', 'id', false),
                    'code' => $c->code,
                ])->all(),
                'created_at' => $tour->created_at?->toIso8601String(),
            ],
        ]);
    }
}
