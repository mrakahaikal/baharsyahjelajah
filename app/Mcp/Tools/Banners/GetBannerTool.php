<?php

namespace App\Mcp\Tools\Banners;

use App\Enums\BannerCtaType;
use App\Enums\BannerPlacement;
use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Banner;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('get_banner')]
#[Title('Get Banner Details')]
#[Description('Get complete banner details by ID, including multilingual texts, placement position, CTA settings, and scheduling.')]
class GetBannerTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Banner numeric ID.')->required(),
            'locale' => $schema->string()->description('Preferred output locale ("id", "en", "ms"). Default is "id".'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $locale = (string) $request->get('locale', 'id');

        if (empty($id)) {
            return Response::error('The "id" parameter is required.');
        }

        $banner = Banner::find((int) $id);

        if (! $banner) {
            return Response::error("Banner with ID {$id} not found.");
        }

        return Response::json([
            'banner' => [
                'id' => $banner->id,
                'title' => $banner->getTranslation('title', $locale, false) ?: $banner->getTranslation('title', 'id', false),
                'title_translations' => $banner->getTranslations('title'),
                'subtitle' => $banner->getTranslation('subtitle', $locale, false) ?: $banner->getTranslation('subtitle', 'id', false),
                'subtitle_translations' => $banner->getTranslations('subtitle'),
                'placement' => $banner->placement?->value ?? (string) $banner->placement,
                'placement_label' => $banner->placement instanceof BannerPlacement ? $banner->placement->getLabel() : $banner->placement,
                'image_path' => $banner->image_path,
                'cta_type' => $banner->cta_type?->value ?? (string) $banner->cta_type,
                'cta_type_label' => $banner->cta_type instanceof BannerCtaType ? $banner->cta_type->getLabel() : $banner->cta_type,
                'cta_value' => $banner->cta_value,
                'cta_label' => $banner->getTranslation('cta_label', $locale, false) ?: $banner->getTranslation('cta_label', 'id', false),
                'cta_label_translations' => $banner->getTranslations('cta_label'),
                'sort_order' => $banner->sort_order,
                'is_active' => (bool) $banner->is_active,
                'starts_at' => $banner->starts_at?->toIso8601String(),
                'ends_at' => $banner->ends_at?->toIso8601String(),
                'created_at' => $banner->created_at?->toIso8601String(),
                'updated_at' => $banner->updated_at?->toIso8601String(),
            ],
            'locale' => $locale,
        ]);
    }
}
