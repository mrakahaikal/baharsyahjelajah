<?php

namespace App\Mcp\Tools\Vehicles;

use App\Models\VehicleRentalArea;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_vehicle_rental_area')]
#[Title('Delete Vehicle Rental Area')]
#[Description('Soft-delete a vehicle rental area with relation safeguards. Rejects deletion if vehicles rates are active unless force: true is provided.')]
class DeleteVehicleRentalAreaTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Vehicle rental area numeric ID to delete.'),
            'slug' => $schema->string()->description('Vehicle rental area unique URL slug to delete.'),
            'force' => $schema->boolean()->description('Force cascade deletion of associated rates and area (default: false).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $slug = $request->get('slug');
        $force = (bool) $request->get('force', false);

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

        $ratesCount = $area->rates()->count();

        if ($ratesCount > 0 && ! $force) {
            $areaName = $area->getTranslation('name', 'id', false) ?: $area->getTranslation('name', 'en', false);

            return Response::error("Cannot delete rental area '{$areaName}' because it is linked to {$ratesCount} active rental rate(s). Use force: true to cascade delete.");
        }

        DB::transaction(function () use ($area, $force): void {
            if ($force) {
                $area->rates()->delete();
            }

            $area->delete();
        });

        return Response::json([
            'message' => 'Wilayah sewa kendaraan berhasil dihapus.',
            'deleted_area' => [
                'id' => $area->id,
                'slug' => $area->slug,
                'name' => $area->getTranslation('name', 'id', false),
                'cascade_deleted_rates' => $force ? $ratesCount : 0,
            ],
        ]);
    }
}
