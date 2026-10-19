<?php

namespace App\Mcp\Resources;

use App\Models\Testimonial;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class CuratedTestimonialsResource extends Resource
{
    protected string $uri = 'testimonials://curated';

    protected string $name = 'Ulasan Pelanggan Terkurasi & Rating Tertinggi';

    protected string $description = 'Snapshot ulasan pelanggan bintang 4 dan 5 yang terkurasi serta berstatus unggulan (featured) untuk materi promosi & social proof.';

    protected string $mimeType = 'application/json';

    public function handle(Request $request): Response
    {
        $testimonials = Testimonial::query()
            ->active()
            ->where(function ($query): void {
                $query->where('is_featured', true)
                    ->orWhere('rating', '>=', 4);
            })
            ->with('product')
            ->orderByDesc('is_featured')
            ->orderByDesc('rating')
            ->take(30)
            ->get()
            ->map(function (Testimonial $item): array {
                $productName = null;
                if ($item->product) {
                    $productName = method_exists($item->product, 'getTranslation')
                        ? ($item->product->getTranslation('name', 'id', false) ?: $item->product->getTranslation('title', 'id', false))
                        : ($item->product->name ?? $item->product->title ?? null);
                }

                return [
                    'id' => $item->id,
                    'reviewer_name' => [
                        'id' => $item->getTranslation('reviewer_name', 'id', false),
                        'en' => $item->getTranslation('reviewer_name', 'en', false),
                        'ms' => $item->getTranslation('reviewer_name', 'ms', false),
                    ],
                    'reviewer_country' => $item->reviewer_country,
                    'reviewer_flag' => $item->reviewer_flag,
                    'product_type' => class_basename($item->product_type),
                    'product_id' => $item->product_id,
                    'product_name' => $productName,
                    'rating' => $item->rating,
                    'stars' => $item->stars,
                    'content' => [
                        'id' => $item->getTranslation('content', 'id', false),
                        'en' => $item->getTranslation('content', 'en', false),
                        'ms' => $item->getTranslation('content', 'ms', false),
                    ],
                    'photo' => $item->photo,
                    'is_featured' => (bool) $item->is_featured,
                ];
            })
            ->all();

        return Response::json([
            'total_curated' => count($testimonials),
            'testimonials' => $testimonials,
        ]);
    }
}
