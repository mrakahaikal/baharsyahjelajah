<?php

namespace App\Mcp\Resources;

use App\Enums\BannerPlacement;
use App\Models\Banner;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class ActiveBannersResource extends Resource
{
    protected string $uri = 'banners://active-campaigns';

    protected string $name = 'Daftar Banner & Kampanye Promo Aktif';

    protected string $description = 'Snapshot banner dan kampanye promosi beranda (hero & promo) yang saat ini sedang aktif dan dalam jadwal tayang.';

    protected string $mimeType = 'application/json';

    public function handle(Request $request): Response
    {
        $now = now();

        $banners = Banner::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Banner $b): array => [
                'id' => $b->id,
                'title' => [
                    'id' => $b->getTranslation('title', 'id', false),
                    'en' => $b->getTranslation('title', 'en', false),
                    'ms' => $b->getTranslation('title', 'ms', false),
                ],
                'subtitle' => [
                    'id' => $b->getTranslation('subtitle', 'id', false),
                    'en' => $b->getTranslation('subtitle', 'en', false),
                    'ms' => $b->getTranslation('subtitle', 'ms', false),
                ],
                'placement' => $b->placement?->value ?? (string) $b->placement,
                'placement_label' => $b->placement instanceof BannerPlacement ? $b->placement->getLabel() : $b->placement,
                'image_path' => $b->image_path,
                'cta_type' => $b->cta_type?->value,
                'cta_value' => $b->cta_value,
                'cta_label' => [
                    'id' => $b->getTranslation('cta_label', 'id', false),
                    'en' => $b->getTranslation('cta_label', 'en', false),
                    'ms' => $b->getTranslation('cta_label', 'ms', false),
                ],
                'starts_at' => $b->starts_at?->toIso8601String(),
                'ends_at' => $b->ends_at?->toIso8601String(),
            ])
            ->all();

        return Response::json([
            'total_active_banners' => count($banners),
            'banners' => $banners,
        ]);
    }
}
