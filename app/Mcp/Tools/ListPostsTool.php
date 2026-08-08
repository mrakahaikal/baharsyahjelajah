<?php

namespace App\Mcp\Tools;

use App\Models\Post;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List and search blog posts in the database with optional keyword filtering, category filter, status filter, and pagination.')]
class ListPostsTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'post_category_id' => 'nullable|integer|exists:post_categories,id',
            'status' => 'nullable|string|in:draft,published',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $perPage = $validated['per_page'] ?? 15;
        $page = $validated['page'] ?? 1;

        $query = Post::query()->with(['category', 'author']);

        if (! empty($validated['post_category_id'])) {
            $query->where('post_category_id', $validated['post_category_id']);
        }

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (! empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search): void {
                $q->where('title->id', 'like', "%{$search}%")
                    ->orWhere('title->en', 'like', "%{$search}%")
                    ->orWhere('title->ms', 'like', "%{$search}%")
                    ->orWhere('slug->id', 'like', "%{$search}%")
                    ->orWhere('slug->en', 'like', "%{$search}%")
                    ->orWhere('slug->ms', 'like', "%{$search}%")
                    ->orWhere('excerpt->id', 'like', "%{$search}%")
                    ->orWhere('excerpt->en', 'like', "%{$search}%")
                    ->orWhere('excerpt->ms', 'like', "%{$search}%")
                    ->orWhere('content->id', 'like', "%{$search}%")
                    ->orWhere('content->en', 'like', "%{$search}%")
                    ->orWhere('content->ms', 'like', "%{$search}%");
            });
        }

        $paginator = $query->latest('id')->paginate(perPage: $perPage, page: $page);

        $posts = collect($paginator->items())->map(fn (Post $post): array => [
            'id' => $post->id,
            'post_category_id' => $post->post_category_id,
            'category_name' => $post->category?->getTranslation('name', 'id'),
            'user_id' => $post->user_id,
            'author_name' => $post->author?->name,
            'title' => $post->getTranslations('title'),
            'slug' => $post->getTranslations('slug'),
            'excerpt' => $post->getTranslations('excerpt'),
            'status' => $post->status,
            'cover_image_url' => $post->cover_image_url,
            'published_at' => $post->published_at?->toIso8601String(),
            'created_at' => $post->created_at?->toIso8601String(),
            'updated_at' => $post->updated_at?->toIso8601String(),
        ]);

        return Response::text((string) json_encode([
            'success' => true,
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'filters' => [
                'search' => $validated['search'] ?? null,
                'post_category_id' => $validated['post_category_id'] ?? null,
                'status' => $validated['status'] ?? null,
            ],
            'posts' => $posts,
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
            'search' => $schema->string()->description('Optional keyword to filter posts by title, slug, excerpt, or content across locales.'),
            'post_category_id' => $schema->integer()->description('Optional category ID filter.'),
            'status' => $schema->string()->description('Optional status filter ("draft" or "published").'),
            'per_page' => $schema->integer()->description('Number of items per page (1 to 100, default: 15).'),
            'page' => $schema->integer()->description('Page number for pagination (default: 1).'),
        ];
    }
}
