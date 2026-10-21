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

#[Name('update_vehicle_rental_area')]
#[Title('Update Vehicle Rental Area')]
#[Description('Update vehicle rental operational area attributes (multilingual name, slug, description, minimum rental days, or active status).')]
class UpdateVehicleRentalAreaTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Vehicle rental area numeric ID to update.'),
            'slug' => $schema->string()->description('Vehicle rental area unique slug to update.'),
            'name' => $schema->union(['string', 'object'])->description('New area name (single string or multilingual JSON map).'),
            'new_slug' => $schema->string()->description('New unique URL slug replacement.'),
            'description' => $schema->union(['string', 'object'])->description('New area description (single string or multilingual JSON map).'),
            'minimum_rental_days' => $schema->integer()->description('Updated minimum rental duration in days.'),
            'is_active' => $schema->boolean()->description('Updated active operational status.'),
            'sort_order' => $schema->integer()->description('Updated display sorting weight.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $slug = $request->get('slug');

        if (empty($id) && empty($slug)) {
            return Response::error('Either "id" or "slug" must be provided to identify the rental area.');
        }

        $area = VehicleRentalArea::query()
            ->when(! empty($id), fn ($q) => $q->where('id', (int) $id))
            ->when(! empty($slug), fn ($q) => $q->where('slug', (string) $slug))
            ->first();

        if (! $area) {
            $identifier = ! empty($id) ? "ID {$id}" : "slug '{$slug}'";

            return Response::error("Vehicle rental area with {$identifier} not found.");
        }

        if ($request->has('name')) {
            $normalizedName = $this->normalizeMultilingualInput($request->get('name'));
            if (! empty($normalizedName['id'])) {
                $mergedName = array_merge($area->getTranslations('name'), $normalizedName);
                $area->setTranslations('name', $mergedName);
            }
        }

        if ($request->has('description')) {
            $normalizedDesc = $this->normalizeMultilingualInput($request->get('description'));
            $mergedDesc = array_merge($area->getTranslations('description'), $normalizedDesc);
            $area->setTranslations('description', $mergedDesc);
        }

        if ($request->filled('new_slug')) {
            $newSlug = Str::slug((string) $request->get('new_slug'));
            $slugExists = VehicleRentalArea::query()
                ->where('slug', $newSlug)
                ->where('id', '!=', $area->id)
                ->exists();

            if ($slugExists) {
                return Response::error("Vehicle rental area with slug '{$newSlug}' already exists.");
            }
            $area->slug = $newSlug;
        }

        if ($request->has('minimum_rental_days')) {
            $area->minimum_rental_days = max(1, (int) $request->get('minimum_rental_days'));
        }

        if ($request->has('is_active')) {
            $area->is_active = (bool) $request->get('is_active');
        }

        if ($request->has('sort_order')) {
            $area->sort_order = max(0, (int) $request->get('sort_order'));
        }

        $area->save();

        return Response::json([
            'message' => 'Wilayah sewa kendaraan berhasil diperbarui.',
            'area' => [
                'id' => $area->id,
                'slug' => $area->slug,
                'name' => $area->getTranslations('name'),
                'description' => $area->getTranslations('description'),
                'minimum_rental_days' => $area->minimum_rental_days,
                'is_active' => (bool) $area->is_active,
                'sort_order' => $area->sort_order,
                'updated_at' => $area->updated_at?->toIso8601String(),
            ],
        ]);
    }
}
