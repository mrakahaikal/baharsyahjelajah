<?php

namespace App\Mcp\Tools\Testimonials;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Testimonial;
use App\Models\Tour;
use App\Models\UmrahPackage;
use App\Models\Vehicle;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('update_testimonial')]
#[Title('Update Testimonial Entry')]
#[Description('Update customer review and testimonial fields, including translations, rating, associated product, featured, or active status.')]
class UpdateTestimonialTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Testimonial numeric ID to update.')->required(),
            'reviewer_name' => $schema->union(['string', 'object'])->description('Updated reviewer name (single string or multilingual JSON map).'),
            'content' => $schema->union(['string', 'object'])->description('Updated testimonial text (single string or multilingual JSON map).'),
            'rating' => $schema->integer()->description('Updated star rating score from 1 to 5.'),
            'product_type' => $schema->string()->description('Updated product type: "tour", "vehicle", or "umrah".'),
            'product_id' => $schema->integer()->description('Updated product numeric ID.'),
            'reviewer_country' => $schema->string()->description('Updated reviewer country name.'),
            'reviewer_flag' => $schema->string()->description('Updated country code.'),
            'photo' => $schema->string()->description('Updated reviewer photo path or URL.'),
            'is_featured' => $schema->boolean()->description('Updated featured status.'),
            'is_active' => $schema->boolean()->description('Updated publish status.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        if (empty($id)) {
            return Response::error('The "id" parameter is required.');
        }

        $item = Testimonial::find((int) $id);
        if (! $item) {
            return Response::error("Testimonial with ID {$id} not found.");
        }

        if ($request->has('reviewer_name')) {
            $normalizedName = $this->normalizeMultilingualInput($request->get('reviewer_name'));
            if (! empty($normalizedName['id'])) {
                $mergedName = array_merge($item->getTranslations('reviewer_name'), $normalizedName);
                $item->setTranslations('reviewer_name', $mergedName);
            }
        }

        if ($request->has('content')) {
            $normalizedContent = $this->normalizeMultilingualInput($request->get('content'));
            if (! empty($normalizedContent['id'])) {
                $mergedContent = array_merge($item->getTranslations('content'), $normalizedContent);
                $item->setTranslations('content', $mergedContent);
            }
        }

        if ($request->has('rating')) {
            $rating = (int) $request->get('rating');
            if ($rating >= 1 && $rating <= 5) {
                $item->rating = $rating;
            }
        }

        if ($request->filled('product_type') || $request->filled('product_id')) {
            $rawType = (string) ($request->get('product_type') ?: $item->product_type);
            $productId = (int) ($request->get('product_id') ?: $item->product_id);

            $productType = match (strtolower($rawType)) {
                'tour' => Tour::class,
                'vehicle' => Vehicle::class,
                'umrah', 'umrah_package' => UmrahPackage::class,
                default => $rawType,
            };

            if (class_exists($productType) && $productType::query()->where('id', $productId)->exists()) {
                $item->product_type = $productType;
                $item->product_id = $productId;
            }
        }

        if ($request->has('reviewer_country')) {
            $item->reviewer_country = $request->get('reviewer_country');
        }

        if ($request->has('reviewer_flag')) {
            $item->reviewer_flag = $request->get('reviewer_flag');
        }

        if ($request->has('photo')) {
            $item->photo = $request->get('photo');
        }

        if ($request->has('is_featured')) {
            $item->is_featured = (bool) $request->get('is_featured');
        }

        if ($request->has('is_active')) {
            $item->is_active = (bool) $request->get('is_active');
        }

        $item->save();

        return Response::json([
            'message' => 'Testimonial berhasil diperbarui.',
            'testimonial' => [
                'id' => $item->id,
                'reviewer_name' => $item->getTranslations('reviewer_name'),
                'reviewer_country' => $item->reviewer_country,
                'reviewer_flag' => $item->reviewer_flag,
                'product_type' => class_basename($item->product_type),
                'product_id' => $item->product_id,
                'rating' => $item->rating,
                'stars' => $item->stars,
                'content' => $item->getTranslations('content'),
                'is_featured' => (bool) $item->is_featured,
                'is_active' => (bool) $item->is_active,
                'updated_at' => $item->updated_at?->toIso8601String(),
            ],
        ]);
    }
}
