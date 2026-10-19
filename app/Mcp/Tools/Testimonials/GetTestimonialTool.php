<?php

namespace App\Mcp\Tools\Testimonials;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Testimonial;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('get_testimonial')]
#[Title('Get Testimonial Details')]
#[Description('Get complete customer review and testimonial details by ID, including reviewer details, associated product, and all language translations.')]
class GetTestimonialTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Testimonial numeric ID.')->required(),
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

        $item = Testimonial::with('product')->find((int) $id);

        if (! $item) {
            return Response::error("Testimonial with ID {$id} not found.");
        }

        $productName = null;
        if ($item->product) {
            $productName = method_exists($item->product, 'getTranslation')
                ? ($item->product->getTranslation('name', $locale, false) ?: $item->product->getTranslation('name', 'id', false) ?: $item->product->getTranslation('title', $locale, false))
                : ($item->product->name ?? $item->product->title ?? null);
        }

        return Response::json([
            'testimonial' => [
                'id' => $item->id,
                'reviewer_name' => $item->getTranslation('reviewer_name', $locale, false) ?: $item->getTranslation('reviewer_name', 'id', false),
                'reviewer_name_translations' => $item->getTranslations('reviewer_name'),
                'reviewer_country' => $item->reviewer_country,
                'reviewer_flag' => $item->reviewer_flag,
                'product_type' => class_basename($item->product_type),
                'product_id' => $item->product_id,
                'product_name' => $productName,
                'rating' => $item->rating,
                'stars' => $item->stars,
                'content' => $item->getTranslation('content', $locale, false) ?: $item->getTranslation('content', 'id', false),
                'content_translations' => $item->getTranslations('content'),
                'photo' => $item->photo,
                'is_featured' => (bool) $item->is_featured,
                'is_active' => (bool) $item->is_active,
                'created_at' => $item->created_at?->toIso8601String(),
                'updated_at' => $item->updated_at?->toIso8601String(),
            ],
            'locale' => $locale,
        ]);
    }
}
