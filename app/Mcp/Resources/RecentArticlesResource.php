<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Destination;
use App\Models\Post;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('recent_articles')]
#[Title('Daftar Artikel Terbaru')]
#[Description('Snapshot artikel blog travel & tour terbaru yang telah dipublikasikan beserta metadata, kategori, dan destinasi terkait.')]
#[Uri('articles://recent')]
#[MimeType('application/json')]
class RecentArticlesResource extends Resource
{
    use NormalizesMultilingualInput;

    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $posts = Post::query()
            ->published()
            ->with(['category', 'destinations', 'author'])
            ->latest('published_at')
            ->limit(15)
            ->get()
            ->map(function (Post $post): array {
                $contentId = (string) $post->getTranslation('content', 'id', false);

                return [
                    'id' => $post->id,
                    'title' => [
                        'id' => $post->getTranslation('title', 'id', false),
                        'en' => $post->getTranslation('title', 'en', false),
                        'ms' => $post->getTranslation('title', 'ms', false),
                    ],
                    'slug' => [
                        'id' => $post->getTranslation('slug', 'id', false),
                        'en' => $post->getTranslation('slug', 'en', false),
                        'ms' => $post->getTranslation('slug', 'ms', false),
                    ],
                    'excerpt' => [
                        'id' => $post->getTranslation('excerpt', 'id', false),
                        'en' => $post->getTranslation('excerpt', 'en', false),
                        'ms' => $post->getTranslation('excerpt', 'ms', false),
                    ],
                    'status' => $post->status,
                    'published_at' => $post->published_at?->toIso8601String(),
                    'reading_time_minutes' => $this->calculateReadingTime($contentId),
                    'cover_image_url' => $post->cover_image_url,
                    'category' => $post->category ? [
                        'id' => $post->category->id,
                        'name' => $post->category->getTranslation('name', 'id', false),
                        'slug' => $post->category->localizedSlug('id'),
                    ] : null,
                    'author' => $post->author ? [
                        'id' => $post->author->id,
                        'name' => $post->author->name,
                    ] : null,
                    'destinations' => $post->destinations->map(fn (Destination $d) => [
                        'id' => $d->id,
                        'name' => $d->getTranslation('name', 'id', false),
                        'slug' => $d->slug,
                    ])->values()->all(),
                ];
            })
            ->all();

        return Response::json([
            'resource' => 'articles://recent',
            'count' => count($posts),
            'articles' => $posts,
        ]);
    }
}
