<?php

namespace App\Mcp\Tools;

use App\Models\PostCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List and search post categories in the database with optional keyword filtering and pagination.')]
class ListPostCategoriesTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $perPage = $validated['per_page'] ?? 15;
        $page = $validated['page'] ?? 1;

        $query = PostCategory::query()->withCount('posts');

        if (! empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search): void {
                $q->where('name->id', 'like', "%{$search}%")
                    ->orWhere('name->en', 'like', "%{$search}%")
                    ->orWhere('name->ms', 'like', "%{$search}%")
                    ->orWhere('slug->id', 'like', "%{$search}%")
                    ->orWhere('slug->en', 'like', "%{$search}%")
                    ->orWhere('slug->ms', 'like', "%{$search}%")
                    ->orWhere('description->id', 'like', "%{$search}%")
                    ->orWhere('description->en', 'like', "%{$search}%")
                    ->orWhere('description->ms', 'like', "%{$search}%");
            });
        }

        $paginator = $query->latest('id')->paginate(perPage: $perPage, page: $page);

        $categories = collect($paginator->items())->map(fn (PostCategory $category): array => [
            'id' => $category->id,
            'name' => $category->getTranslations('name'),
            'slug' => $category->getTranslations('slug'),
            'description' => $category->getTranslations('description'),
            'posts_count' => $category->posts_count,
            'created_at' => $category->created_at?->toIso8601String(),
            'updated_at' => $category->updated_at?->toIso8601String(),
        ]);

        return Response::text((string) json_encode([
            'success' => true,
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'search' => $validated['search'] ?? null,
            'categories' => $categories,
        ], JSON_PRETTY_PRINT));
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Optional search keyword to filter categories by name, slug, or description across locales.'),
            'per_page' => $schema->integer()->description('Number of items per page (1 to 100, default: 15).'),
            'page' => $schema->integer()->description('Page number for pagination (default: 1).'),
        ];
    }
}
