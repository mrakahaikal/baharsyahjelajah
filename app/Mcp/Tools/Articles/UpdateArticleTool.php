<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Articles;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Destination;
use App\Models\Post;
use App\Models\PostCategory;
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

#[Name('update_article')]
#[Title('Update Article')]
#[Description('Update an existing article: title, content, excerpt, category, destinations, publication status, or cover image.')]
class UpdateArticleTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The numeric ID of the article to update.'),
            'slug' => $schema->string()->description('The existing slug of the article to update (used if "id" is omitted).'),
            'title' => $schema->string()->description('Updated title. Can be a string or JSON object with keys "id", "en", "ms".'),
            'content' => $schema->string()->description('Updated content body in RichText HTML format (e.g. <p>, <h2>, <ul>, <li>, <blockquote>, <strong>). If plain text or markdown is passed, it is automatically converted into clean semantic RichText HTML. Can be a string or JSON object with keys "id", "en", "ms".'),
            'excerpt' => $schema->string()->description('Updated summary excerpt. Can be a string or JSON object with keys "id", "en", "ms".'),
            'custom_slug' => $schema->string()->description('Updated custom slug. Can be a string or JSON object with keys "id", "en", "ms".'),
            'category_id' => $schema->integer()->description('New post category ID.'),
            'category_slug' => $schema->string()->description('New post category slug (used if "category_id" is omitted).'),
            'destination_ids' => $schema->array()->description('Replacement array of destination IDs to link to this article.')->items($schema->integer()),
            'destination_slugs' => $schema->array()->description('Replacement array of destination slugs to link to this article.')->items($schema->string()),
            'status' => $schema->string()->description('Updated status: "draft", "published", or "archived".')->enum(['draft', 'published', 'archived']),
            'published_at' => $schema->string()->description('Updated publication ISO datetime.'),
            'cover_image' => $schema->string()->description('Updated cover image URL or storage path.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $slug = $request->get('slug');

        if (empty($id) && empty($slug)) {
            return Response::error('Either "id" or "slug" must be provided to locate the article to update.');
        }

        $query = Post::query()->with(['category', 'author', 'destinations']);

        /** @var Post|null $post */
        $post = ! empty($id)
            ? $query->find((int) $id)
            : $query->whereLocalizedSlug((string) $slug)->first();

        if (! $post) {
            $identifier = ! empty($id) ? "ID {$id}" : "slug '{$slug}'";

            return Response::error("Article with {$identifier} not found.");
        }

        $attributesToUpdate = [];

        // Title
        if ($request->has('title')) {
            $currentTitle = $post->getTranslations('title');
            $newTitle = $this->mergeTranslations($currentTitle, $request->get('title'));
            $attributesToUpdate['title'] = $newTitle;
        }

        // Slug
        if ($request->has('custom_slug')) {
            $currentSlug = $post->getTranslations('slug');
            $rawCustomSlug = $request->get('custom_slug');
            $normalizedSlug = $this->normalizeMultilingualInput($rawCustomSlug);
            $newSlug = [
                'id' => Str::slug($normalizedSlug['id'] ?: ($currentSlug['id'] ?? '')),
                'en' => Str::slug($normalizedSlug['en'] ?: ($currentSlug['en'] ?? '')),
                'ms' => Str::slug($normalizedSlug['ms'] ?: ($currentSlug['ms'] ?? '')),
            ];
            $attributesToUpdate['slug'] = array_merge($currentSlug, array_filter($newSlug));
        }

        // Excerpt
        if ($request->has('excerpt')) {
            $currentExcerpt = $post->getTranslations('excerpt');
            $attributesToUpdate['excerpt'] = $this->mergeTranslations($currentExcerpt, $request->get('excerpt'));
        }

        // Content
        if ($request->has('content')) {
            $currentContent = $post->getTranslations('content');
            $newContent = $this->normalizeRichTextMultilingual($request->get('content'));
            $attributesToUpdate['content'] = array_merge($currentContent, array_filter($newContent, fn ($val): bool => $val !== ''));
        }

        // Category
        if ($request->has('category_id') || $request->has('category_slug')) {
            $newCatId = $request->get('category_id');
            $newCatSlug = $request->get('category_slug');

            $category = null;
            if (! empty($newCatId)) {
                $category = PostCategory::find((int) $newCatId);
            } elseif (! empty($newCatSlug)) {
                $category = PostCategory::query()->whereLocalizedSlug((string) $newCatSlug)->first();
            }

            if (! $category) {
                return Response::error('Specified category could not be found.');
            }

            $attributesToUpdate['post_category_id'] = $category->id;
        }

        // Status
        if ($request->has('status')) {
            $status = (string) $request->get('status');
            if (! in_array($status, ['draft', 'published', 'archived'], true)) {
                return Response::error('Invalid status value. Allowed: draft, published, archived.');
            }
            $attributesToUpdate['status'] = $status;
            if ($status === 'published' && ! $post->published_at && ! $request->has('published_at')) {
                $attributesToUpdate['published_at'] = Carbon::now();
            }
        }

        // Published at
        if ($request->has('published_at')) {
            $rawPublishedAt = $request->get('published_at');
            if (empty($rawPublishedAt)) {
                $attributesToUpdate['published_at'] = null;
            } else {
                try {
                    $attributesToUpdate['published_at'] = Carbon::parse($rawPublishedAt);
                } catch (\Throwable) {
                    return Response::error("Invalid 'published_at' format: {$rawPublishedAt}");
                }
            }
        }

        // Cover image
        if ($request->has('cover_image')) {
            $attributesToUpdate['cover_image'] = $request->get('cover_image');
        }

        // Destinations sync
        $hasDestinationsUpdate = $request->has('destination_ids') || $request->has('destination_slugs');
        $resolvedDestinationIds = $hasDestinationsUpdate
            ? $this->resolveDestinationIds($request->get('destination_ids'), $request->get('destination_slugs'))
            : null;

        DB::transaction(function () use ($post, $attributesToUpdate, $hasDestinationsUpdate, $resolvedDestinationIds): void {
            if (! empty($attributesToUpdate)) {
                $post->update($attributesToUpdate);
            }

            if ($hasDestinationsUpdate && is_array($resolvedDestinationIds)) {
                $post->destinations()->sync($resolvedDestinationIds);
            }
        });

        $post->refresh()->load(['category', 'destinations', 'author']);

        return Response::json([
            'message' => 'Article updated successfully.',
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
                'updated_at' => $post->updated_at?->toIso8601String(),
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
