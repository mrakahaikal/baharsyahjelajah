<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Umrah;

use App\Models\UmrahDeparture;
use App\Models\UmrahPackage;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_umrah_package')]
#[Title('Delete Umrah Package')]
#[Description('Permanently delete an Umrah package by ID along with its departures, room prices, private prices, itineraries, and inclusions.')]
class DeleteUmrahPackageTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Numeric ID of the Umrah package to delete.')->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        if (empty($id)) {
            return Response::error('The "id" parameter is required.');
        }

        $package = UmrahPackage::with(['departures.prices', 'prices', 'privatePrices', 'itineraries', 'includes'])->find((int) $id);
        if (! $package) {
            return Response::error("Umrah package with ID {$id} not found.");
        }

        $nameSummary = $package->getTranslation('name', 'id', false) ?: $package->getTranslation('name', 'en', false);

        DB::transaction(function () use ($package): void {
            $package->departures->each(function (UmrahDeparture $dep): void {
                $dep->prices()->delete();
                $dep->delete();
            });

            $package->prices()->delete();
            $package->privatePrices()->delete();
            $package->itineraries()->delete();
            $package->includes()->delete();

            $package->forceDelete();
        });

        return Response::json([
            'message' => 'Paket umrah berhasil dihapus.',
            'deleted_package' => [
                'id' => (int) $id,
                'name' => $nameSummary,
            ],
        ]);
    }
}
