<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Tours\Categories;

use App\Models\TourCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_tour_categories')]
#[Title('List Tour Categories')]
#[Description('Get a paginated or filtered list of tour categories with total and active tour counts.')]
class ListTourCategoriesTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Optional search keyword matching category name or slug across languages.'),
            'locale' => $schema->string()->description('Preferred output locale ("id", "en", "ms"). Default: "id".'),
            'per_page' => $schema->integer()->description('Number of categories per page (1-100, default: 20).'),
            'page' => $schema->integer()->description('Page number for pagination (default: 1).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $locale = (string) $request->get('locale', 'id');
        $perPage = min(100, max(1, (int) $request->get('per_page', 20)));
        $page = max(1, (int) $request->get('page', 1));

        $query = TourCategory::query()
            ->withCount(['tours', 'activeTours']);

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function (Builder $sub) use ($search): void {
                $sub->where('name->id', 'like', "%{$search}%")
                    ->orWhere('name->en', 'like', "%{$search}%")
                    ->orWhere('name->ms', 'like', "%{$search}%")
                    ->orWhere('slug->id', 'like', "%{$search}%")
                    ->orWhere('slug->en', 'like', "%{$search}%")
                    ->orWhere('slug->ms', 'like', "%{$search}%");
            });
        }

        $paginator = $query->ordered()->orderBy('id')->paginate(perPage: $perPage, page: $page);

        $categories = collect($paginator->items())->map(function (TourCategory $category) use ($locale): array {
            return [
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
                'created_at' => $category->created_at?->toIso8601String(),
                'updated_at' => $category->updated_at?->toIso8601String(),
            ];
        })->all();

        return Response::json([
            'categories' => $categories,
            'pagination' => [
                'total' => $paginator->total(),
                'count' => count($categories),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
            ],
            'locale' => $locale,
        ]);
    }
}
