<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Models\TourCategory;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('tour_categories_overview')]
#[Title('Ringkasan Kategori Tur Wisata')]
#[Description('Daftar kategori paket wisata beserta jumlah total tur dan tur aktif di setiap kategori.')]
#[Uri('tours://categories')]
#[MimeType('application/json')]
class TourCategoriesResource extends Resource
{
    public function handle(Request $request): Response
    {
        $categories = TourCategory::query()
            ->withCount(['tours', 'activeTours'])
            ->ordered()
            ->orderBy('id')
            ->get()
            ->map(fn (TourCategory $cat): array => [
                'id' => $cat->id,
                'name' => $cat->getTranslations('name'),
                'slug' => $cat->getTranslations('slug'),
                'icon' => $cat->icon,
                'sort_order' => (int) $cat->sort_order,
                'tours_count' => (int) ($cat->tours_count ?? 0),
                'active_tours_count' => (int) ($cat->active_tours_count ?? 0),
            ])
            ->all();

        return Response::json([
            'resource' => 'tours://categories',
            'total_categories' => count($categories),
            'categories' => $categories,
        ]);
    }
}
