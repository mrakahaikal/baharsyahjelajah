<?php

namespace App\Mcp\Tools\Vehicles;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\VehicleRentalArea;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_vehicle_rental_areas')]
#[Title('List Vehicle Rental Areas')]
#[Description('List operational vehicle rental areas with search, active status filter, pagination, and fleet rate counts.')]
class ListVehicleRentalAreasTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Search keyword matching area name, slug, or description.'),
            'is_active' => $schema->boolean()->description('Filter by active status (true/false).'),
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

        $query = VehicleRentalArea::query()
            ->withCount(['rates as rates_count'])
            ->when($request->has('is_active'), function (Builder $q) use ($request): void {
                $q->where('is_active', (bool) $request->get('is_active'));
            })
            ->when($request->filled('search'), function (Builder $q) use ($request): void {
                $search = (string) $request->get('search');
                $q->where(function (Builder $sub) use ($search): void {
                    $sub->where('slug', 'like', "%{$search}%")
                        ->orWhere('name->id', 'like', "%{$search}%")
                        ->orWhere('name->en', 'like', "%{$search}%")
                        ->orWhere('name->ms', 'like', "%{$search}%")
                        ->orWhere('description->id', 'like', "%{$search}%")
                        ->orWhere('description->en', 'like', "%{$search}%")
                        ->orWhere('description->ms', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('id');

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        $areas = collect($paginator->items())->map(function (VehicleRentalArea $area) use ($locale): array {
            $activeVehiclesCount = $area->rates()
                ->where('is_active', true)
                ->distinct('vehicle_id')
                ->count('vehicle_id');

            return [
                'id' => $area->id,
                'slug' => $area->slug,
                'name' => $area->getTranslation('name', $locale, false) ?: $area->getTranslation('name', 'id', false),
                'name_translations' => $area->getTranslations('name'),
                'description' => $area->getTranslation('description', $locale, false) ?: $area->getTranslation('description', 'id', false),
                'minimum_rental_days' => $area->minimum_rental_days,
                'is_active' => (bool) $area->is_active,
                'sort_order' => $area->sort_order,
                'rates_count' => $area->rates_count,
                'active_vehicles_count' => $activeVehiclesCount,
            ];
        })->all();

        return Response::json([
            'areas' => $areas,
            'pagination' => [
                'total' => $paginator->total(),
                'count' => count($areas),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
            ],
            'locale' => $locale,
        ]);
    }
}
