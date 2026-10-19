<?php

namespace App\Mcp\Tools\Testimonials;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Testimonial;
use App\Models\Tour;
use App\Models\UmrahPackage;
use App\Models\Vehicle;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_testimonials')]
#[Title('List Traveler & Customer Testimonials')]
#[Description('List customer reviews and testimonials with filters by product type (tour, vehicle, umrah), product ID, star rating, featured status, search, and pagination.')]
class ListTestimonialsTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'product_type' => $schema->string()->description('Filter by reviewed product: "tour", "vehicle", or "umrah".'),
            'product_id' => $schema->integer()->description('Filter by specific product numeric ID.'),
            'rating' => $schema->integer()->description('Filter by exact star rating (1-5).'),
            'min_rating' => $schema->integer()->description('Filter by minimum star rating (1-5).'),
            'is_featured' => $schema->boolean()->description('Filter by featured testimonial status.'),
            'is_active' => $schema->boolean()->description('Filter by active visibility status. Default is true.'),
            'search' => $schema->string()->description('Search keyword matching reviewer name or review content.'),
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

        $query = Testimonial::query();

        if ($request->has('is_active')) {
            $query->where('is_active', (bool) $request->get('is_active'));
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', (bool) $request->get('is_featured'));
        }

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->get('rating'));
        }

        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', (int) $request->get('min_rating'));
        }

        if ($request->filled('product_type')) {
            $type = (string) $request->get('product_type');
            $mappedType = match (strtolower($type)) {
                'tour' => Tour::class,
                'vehicle' => Vehicle::class,
                'umrah', 'umrah_package' => UmrahPackage::class,
                default => $type,
            };
            $query->where('product_type', $mappedType);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', (int) $request->get('product_id'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function (Builder $sub) use ($search): void {
                $sub->where('reviewer_name->id', 'like', "%{$search}%")
                    ->orWhere('reviewer_name->en', 'like', "%{$search}%")
                    ->orWhere('reviewer_name->ms', 'like', "%{$search}%")
                    ->orWhere('content->id', 'like', "%{$search}%")
                    ->orWhere('content->en', 'like', "%{$search}%")
                    ->orWhere('content->ms', 'like', "%{$search}%")
                    ->orWhere('reviewer_country', 'like', "%{$search}%");
            });
        }

        $query->orderByDesc('is_featured')
            ->orderByDesc('rating')
            ->orderByDesc('id');

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        $testimonials = collect($paginator->items())->map(function (Testimonial $item) use ($locale): array {
            $productName = null;
            if ($item->product) {
                $productName = method_exists($item->product, 'getTranslation')
                    ? ($item->product->getTranslation('name', $locale, false) ?: $item->product->getTranslation('name', 'id', false) ?: $item->product->getTranslation('title', $locale, false))
                    : ($item->product->name ?? $item->product->title ?? null);
            }

            return [
                'id' => $item->id,
                'reviewer_name' => $item->getTranslation('reviewer_name', $locale, false) ?: $item->getTranslation('reviewer_name', 'id', false),
                'reviewer_country' => $item->reviewer_country,
                'reviewer_flag' => $item->reviewer_flag,
                'product_type' => class_basename($item->product_type),
                'product_id' => $item->product_id,
                'product_name' => $productName,
                'rating' => $item->rating,
                'stars' => $item->stars,
                'content' => $item->getTranslation('content', $locale, false) ?: $item->getTranslation('content', 'id', false),
                'is_featured' => (bool) $item->is_featured,
                'is_active' => (bool) $item->is_active,
            ];
        })->all();

        return Response::json([
            'testimonials' => $testimonials,
            'pagination' => [
                'total' => $paginator->total(),
                'count' => count($testimonials),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
            ],
            'locale' => $locale,
        ]);
    }
}
