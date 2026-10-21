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

#[Name('create_banner')]
#[Title('Create Banner Entry')]
#[Description('Create a new promotional or hero banner with multilingual texts, placement position, CTA settings, and scheduling.')]
class CreateBannerTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->union(['string', 'object'])->description('Banner headline title. Single string or multilingual JSON map {"id": "...", "en": "...", "ms": "..."}. Required.'),
            'subtitle' => $schema->union(['string', 'object'])->description('Banner subheadline/description. Single string or multilingual map.'),
            'placement' => $schema->string()->description('Placement area: "home_hero" or "home_promo". Default is "home_promo".'),
            'image_path' => $schema->string()->description('Path or URL of the banner image.'),
            'cta_type' => $schema->string()->description('Call-to-Action button type: "route", "url", or "whatsapp".'),
            'cta_value' => $schema->string()->description('Destination route, link URL, or WhatsApp phone number.'),
            'cta_label' => $schema->union(['string', 'object'])->description('Button label text. Single string or multilingual map.'),
            'sort_order' => $schema->integer()->description('Sorting weight for display order. Default is 0.'),
            'is_active' => $schema->boolean()->description('Publish status. Default is true.'),
            'starts_at' => $schema->string()->description('Optional campaign start schedule (ISO datetime).'),
            'ends_at' => $schema->string()->description('Optional campaign end schedule (ISO datetime).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $rawTitle = $request->get('title');
        if (empty($rawTitle)) {
            return Response::error('The "title" parameter is required.');
        }

        $title = $this->normalizeMultilingualInput($rawTitle);
        if (empty($title['id'])) {
            return Response::error('Title text for Indonesian (id) cannot be empty.');
        }

        $rawSubtitle = $request->get('subtitle');
        $subtitle = ! empty($rawSubtitle) ? $this->normalizeMultilingualInput($rawSubtitle) : null;

        $rawCtaLabel = $request->get('cta_label');
        $ctaLabel = ! empty($rawCtaLabel) ? $this->normalizeMultilingualInput($rawCtaLabel) : null;

        $rawPlacement = (string) $request->get('placement', 'home_promo');
        $placement = BannerPlacement::tryFrom($rawPlacement) ?? BannerPlacement::HomePromo;

        $rawCtaType = (string) $request->get('cta_type');
        $ctaType = ! empty($rawCtaType) ? BannerCtaType::tryFrom($rawCtaType) : null;

        $startsAt = $request->filled('starts_at') ? Carbon::parse((string) $request->get('starts_at')) : null;
        $endsAt = $request->filled('ends_at') ? Carbon::parse((string) $request->get('ends_at')) : null;

        $banner = Banner::create([
            'title' => $title,
            'subtitle' => $subtitle,
            'placement' => $placement,
            'image_path' => $request->get('image_path'),
            'cta_type' => $ctaType,
            'cta_value' => $request->get('cta_value'),
            'cta_label' => $ctaLabel,
            'sort_order' => max(0, (int) $request->get('sort_order', 0)),
            'is_active' => $request->has('is_active') ? (bool) $request->get('is_active') : true,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        return Response::json([
            'message' => 'Banner berhasil dibuat.',
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
                'created_at' => $banner->created_at?->toIso8601String(),
            ],
        ]);
    }
}
