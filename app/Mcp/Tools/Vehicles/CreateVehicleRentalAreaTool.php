<?php

namespace App\Mcp\Tools\Vehicles;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\VehicleRentalArea;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('create_vehicle_rental_area')]
#[Title('Create Vehicle Rental Area')]
#[Description('Create a new vehicle rental operational area with multilingual name/description, unique slug, and minimum rental duration.')]
class CreateVehicleRentalAreaTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->union(['string', 'object'])->description('Area name. Single string or multilingual JSON map {"id": "...", "en": "...", "ms": "..."}. Required.'),
            'slug' => $schema->string()->description('Unique URL slug. Auto-generated from Indonesian name if omitted.'),
            'description' => $schema->union(['string', 'object'])->description('Area description or coverage notes. Single string or multilingual map.'),
            'minimum_rental_days' => $schema->integer()->description('Minimum rental duration in days (default: 1).'),
            'is_active' => $schema->boolean()->description('Active operational status (default: true).'),
            'sort_order' => $schema->integer()->description('Display sorting weight (default: 0).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $rawName = $request->get('name');
        if (empty($rawName)) {
            return Response::error('The "name" parameter is required.');
        }

        $name = $this->normalizeMultilingualInput($rawName);
        if (empty($name['id'])) {
            return Response::error('Area name for Indonesian (id) cannot be empty.');
        }

        $rawDesc = $request->get('description');
        $description = ! empty($rawDesc)
            ? $this->normalizeMultilingualInput($rawDesc)
            : null;

        $rawSlug = $request->get('slug');
        $slug = ! empty($rawSlug)
            ? Str::slug((string) $rawSlug)
            : Str::slug($name['id']);

        if (empty($slug)) {
            $slug = 'area-'.Str::random(6);
        }

        if (VehicleRentalArea::query()->where('slug', $slug)->exists()) {
            if (! empty($rawSlug)) {
                return Response::error("Vehicle rental area with slug '{$slug}' already exists.");
            }
            $slug .= '-'.Str::random(4);
        }

        $area = VehicleRentalArea::create([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'minimum_rental_days' => max(1, (int) $request->get('minimum_rental_days', 1)),
            'is_active' => $request->has('is_active') ? (bool) $request->get('is_active') : true,
            'sort_order' => max(0, (int) $request->get('sort_order', 0)),
        ]);

        return Response::json([
            'message' => 'Wilayah sewa kendaraan berhasil dibuat.',
            'area' => [
                'id' => $area->id,
                'slug' => $area->slug,
                'name' => $area->getTranslations('name'),
                'description' => $area->getTranslations('description'),
                'minimum_rental_days' => $area->minimum_rental_days,
                'is_active' => (bool) $area->is_active,
                'sort_order' => $area->sort_order,
                'created_at' => $area->created_at?->toIso8601String(),
            ],
        ]);
    }
}
