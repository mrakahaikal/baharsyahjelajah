<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Tours\Packages;

use App\Models\PackageTier;
use App\Models\TourPackage;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_tour_package')]
#[Title('Delete Tour Package')]
#[Description('Permanently delete a tour package variant by ID along with its tiers, pricing, itineraries, and inclusions.')]
class DeleteTourPackageTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Numeric ID of the tour package to delete.')->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        if (empty($id)) {
            return Response::error('The "id" parameter is required.');
        }

        $package = TourPackage::with(['tiers', 'itineraries', 'includes'])->find((int) $id);
        if (! $package) {
            return Response::error("Tour package with ID {$id} not found.");
        }

        $nameSummary = $package->getTranslation('name', 'id', false) ?: $package->getTranslation('name', 'en', false);

        DB::transaction(function () use ($package): void {
            $package->tiers->each(function (PackageTier $tier): void {
                $tier->priceTiers()->delete();
                $tier->delete();
            });
            $package->includes()->delete();
            $package->itineraries->each(fn ($itinerary) => $itinerary->delete());
            $package->delete();
        });

        return Response::json([
            'message' => 'Paket tur berhasil dihapus.',
            'deleted_package' => [
                'id' => (int) $id,
                'name' => $nameSummary,
            ],
        ]);
    }
}
