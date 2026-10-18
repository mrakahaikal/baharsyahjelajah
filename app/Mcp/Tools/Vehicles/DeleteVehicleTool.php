<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Vehicles;

use App\Models\Vehicle;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_vehicle')]
#[Title('Delete Vehicle & Fleet Entry')]
#[Description('Safely delete (soft-delete) a fleet vehicle. Rejects deletion if linked to Tour or Umrah packages unless force=true is provided. Scope: mcp:admin.')]
class DeleteVehicleTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The numeric ID of the vehicle to delete.'),
            'slug' => $schema->string()->description('The URL slug of the vehicle to delete (used if "id" is omitted).'),
            'catalog_code' => $schema->string()->description('The catalog code of the vehicle to delete.'),
            'force' => $schema->boolean()->description('Force deletion by detaching from linked Tour and Umrah packages. Default is false.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $slug = $request->get('slug');
        $catalogCode = $request->get('catalog_code');

        if (empty($id) && empty($slug) && empty($catalogCode)) {
            return Response::error('At least one of "id", "slug", or "catalog_code" must be provided.');
        }

        /** @var Vehicle|null $vehicle */
        if (! empty($id)) {
            $vehicle = Vehicle::withTrashed()->find((int) $id);
        } elseif (! empty($catalogCode)) {
            $vehicle = Vehicle::withTrashed()->where('catalog_code', (string) $catalogCode)->first();
        } else {
            $vehicle = Vehicle::withTrashed()->whereLocalizedSlug((string) $slug)->first();
        }

        if (! $vehicle) {
            $identifier = ! empty($id)
                ? "ID {$id}"
                : (! empty($catalogCode) ? "catalog code '{$catalogCode}'" : "slug '{$slug}'");

            return Response::error("Vehicle with {$identifier} not found.");
        }

        $force = (bool) $request->get('force', false);

        $toursCount = $vehicle->tourPackages()->count();
        $umrahCount = $vehicle->umrahPackages()->count();
        $totalLinked = $toursCount + $umrahCount;

        $vehicleName = $vehicle->getTranslation('name', 'id', false);

        if ($totalLinked > 0 && ! $force) {
            return Response::error(
                "Cannot delete vehicle '{$vehicleName}' because it is currently linked to {$totalLinked} package(s) ({$toursCount} tour package(s), {$umrahCount} umrah package(s)). Set 'force' => true to detach links and delete."
            );
        }

        $vehicleId = $vehicle->id;

        DB::transaction(function () use ($vehicle): void {
            $vehicle->tourPackages()->detach();
            $vehicle->umrahPackages()->detach();
            $vehicle->countries()->detach();
            $vehicle->rentalRates()->delete();
            $vehicle->delete();
        });

        return Response::json([
            'message' => "Vehicle '{$vehicleName}' (ID {$vehicleId}) deleted successfully.",
            'deleted_id' => $vehicleId,
            'detached_links' => [
                'tour_packages' => $toursCount,
                'umrah_packages' => $umrahCount,
            ],
            'force' => $force,
        ]);
    }
}
