<?php

namespace App\Mcp\Tools\Banners;

use App\Enums\BannerPlacement;
use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Banner;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_banners')]
#[Title('List Promotional & Hero Banners')]
#[Description('List website banners and promotional campaigns with filters by placement (home_hero, home_promo), active schedule, search keyword, and pagination.')]
class ListBannersTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'placement' => $schema->string()->description('Filter by placement position: "home_hero" or "home_promo".'),
            'is_active' => $schema->boolean()->description('Filter by active visibility toggle.'),
            'currently_running' => $schema->boolean()->description('Filter only banners whose schedule is currently live (starts_at <= now <= ends_at).'),
            'search' => $schema->string()->description('Search keyword matching title, subtitle, or CTA label.'),
            'locale' => $schema->string()->description('Preferred output locale ("id", "en", "ms"). Default is "id".'),
            'per_page' => $schema->integer()->description('Number of results per page (1-50, default: 15).'),
            'page' => $schema->integer()->description('Page number for pagination (default: 1).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $locale = (string) $request->get('locale', 'id');
        $perPage = min(50, max(1, (int) $request->get('per_page', 15)));
        $page = max(1, (int) $request->get('page', 1));

        $query = Banner::query();

        if ($request->has('is_active')) {
            $query->where('is_active', (bool) $request->get('is_active'));
        }

        if ($request->filled('placement')) {
            $placement = (string) $request->get('placement');
            $enumPlacement = BannerPlacement::tryFrom($placement);
            $query->where('placement', $enumPlacement?->value ?? $placement);
        }

        if ($request->boolean('currently_running')) {
            $now = now();
            $query->where('is_active', true)
                ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
        }

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function (Builder $sub) use ($search): void {
                $sub->where('title->id', 'like', "%{$search}%")
                    ->orWhere('title->en', 'like', "%{$search}%")
                    ->orWhere('title->ms', 'like', "%{$search}%")
                    ->orWhere('subtitle->id', 'like', "%{$search}%")
                    ->orWhere('subtitle->en', 'like', "%{$search}%")
                    ->orWhere('subtitle->ms', 'like', "%{$search}%");
            });
        }

        $query->orderBy('sort_order')->orderBy('id');

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        $banners = collect($paginator->items())->map(function (Banner $banner) use ($locale): array {
            return [
                'id' => $banner->id,
                'title' => $banner->getTranslation('title', $locale, false) ?: $banner->getTranslation('title', 'id', false),
                'subtitle' => $banner->getTranslation('subtitle', $locale, false) ?: $banner->getTranslation('subtitle', 'id', false),
                'placement' => $banner->placement?->value ?? (string) $banner->placement,
                'placement_label' => $banner->placement instanceof BannerPlacement ? $banner->placement->getLabel() : $banner->placement,
                'image_path' => $banner->image_path,
                'cta_type' => $banner->cta_type?->value ?? (string) $banner->cta_type,
                'cta_value' => $banner->cta_value,
                'cta_label' => $banner->getTranslation('cta_label', $locale, false) ?: $banner->getTranslation('cta_label', 'id', false),
                'sort_order' => $banner->sort_order,
                'is_active' => (bool) $banner->is_active,
                'starts_at' => $banner->starts_at?->toIso8601String(),
                'ends_at' => $banner->ends_at?->toIso8601String(),
            ];
        })->all();

        return Response::json([
            'banners' => $banners,
            'pagination' => [
                'total' => $paginator->total(),
                'count' => count($banners),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
            ],
            'locale' => $locale,
        ]);
    }
}
