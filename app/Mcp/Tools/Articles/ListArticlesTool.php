<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Articles;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Destination;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_articles')]
#[Title('List Articles')]
#[Description('List articles with optional search, filters (category, destination, status), locale selection, and pagination.')]
class ListArticlesTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Optional search keyword matching article title, excerpt, or content in any language.'),
            'category_id' => $schema->integer()->description('Optional filter by post category ID.'),
            'category_slug' => $schema->string()->description('Optional filter by post category slug (in id, en, or ms).'),
            'destination_id' => $schema->integer()->description('Optional filter by attached destination ID.'),
            'destination_slug' => $schema->string()->description('Optional filter by attached destination slug.'),
            'status' => $schema->string()->description('Filter by post status: "all", "published", "draft", or "archived". Default is "all".')->enum(['all', 'published', 'draft', 'archived']),
            'locale' => $schema->string()->description('Preferred language locale for returned titles, slugs, and excerpts ("id", "en", or "ms"). Default is "id".'),
            'per_page' => $schema->integer()->description('Number of articles per page (default: 15, max: 100).'),
            'page' => $schema->integer()->description('Page number for pagination (default: 1).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $locale = (string) $request->get('locale', 'id');
        if (! in_array($locale, ['id', 'en', 'ms'], true)) {
            $locale = 'id';
        }

        $perPage = (int) $request->get('per_page', 15);
        $perPage = max(1, min(100, $perPage));
        $page = max(1, (int) $request->get('page', 1));

        $query = Post::query()
            ->with(['category', 'author', 'destinations'])
            ->latest('id');

        // Filter status
        $status = (string) $request->get('status', 'all');
        if ($status === 'published') {
            $query->published();
        } elseif (in_array($status, ['draft', 'archived'], true)) {
            $query->where('status', $status);
        }

        // Filter category by ID or slug
        $categoryId = $request->get('category_id');
        $categorySlug = $request->get('category_slug');

        if (! empty($categoryId)) {
            $query->where('post_category_id', (int) $categoryId);
        } elseif (! empty($categorySlug)) {
            $category = PostCategory::query()->whereLocalizedSlug((string) $categorySlug)->first();
            if ($category) {
                $query->where('post_category_id', $category->id);
            } else {
                return Response::json([
                    'articles' => [],
                    'pagination' => [
                        'total' => 0,
                        'per_page' => $perPage,
                        'current_page' => $page,
                        'last_page' => 1,
                        'has_more_pages' => false,
                    ],
                ]);
            }
        }

        // Filter destination by ID or slug
        $destinationId = $request->get('destination_id');
        $destinationSlug = $request->get('destination_slug');

        if (! empty($destinationId)) {
            $query->whereHas('destinations', fn (Builder $q) => $q->where('destinations.id', (int) $destinationId));
        } elseif (! empty($destinationSlug)) {
            $destination = Destination::query()->where('slug', (string) $destinationSlug)->first();
            if ($destination) {
                $query->whereHas('destinations', fn (Builder $q) => $q->where('destinations.id', $destination->id));
            } else {
                return Response::json([
                    'articles' => [],
                    'pagination' => [
                        'total' => 0,
                        'per_page' => $perPage,
                        'current_page' => $page,
                        'last_page' => 1,
                        'has_more_pages' => false,
                    ],
                ]);
            }
        }

        // Filter search keyword
        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('title->id', 'like', "%{$search}%")
                    ->orWhere('title->en', 'like', "%{$search}%")
                    ->orWhere('title->ms', 'like', "%{$search}%")
                    ->orWhere('excerpt->id', 'like', "%{$search}%")
                    ->orWhere('excerpt->en', 'like', "%{$search}%")
                    ->orWhere('excerpt->ms', 'like', "%{$search}%")
                    ->orWhere('content->id', 'like', "%{$search}%")
                    ->orWhere('content->en', 'like', "%{$search}%")
                    ->orWhere('content->ms', 'like', "%{$search}%");
            });
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $articles = collect($paginator->items())->map(function (Post $post) use ($locale): array {
            $contentStr = (string) ($post->getTranslation('content', $locale, false) ?: $post->getTranslation('content', 'id', false));

            return [
                'id' => $post->id,
                'title' => $post->getTranslation('title', $locale, false) ?: $post->getTranslation('title', 'id', false),
                'slug' => $post->localizedSlug($locale),
                'excerpt' => $post->getTranslation('excerpt', $locale, false) ?: $post->getTranslation('excerpt', 'id', false),
                'status' => $post->status,
                'published_at' => $post->published_at?->toIso8601String(),
                'reading_time_minutes' => $this->calculateReadingTime($contentStr),
                'cover_image_url' => $post->cover_image_url,
                'category' => $post->category ? [
                    'id' => $post->category->id,
                    'name' => $post->category->getTranslation('name', $locale, false) ?: $post->category->getTranslation('name', 'id', false),
                    'slug' => $post->category->localizedSlug($locale),
                ] : null,
                'author' => $post->author ? [
                    'id' => $post->author->id,
                    'name' => $post->author->name,
                ] : null,
                'destinations' => $post->destinations->map(fn (Destination $dest) => [
                    'id' => $dest->id,
                    'name' => $dest->getTranslation('name', $locale, false) ?: $dest->getTranslation('name', 'id', false),
                    'slug' => $dest->slug,
                ])->values()->all(),
                'created_at' => $post->created_at?->toIso8601String(),
                'updated_at' => $post->updated_at?->toIso8601String(),
            ];
        })->all();

        return Response::json([
            'articles' => $articles,
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
        ]);
    }
}
