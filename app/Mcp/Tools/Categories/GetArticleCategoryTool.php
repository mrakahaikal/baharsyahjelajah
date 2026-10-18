<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Categories;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('get_article_category')]
#[Title('Get Article Category')]
#[Description('Retrieve detailed information for a single article category by its ID or slug, including recent articles.')]
class GetArticleCategoryTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The unique identifier of the category.'),
            'slug' => $schema->string()->description('The unique slug of the category in any supported language.'),
            'locale' => $schema->string()->description('The locale for localized string fields ("id", "en", "ms"). Default: "id".'),
        ];
    }

    public function handle(Request $request): Response
    {
        $request->validate([
            'id' => ['nullable', 'integer'],
            'slug' => ['nullable', 'string', 'max:255'],
            'locale' => ['nullable', 'string', 'in:id,en,ms'],
        ]);

        $id = $request->get('id');
        $slug = $request->get('slug');
        $locale = (string) ($request->get('locale') ?? 'id');

        if (empty($id) && empty($slug)) {
            return Response::error('You must provide either an "id" or a "slug" to retrieve a category.');
        }

        $query = PostCategory::query()->withCount('posts');

        if (! empty($id)) {
            $query->where('id', (int) $id);
        } else {
            $query->whereLocalizedSlug((string) $slug);
        }

        /** @var PostCategory|null $category */
        $category = $query->first();

        if (! $category) {
            return Response::error('Category not found with the provided identifier.');
        }

        $recentArticles = $category->posts()
            ->select(['id', 'title', 'slug', 'status', 'published_at'])
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (Post $post): array => [
                'id' => $post->id,
                'title' => $post->getTranslation('title', $locale, false) ?: ($post->getTranslation('title', 'id', false) ?: ''),
                'slug' => $post->localizedSlug($locale),
                'status' => $post->status,
                'published_at' => $post->published_at?->toIso8601String(),
            ]);

        return Response::json([
            'category' => [
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
                'recent_articles' => $recentArticles,
            ],
        ]);
    }
}
