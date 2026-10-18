<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Models\Post;
use App\Models\PostCategory;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('article_catalog_overview')]
#[Title('Ringkasan Katalog & Statistik Artikel')]
#[Description('Ringkasan statistik agregat katalog artikel blog: status publikasi, distribusi kategori, keterkaitan destinasi, dan aktivitas terkini.')]
#[Uri('articles://catalog-overview')]
#[MimeType('application/json')]
class ArticleCatalogOverviewResource extends Resource
{
    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $totalArticles = Post::count();
        $publishedCount = Post::published()->count();
        $draftCount = Post::where('status', 'draft')->count();
        $archivedCount = Post::where('status', 'archived')->count();

        $categoryDistribution = PostCategory::query()
            ->withCount('posts')
            ->orderByDesc('posts_count')
            ->get()
            ->map(fn (PostCategory $cat) => [
                'category_id' => $cat->id,
                'name' => $cat->getTranslation('name', 'id', false),
                'slug' => $cat->localizedSlug('id'),
                'articles_count' => $cat->posts_count,
            ])
            ->all();

        $linkedToDestinationsCount = Post::has('destinations')->count();
        $latestPost = Post::published()->latest('published_at')->first();

        return Response::json([
            'resource' => 'articles://catalog-overview',
            'overview' => [
                'total_articles' => $totalArticles,
                'status_breakdown' => [
                    'published' => $publishedCount,
                    'draft' => $draftCount,
                    'archived' => $archivedCount,
                ],
                'articles_linked_to_destinations' => $linkedToDestinationsCount,
                'articles_without_destinations' => max(0, $totalArticles - $linkedToDestinationsCount),
                'latest_published_at' => $latestPost?->published_at?->toIso8601String(),
                'categories_distribution' => $categoryDistribution,
            ],
        ]);
    }
}
