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

#[Name('create_testimonial')]
#[Title('Create Testimonial Entry')]
#[Description('Create a new customer review and testimonial associated with a Tour, Vehicle, or Umrah Package.')]
class CreateTestimonialTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'reviewer_name' => $schema->union(['string', 'object'])->description('Reviewer customer name. Single string or multilingual JSON map {"id": "...", "en": "...", "ms": "..."}. Required.'),
            'content' => $schema->union(['string', 'object'])->description('Review testimonial text. Single string or multilingual JSON map. Required.'),
            'rating' => $schema->integer()->description('Star rating score from 1 to 5. Required.'),
            'product_type' => $schema->string()->description('Associated product type: "tour", "vehicle", or "umrah". Required.'),
            'product_id' => $schema->integer()->description('Associated product numeric ID. Required.'),
            'reviewer_country' => $schema->string()->description('Reviewer country name (e.g. "Indonesia", "Malaysia").'),
            'reviewer_flag' => $schema->string()->description('Country code (e.g. "ID", "MY", "SA").'),
            'photo' => $schema->string()->description('Reviewer photo path or image URL.'),
            'is_featured' => $schema->boolean()->description('Highlight on homepage or featured banners. Default is false.'),
            'is_active' => $schema->boolean()->description('Publish status. Default is true.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $rawName = $request->get('reviewer_name');
        if (empty($rawName)) {
            return Response::error('The "reviewer_name" parameter is required.');
        }

        $reviewerName = $this->normalizeMultilingualInput($rawName);
        if (empty($reviewerName['id'])) {
            return Response::error('Reviewer name for Indonesian (id) cannot be empty.');
        }

        $rawContent = $request->get('content');
        if (empty($rawContent)) {
            return Response::error('The "content" parameter is required.');
        }

        $content = $this->normalizeMultilingualInput($rawContent);
        if (empty($content['id'])) {
            return Response::error('Review content for Indonesian (id) cannot be empty.');
        }

        $rating = $request->get('rating');
        if ($rating === null || (int) $rating < 1 || (int) $rating > 5) {
            return Response::error('The "rating" parameter is required and must be an integer between 1 and 5.');
        }

        $rawProductType = (string) $request->get('product_type');
        if (empty($rawProductType)) {
            return Response::error('The "product_type" parameter is required ("tour", "vehicle", "umrah").');
        }

        $productType = match (strtolower($rawProductType)) {
            'tour' => Tour::class,
            'vehicle' => Vehicle::class,
            'umrah', 'umrah_package' => UmrahPackage::class,
            default => $rawProductType,
        };

        $productId = $request->get('product_id');
        if (empty($productId)) {
            return Response::error('The "product_id" parameter is required.');
        }

        // Verify that product exists
        if (! class_exists($productType) || ! $productType::query()->where('id', (int) $productId)->exists()) {
            return Response::error("Associated product {$rawProductType} with ID {$productId} does not exist.");
        }

        $testimonial = Testimonial::create([
            'reviewer_name' => $reviewerName,
            'content' => $content,
            'rating' => (int) $rating,
            'product_type' => $productType,
            'product_id' => (int) $productId,
            'reviewer_country' => $request->get('reviewer_country'),
            'reviewer_flag' => $request->get('reviewer_flag'),
            'photo' => $request->get('photo'),
            'is_featured' => $request->has('is_featured') ? (bool) $request->get('is_featured') : false,
            'is_active' => $request->has('is_active') ? (bool) $request->get('is_active') : true,
        ]);

        return Response::json([
            'message' => 'Testimonial berhasil dibuat.',
            'testimonial' => [
                'id' => $testimonial->id,
                'reviewer_name' => $testimonial->getTranslations('reviewer_name'),
                'reviewer_country' => $testimonial->reviewer_country,
                'reviewer_flag' => $testimonial->reviewer_flag,
                'product_type' => class_basename($testimonial->product_type),
                'product_id' => $testimonial->product_id,
                'rating' => $testimonial->rating,
                'stars' => $testimonial->stars,
                'content' => $testimonial->getTranslations('content'),
                'is_featured' => (bool) $testimonial->is_featured,
                'is_active' => (bool) $testimonial->is_active,
                'created_at' => $testimonial->created_at?->toIso8601String(),
            ],
        ]);
    }
}
