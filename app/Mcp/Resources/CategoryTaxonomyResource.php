<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Models\PostCategory;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('category_taxonomy')]
#[Title('Taksonomi Kategori Artikel')]
#[Description('Snapshot struktur lengkap taksonomi kategori artikel blog beserta terjemahan bahasa (id, en, ms) dan jumlah artikel terkait.')]
#[Uri('categories://taxonomy')]
#[MimeType('application/json')]
class CategoryTaxonomyResource extends Resource
{
    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $categories = PostCategory::query()
            ->withCount('posts')
            ->orderBy('id')
            ->get()
            ->map(fn (PostCategory $cat): array => [
                'id' => $cat->id,
                'name' => [
                    'id' => $cat->getTranslation('name', 'id', false),
                    'en' => $cat->getTranslation('name', 'en', false),
                    'ms' => $cat->getTranslation('name', 'ms', false),
                ],
                'slug' => [
                    'id' => $cat->getTranslation('slug', 'id', false),
                    'en' => $cat->getTranslation('slug', 'en', false),
                    'ms' => $cat->getTranslation('slug', 'ms', false),
                ],
                'description' => [
                    'id' => $cat->getTranslation('description', 'id', false),
                    'en' => $cat->getTranslation('description', 'en', false),
                    'ms' => $cat->getTranslation('description', 'ms', false),
                ],
                'articles_count' => $cat->posts_count,
            ])
            ->all();

        return Response::json([
            'resource' => 'categories://taxonomy',
            'total_categories' => count($categories),
            'categories' => $categories,
        ]);
    }
}
