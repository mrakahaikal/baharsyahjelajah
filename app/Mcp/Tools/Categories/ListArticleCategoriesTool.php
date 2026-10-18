<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Categories;

use App\Models\PostCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_article_categories')]
#[Title('List Article Categories')]
#[Description('Get a paginated or filtered list of article categories with article counts and multilingual support.')]
class ListArticleCategoriesTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Optional search term to filter categories by name or slug across languages.'),
            'locale' => $schema->string()->description('The locale for primary string fields ("id", "en", "ms"). Default: "id".'),
            'per_page' => $schema->integer()->description('Number of categories per page (default: 20, min: 1, max: 100).'),
            'with_counts' => $schema->boolean()->description('Whether to include the total count of articles in each category (default: true).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'locale' => ['nullable', 'string', 'in:id,en,ms'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'with_counts' => ['nullable', 'boolean'],
        ]);

        $search = $request->get('search');
        $locale = (string) ($request->get('locale') ?? 'id');
        $perPage = (int) ($request->get('per_page') ?? 20);
        $withCounts = $request->has('with_counts') ? (bool) $request->get('with_counts') : true;

        $query = PostCategory::query();

        if (! empty($search)) {
            $query->where(function ($q) use ($search): void {
                $q->where('name->id', 'like', "%{$search}%")
                    ->orWhere('name->en', 'like', "%{$search}%")
                    ->orWhere('name->ms', 'like', "%{$search}%")
                    ->orWhere('slug->id', 'like', "%{$search}%")
                    ->orWhere('slug->en', 'like', "%{$search}%")
                    ->orWhere('slug->ms', 'like', "%{$search}%");
            });
        }

        if ($withCounts) {
            $query->withCount('posts');
        }

        $categories = $query->latest('id')->paginate($perPage);

        $data = collect($categories->items())->map(function (PostCategory $category) use ($locale): array {
            return [
                'id' => $category->id,
                'name' => $category->getTranslation('name', $locale, false) ?: ($category->getTranslation('name', 'id', false) ?: ''),
                'slug' => $category->localizedSlug($locale),
                'description' => $category->getTranslation('description', $locale, false),
                'translations' => [
                    'name' => $category->getTranslations('name'),
                    'slug' => $category->getTranslations('slug'),
                    'description' => $category->getTranslations('description'),
                ],
                'articles_count' => $category->posts_count ?? 0,
                'created_at' => $category->created_at?->toIso8601String(),
                'updated_at' => $category->updated_at?->toIso8601String(),
            ];
        })->all();

        return Response::json([
            'data' => $data,
            'pagination' => [
                'current_page' => $categories->currentPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
                'last_page' => $categories->lastPage(),
            ],
        ]);
    }
}
