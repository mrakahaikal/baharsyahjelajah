<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Tours;

use App\Models\PackageTier;
use App\Models\Tour;
use App\Models\TourPackage;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_tour')]
#[Title('Delete Tour')]
#[Description('Permanently delete a tour by ID along with its packages, tiers, pricing, itineraries, and inclusions.')]
class DeleteTourTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Numeric ID of the tour to delete.')->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        if (empty($id)) {
            return Response::error('The "id" parameter is required.');
        }

        $tour = Tour::with(['packages.tiers', 'packages.itineraries', 'packages.includes'])->find((int) $id);
        if (! $tour) {
            return Response::error("Tour with ID {$id} not found.");
        }

        $nameSummary = $tour->getTranslation('name', 'id', false) ?: $tour->getTranslation('name', 'en', false);
        $packagesCount = $tour->packages->count();

        DB::transaction(function () use ($tour): void {
            $tour->packages->each(function (TourPackage $package): void {
                $package->tiers->each(function (PackageTier $tier): void {
                    $tier->priceTiers()->delete();
                    $tier->delete();
                });
                $package->includes()->delete();
                $package->itineraries->each(fn ($itinerary) => $itinerary->delete());
                $package->delete();
            });

            $tour->delete();
        });

        return Response::json([
            'message' => 'Tur beserta seluruh paket terkait berhasil dihapus.',
            'deleted_tour' => [
                'id' => (int) $id,
                'name' => $nameSummary,
                'deleted_packages_count' => $packagesCount,
            ],
        ]);
    }
}
