<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Articles;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Destination;
use App\Models\Post;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('get_article')]
#[Title('Get Article')]
#[Description('Retrieve full article details, content, translations, author, category, and associated destinations by ID or slug.')]
class GetArticleTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The numeric ID of the article.'),
            'slug' => $schema->string()->description('The URL slug of the article in any supported language (id, en, ms). Used if "id" is omitted.'),
            'locale' => $schema->string()->description('Preferred display locale ("id", "en", or "ms"). Default is "id".'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $slug = $request->get('slug');

        if (empty($id) && empty($slug)) {
            return Response::error('Either "id" or "slug" parameter must be provided.');
        }

        $query = Post::query()->with(['category', 'author', 'destinations', 'countries']);

        if (! empty($id)) {
            $post = $query->find((int) $id);
        } else {
            $post = $query->whereLocalizedSlug((string) $slug)->first();
        }

        if (! $post) {
            $identifier = ! empty($id) ? "ID {$id}" : "slug '{$slug}'";

            return Response::error("Article with {$identifier} not found.");
        }

        $locale = (string) $request->get('locale', 'id');
        if (! in_array($locale, ['id', 'en', 'ms'], true)) {
            $locale = 'id';
        }

        $contentForLocale = (string) ($post->getTranslation('content', $locale, false) ?: $post->getTranslation('content', 'id', false));

        return Response::json([
            'article' => [
                'id' => $post->id,
                'title' => $post->getTranslation('title', $locale, false) ?: $post->getTranslation('title', 'id', false),
                'slug' => $post->localizedSlug($locale),
                'excerpt' => $post->getTranslation('excerpt', $locale, false) ?: $post->getTranslation('excerpt', 'id', false),
                'content' => $post->getTranslation('content', $locale, false) ?: $post->getTranslation('content', 'id', false),
                'status' => $post->status,
                'published_at' => $post->published_at?->toIso8601String(),
                'reading_time_minutes' => $this->calculateReadingTime($contentForLocale),
                'cover_image' => $post->cover_image,
                'cover_image_url' => $post->cover_image_url,
                'category' => $post->category ? [
                    'id' => $post->category->id,
                    'name' => $post->category->getTranslation('name', $locale, false) ?: $post->category->getTranslation('name', 'id', false),
                    'slug' => $post->category->localizedSlug($locale),
                ] : null,
                'author' => $post->author ? [
                    'id' => $post->author->id,
                    'name' => $post->author->name,
                    'email' => $post->author->email,
                ] : null,
                'destinations' => $post->destinations->map(fn (Destination $dest) => [
                    'id' => $dest->id,
                    'name' => $dest->getTranslation('name', $locale, false) ?: $dest->getTranslation('name', 'id', false),
                    'slug' => $dest->slug,
                ])->values()->all(),
                'translations' => [
                    'title' => $post->getTranslations('title'),
                    'slug' => $post->getTranslations('slug'),
                    'excerpt' => $post->getTranslations('excerpt'),
                    'content' => $post->getTranslations('content'),
                ],
                'created_at' => $post->created_at?->toIso8601String(),
                'updated_at' => $post->updated_at?->toIso8601String(),
            ],
        ]);
    }
}
