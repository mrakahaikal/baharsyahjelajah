<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Tours\Categories;

use App\Models\Tour;
use App\Models\TourCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('get_tour_category')]
#[Title('Get Tour Category Details')]
#[Description('Get detailed information about a specific tour category by ID or slug, including its associated tours.')]
class GetTourCategoryTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The numeric ID of the tour category.'),
            'slug' => $schema->string()->description('The slug of the tour category (in id, en, or ms). Used if id is not provided.'),
            'locale' => $schema->string()->description('Preferred output locale ("id", "en", "ms"). Default: "id".'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $slug = $request->get('slug');
        $locale = (string) $request->get('locale', 'id');

        if (empty($id) && empty($slug)) {
            return Response::error('Either "id" or "slug" must be provided.');
        }

        $query = TourCategory::query()
            ->withCount(['tours', 'activeTours'])
            ->with(['tours' => fn ($q) => $q->withCount('packages')->orderByDesc('is_featured')->orderBy('id')]);

        $category = ! empty($id)
            ? $query->find((int) $id)
            : $query->whereLocalizedSlug((string) $slug)->first();

        if (! $category) {
            $identifier = ! empty($id) ? "ID {$id}" : "slug '{$slug}'";

            return Response::error("Tour category with {$identifier} not found.");
        }

        $tours = $category->tours->map(function (Tour $tour) use ($locale): array {
            return [
                'id' => $tour->id,
                'name' => $tour->getTranslation('name', $locale, false) ?: $tour->getTranslation('name', 'id', false),
                'slug' => $tour->localizedSlug($locale),
                'tour_type' => $tour->tour_type?->value,
                'currency' => $tour->currency,
                'is_active' => (bool) $tour->is_active,
                'is_featured' => (bool) $tour->is_featured,
                'packages_count' => (int) ($tour->packages_count ?? 0),
            ];
        })->all();

        return Response::json([
            'category' => [
                'id' => $category->id,
                'name' => $category->getTranslation('name', $locale, false) ?: $category->getTranslation('name', 'id', false),
                'slug' => $category->localizedSlug($locale),
                'icon' => $category->icon,
                'sort_order' => (int) $category->sort_order,
                'tours_count' => (int) ($category->tours_count ?? 0),
                'active_tours_count' => (int) ($category->active_tours_count ?? 0),
                'translations' => [
                    'name' => $category->getTranslations('name'),
                    'slug' => $category->getTranslations('slug'),
                ],
                'tours' => $tours,
                'created_at' => $category->created_at?->toIso8601String(),
                'updated_at' => $category->updated_at?->toIso8601String(),
            ],
            'locale' => $locale,
        ]);
    }
}
