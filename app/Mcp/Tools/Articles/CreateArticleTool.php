<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Articles;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Destination;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('create_article')]
#[Title('Create Article')]
#[Description('Create a new travel article with multilingual content, category association, and destination linking.')]
class CreateArticleTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->description('Article title. Can be a string (e.g. "Panduan Liburan ke Lombok") or a JSON string / object with keys "id", "en", "ms".')->required(),
            'content' => $schema->string()->description('Full article body in RichText HTML format (e.g. <p>, <h2>, <ul>, <li>, <blockquote>, <strong>). If plain text or markdown is passed, it is automatically converted into clean semantic RichText HTML. Can be a string or a JSON string / object with keys "id", "en", "ms".')->required(),
            'category_id' => $schema->integer()->description('The numeric ID of the post category.'),
            'category_slug' => $schema->string()->description('The slug of the post category (used if category_id is not provided).'),
            'slug' => $schema->string()->description('Optional custom URL slug. Can be a simple string or JSON string / object with keys "id", "en", "ms". Auto-generated from title if omitted.'),
            'excerpt' => $schema->string()->description('Optional article excerpt or summary. Auto-generated from content if omitted.'),
            'status' => $schema->string()->description('Publication status: "draft", "published", or "archived". Default is "draft".')->enum(['draft', 'published', 'archived']),
            'published_at' => $schema->string()->description('Optional publication ISO datetime (e.g. "2026-10-18T12:00:00Z"). Set automatically to current time if status is "published" and omitted.'),
            'destination_ids' => $schema->array()->description('Array of destination numeric IDs to attach to this article.')->items($schema->integer()),
            'destination_slugs' => $schema->array()->description('Array of destination slugs to attach to this article.')->items($schema->string()),
            'cover_image' => $schema->string()->description('Optional path or URL to the cover image.'),
            'user_id' => $schema->integer()->description('Optional author user ID. Defaults to the authenticated user or first administrator.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $rawTitle = $request->get('title');
        $rawContent = $request->get('content');

        if (empty($rawTitle)) {
            return Response::error('The "title" parameter is required.');
        }

        if (empty($rawContent)) {
            return Response::error('The "content" parameter is required.');
        }

        // 1. Resolve Category
        $categoryId = $request->get('category_id');
        $categorySlug = $request->get('category_slug');

        $category = null;
        if (! empty($categoryId)) {
            $category = PostCategory::find((int) $categoryId);
        } elseif (! empty($categorySlug)) {
            $category = PostCategory::query()->whereLocalizedSlug((string) $categorySlug)->first();
        }

        if (! $category) {
            return Response::error('Valid "category_id" or "category_slug" must be provided to attach the article to a category.');
        }

        // 2. Resolve Author / User
        $userId = $request->get('user_id');
        if (empty($userId)) {
            $userId = auth()->id() ?? User::query()->first()?->id;
        }

        if (empty($userId) || ! User::whereKey($userId)->exists()) {
            return Response::error('A valid author user could not be resolved. Please provide a valid "user_id".');
        }

        // 3. Normalize Multilingual Fields
        $title = $this->normalizeMultilingualInput($rawTitle);
        if (empty($title['id'])) {
            return Response::error('Article title for Indonesian (id) cannot be empty.');
        }

        $content = $this->normalizeRichTextMultilingual($rawContent);
        if (empty($content['id'])) {
            return Response::error('Article content for Indonesian (id) cannot be empty.');
        }

        $slug = $this->resolveSlugTranslations($title, $request->get('slug'));

        // Check primary slug uniqueness
        $slugConflict = Post::query()->whereJsonContains('slug->id', $slug['id'])->exists();
        if ($slugConflict) {
            $slug['id'] .= '-'.Str::random(4);
        }

        // Excerpt
        $rawExcerpt = $request->get('excerpt');
        if (! empty($rawExcerpt)) {
            $excerpt = $this->normalizeMultilingualInput($rawExcerpt);
        } else {
            $excerpt = [
                'id' => Str::limit(strip_tags($content['id']), 160),
                'en' => Str::limit(strip_tags($content['en']), 160),
                'ms' => Str::limit(strip_tags($content['ms']), 160),
            ];
        }

        // Status & Published At
        $status = (string) $request->get('status', 'draft');
        if (! in_array($status, ['draft', 'published', 'archived'], true)) {
            $status = 'draft';
        }

        $rawPublishedAt = $request->get('published_at');
        $publishedAt = null;
        if (! empty($rawPublishedAt)) {
            try {
                $publishedAt = Carbon::parse($rawPublishedAt);
            } catch (\Throwable) {
                return Response::error("Invalid 'published_at' datetime format: {$rawPublishedAt}");
            }
        } elseif ($status === 'published') {
            $publishedAt = Carbon::now();
        }

        // 4. Resolve Destinations to attach
        $destinationIds = $this->resolveDestinationIds(
            $request->get('destination_ids'),
            $request->get('destination_slugs')
        );

        // 5. Create Article in DB Transaction
        $coverImage = $request->get('cover_image');

        /** @var Post $post */
        $post = DB::transaction(function () use (
            $category,
            $userId,
            $title,
            $slug,
            $excerpt,
            $content,
            $coverImage,
            $status,
            $publishedAt,
            $destinationIds
        ): Post {
            $post = Post::create([
                'post_category_id' => $category->id,
                'user_id' => $userId,
                'title' => $title,
                'slug' => $slug,
                'excerpt' => $excerpt,
                'content' => $content,
                'cover_image' => $coverImage,
                'status' => $status,
                'published_at' => $publishedAt,
            ]);

            if (! empty($destinationIds)) {
                $post->destinations()->sync($destinationIds);
            }

            return $post;
        });

        $post->load(['category', 'author', 'destinations']);

        return Response::json([
            'message' => 'Article created successfully.',
            'article' => [
                'id' => $post->id,
                'title' => $post->getTranslation('title', 'id', false),
                'slug' => $post->localizedSlug('id'),
                'status' => $post->status,
                'published_at' => $post->published_at?->toIso8601String(),
                'category' => [
                    'id' => $post->category->id,
                    'name' => $post->category->getTranslation('name', 'id', false),
                    'slug' => $post->category->localizedSlug('id'),
                ],
                'destinations' => $post->destinations->map(fn (Destination $d) => [
                    'id' => $d->id,
                    'name' => $d->getTranslation('name', 'id', false),
                    'slug' => $d->slug,
                ])->values()->all(),
                'translations' => [
                    'title' => $post->getTranslations('title'),
                    'slug' => $post->getTranslations('slug'),
                ],
                'created_at' => $post->created_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Resolve destination IDs from IDs and slugs.
     *
     * @return array<int>
     */
    protected function resolveDestinationIds(mixed $rawIds, mixed $rawSlugs): array
    {
        $ids = [];

        if (is_string($rawIds)) {
            $decoded = json_decode($rawIds, true);
            if (is_array($decoded)) {
                $rawIds = $decoded;
            }
        }
        if (is_array($rawIds)) {
            foreach ($rawIds as $id) {
                if (is_numeric($id)) {
                    $ids[] = (int) $id;
                }
            }
        }

        if (is_string($rawSlugs)) {
            $decoded = json_decode($rawSlugs, true);
            if (is_array($decoded)) {
                $rawSlugs = $decoded;
            }
        }
        if (is_array($rawSlugs) && ! empty($rawSlugs)) {
            $matchedIds = Destination::query()
                ->whereIn('slug', $rawSlugs)
                ->pluck('id')
                ->all();

            $ids = array_merge($ids, $matchedIds);
        }

        return array_values(array_unique($ids));
    }
}
