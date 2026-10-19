<?php

namespace App\Mcp\Tools\Banners;

use App\Enums\BannerCtaType;
use App\Enums\BannerPlacement;
use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Banner;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('update_banner')]
#[Title('Update Banner Entry')]
#[Description('Update banner details, including multilingual headlines, placement position, CTA settings, or active scheduling.')]
class UpdateBannerTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Banner numeric ID to update.')->required(),
            'title' => $schema->mixed()->description('Updated banner title (single string or multilingual JSON map).'),
            'subtitle' => $schema->mixed()->description('Updated banner subtitle (single string or multilingual JSON map).'),
            'placement' => $schema->string()->description('Updated placement position: "home_hero" or "home_promo".'),
            'image_path' => $schema->string()->description('Updated image path or URL.'),
            'cta_type' => $schema->string()->description('Updated CTA type: "route", "url", or "whatsapp".'),
            'cta_value' => $schema->string()->description('Updated CTA destination value.'),
            'cta_label' => $schema->mixed()->description('Updated button label text.'),
            'sort_order' => $schema->integer()->description('Updated sorting weight.'),
            'is_active' => $schema->boolean()->description('Updated active status.'),
            'starts_at' => $schema->string()->description('Updated schedule start (ISO datetime). Pass null to clear.'),
            'ends_at' => $schema->string()->description('Updated schedule end (ISO datetime). Pass null to clear.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        if (empty($id)) {
            return Response::error('The "id" parameter is required.');
        }

        $banner = Banner::find((int) $id);
        if (! $banner) {
            return Response::error("Banner with ID {$id} not found.");
        }

        if ($request->has('title')) {
            $normalizedTitle = $this->normalizeMultilingualInput($request->get('title'));
            if (! empty($normalizedTitle['id'])) {
                $mergedTitle = array_merge($banner->getTranslations('title'), $normalizedTitle);
                $banner->setTranslations('title', $mergedTitle);
            }
        }

        if ($request->has('subtitle')) {
            $normalizedSub = $this->normalizeMultilingualInput($request->get('subtitle'));
            if (! empty($normalizedSub['id'])) {
                $mergedSub = array_merge($banner->getTranslations('subtitle'), $normalizedSub);
                $banner->setTranslations('subtitle', $mergedSub);
            }
        }

        if ($request->filled('placement')) {
            $placementEnum = BannerPlacement::tryFrom((string) $request->get('placement'));
            if ($placementEnum) {
                $banner->placement = $placementEnum;
            }
        }

        if ($request->has('image_path')) {
            $banner->image_path = $request->get('image_path');
        }

        if ($request->has('cta_type')) {
            $rawType = $request->get('cta_type');
            $banner->cta_type = ! empty($rawType) ? BannerCtaType::tryFrom((string) $rawType) : null;
        }

        if ($request->has('cta_value')) {
            $banner->cta_value = $request->get('cta_value');
        }

        if ($request->has('cta_label')) {
            $normalizedLabel = $this->normalizeMultilingualInput($request->get('cta_label'));
            if (! empty($normalizedLabel['id'])) {
                $mergedLabel = array_merge($banner->getTranslations('cta_label'), $normalizedLabel);
                $banner->setTranslations('cta_label', $mergedLabel);
            }
        }

        if ($request->has('sort_order')) {
            $banner->sort_order = max(0, (int) $request->get('sort_order'));
        }

        if ($request->has('is_active')) {
            $banner->is_active = (bool) $request->get('is_active');
        }

        if ($request->has('starts_at')) {
            $banner->starts_at = $request->filled('starts_at') ? Carbon::parse((string) $request->get('starts_at')) : null;
        }

        if ($request->has('ends_at')) {
            $banner->ends_at = $request->filled('ends_at') ? Carbon::parse((string) $request->get('ends_at')) : null;
        }

        $banner->save();

        return Response::json([
            'message' => 'Banner berhasil diperbarui.',
            'banner' => [
                'id' => $banner->id,
                'title' => $banner->getTranslations('title'),
                'subtitle' => $banner->getTranslations('subtitle'),
                'placement' => $banner->placement?->value,
                'placement_label' => $banner->placement?->getLabel(),
                'image_path' => $banner->image_path,
                'cta_type' => $banner->cta_type?->value,
                'cta_value' => $banner->cta_value,
                'cta_label' => $banner->getTranslations('cta_label'),
                'sort_order' => $banner->sort_order,
                'is_active' => (bool) $banner->is_active,
                'starts_at' => $banner->starts_at?->toIso8601String(),
                'ends_at' => $banner->ends_at?->toIso8601String(),
                'updated_at' => $banner->updated_at?->toIso8601String(),
            ],
        ]);
    }
}
