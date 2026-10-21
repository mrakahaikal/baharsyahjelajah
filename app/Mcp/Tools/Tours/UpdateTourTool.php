<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Tours;

use App\Enums\TourType;
use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Country;
use App\Models\Tour;
use App\Models\TourCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('update_tour')]
#[Title('Update Tour')]
#[Description('Update an existing tour by ID, including multilingual fields, category, destination type, base currency, status flags, and country associations.')]
class UpdateTourTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Numeric ID of the tour to update.')->required(),
            'tour_category_id' => $schema->integer()->description('Updated tour category ID.'),
            'name' => $schema->union(['string', 'object'])->description('Updated tour name (plain string or multilingual map).'),
            'slug' => $schema->union(['string', 'object'])->description('Updated URL slug (plain string or multilingual map).'),
            'short_description' => $schema->union(['string', 'object'])->description('Updated short summary (plain string or multilingual map).'),
            'description' => $schema->union(['string', 'object'])->description('Updated full description (plain text, Markdown, or HTML; auto-converted to semantic HTML).'),
            'tour_type' => $schema->string()->description('Updated destination type: "domestic" or "international".'),
            'currency' => $schema->string()->description('Updated 3-letter base currency code.'),
            'country_ids' => $schema->array()->description('Updated array of numeric country IDs to sync.'),
            'is_active' => $schema->boolean()->description('Updated active status.'),
            'is_featured' => $schema->boolean()->description('Updated featured status.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        if (empty($id)) {
            return Response::error('The "id" parameter is required.');
        }

        $tour = Tour::find((int) $id);
        if (! $tour) {
            return Response::error("Tour with ID {$id} not found.");
        }

        if ($request->filled('tour_category_id')) {
            $categoryId = (int) $request->get('tour_category_id');
            if (! TourCategory::whereKey($categoryId)->exists()) {
                return Response::error("Tour category with ID {$categoryId} not found.");
            }
            $tour->tour_category_id = $categoryId;
        }

        if ($request->has('name') && ! empty($request->get('name'))) {
            $rawName = $request->get('name');
            $existing = $tour->getTranslations('name');
            $incoming = is_array($rawName) ? $rawName : $this->normalizeMultilingualInput($rawName);
            $tour->setTranslations('name', array_merge($existing, array_filter($incoming, fn ($v) => $v !== null && $v !== '')));
        }

        if ($request->has('slug') && ! empty($request->get('slug'))) {
            $rawSlug = $request->get('slug');
            $existing = $tour->getTranslations('slug');
            $incoming = is_array($rawSlug)
                ? array_map(fn ($v) => Str::slug((string) $v), $rawSlug)
                : array_map(fn ($v) => Str::slug((string) $v), $this->normalizeMultilingualInput($rawSlug));
            $tour->setTranslations('slug', array_merge($existing, array_filter($incoming, fn ($v) => $v !== '')));
        }

        if ($request->has('short_description') && ! empty($request->get('short_description'))) {
            $rawShort = $request->get('short_description');
            $existing = $tour->getTranslations('short_description');
            $incoming = is_array($rawShort) ? $rawShort : $this->normalizeMultilingualInput($rawShort);
            $tour->setTranslations('short_description', array_merge($existing, array_filter($incoming, fn ($v) => $v !== null && $v !== '')));
        }

        if ($request->has('description') && ! empty($request->get('description'))) {
            $rawDesc = $request->get('description');
            $existing = $tour->getTranslations('description');
            $incoming = is_array($rawDesc)
                ? array_map(fn ($v) => $this->ensureRichTextHtml((string) $v), $rawDesc)
                : $this->normalizeRichTextMultilingual($rawDesc);
            $tour->setTranslations('description', array_merge($existing, array_filter($incoming, fn ($v) => $v !== '')));
        }

        if ($request->filled('tour_type')) {
            $rawType = (string) $request->get('tour_type');
            $tourType = TourType::tryFrom($rawType);
            if (! $tourType) {
                return Response::error("Invalid tour_type '{$rawType}'. Allowed values: 'domestic', 'international'.");
            }
            $tour->tour_type = $tourType;
        }

        if ($request->filled('currency')) {
            $currency = strtoupper(trim((string) $request->get('currency')));
            if (strlen($currency) !== 3) {
                return Response::error('The "currency" parameter must be a valid 3-letter ISO currency code.');
            }
            $tour->currency = $currency;
        }

        if ($request->has('is_active')) {
            $tour->is_active = (bool) $request->get('is_active');
        }

        if ($request->has('is_featured')) {
            $tour->is_featured = (bool) $request->get('is_featured');
        }

        if ($request->has('country_ids') && is_array($request->get('country_ids'))) {
            $rawCountryIds = array_values(array_unique(array_map('intval', $request->get('country_ids'))));
            if (! empty($rawCountryIds)) {
                $existingCount = Country::whereIn('id', $rawCountryIds)->count();
                if ($existingCount !== count($rawCountryIds)) {
                    return Response::error('One or more provided country_ids do not exist.');
                }
            }
            $tour->countries()->sync($rawCountryIds);
        }

        $tour->save();
        $tour->load(['category', 'countries']);

        return Response::json([
            'message' => 'Tur berhasil diperbarui.',
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
                'updated_at' => $tour->updated_at?->toIso8601String(),
            ],
        ]);
    }
}
