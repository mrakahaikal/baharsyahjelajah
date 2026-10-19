<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Tours;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\PackageTier;
use App\Models\TourPackage;
use App\Models\TourPriceTier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('manage_tour_pricing_tier')]
#[Title('Manage Tour Package Pricing Tier')]
#[Description('Create, update, or delete a quality tier (e.g., Silver, Gold, Hotel 4-Star) and its pax-based price brackets for a tour package.')]
class ManageTourPricingTierTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()->description('Operation to perform: "upsert" (create or update tier and its price brackets) or "delete" (remove tier). Default: "upsert".'),
            'tour_package_id' => $schema->integer()->description('Numeric ID of the parent tour package.')->required(),
            'package_tier_id' => $schema->integer()->description('Numeric ID of an existing package tier (required for "delete", optional for "upsert" when updating an existing tier).'),
            'name' => $schema->mixed()->description('Tier name (plain string or multilingual map, e.g., "Gold - Hotel Bintang 4"). Required when creating a new tier.'),
            'hotel_stars' => $schema->integer()->description('Optional hotel star rating (1 to 5).'),
            'price_tiers' => $schema->array()->description('Array of pax price brackets to sync on this tier: [{"min_pax": 2, "max_pax": 4, "price": 2500000, "currency": "IDR"}].'),
        ];
    }

    public function handle(Request $request): Response
    {
        $action = strtolower(trim((string) $request->get('action', 'upsert')));
        if (! in_array($action, ['upsert', 'delete'], true)) {
            return Response::error("Invalid action '{$action}'. Allowed values: 'upsert', 'delete'.");
        }

        $packageId = $request->get('tour_package_id');
        if (empty($packageId)) {
            return Response::error('The "tour_package_id" parameter is required.');
        }

        $package = TourPackage::find((int) $packageId);
        if (! $package) {
            return Response::error("Tour package with ID {$packageId} not found.");
        }

        if ($action === 'delete') {
            $tierId = $request->get('package_tier_id');
            if (empty($tierId)) {
                return Response::error('The "package_tier_id" parameter is required when action is "delete".');
            }

            $tier = PackageTier::where('tour_package_id', $package->id)->find((int) $tierId);
            if (! $tier) {
                return Response::error("Package tier with ID {$tierId} not found for tour package ID {$package->id}.");
            }

            $tierName = $tier->getTranslation('name', 'id', false) ?: $tier->getTranslation('name', 'en', false);

            DB::transaction(function () use ($tier): void {
                $tier->priceTiers()->delete();
                $tier->delete();
            });

            return Response::json([
                'message' => 'Tier harga paket tur berhasil dihapus.',
                'deleted_tier' => [
                    'id' => (int) $tierId,
                    'name' => $tierName,
                ],
            ]);
        }

        $tierId = $request->get('package_tier_id');
        $tier = null;

        if (! empty($tierId)) {
            $tier = PackageTier::where('tour_package_id', $package->id)->find((int) $tierId);
            if (! $tier) {
                return Response::error("Package tier with ID {$tierId} not found for tour package ID {$package->id}.");
            }
        }

        if (! $tier && empty($request->get('name'))) {
            return Response::error('The "name" parameter is required when creating a new package tier.');
        }

        if ($request->has('hotel_stars') && $request->get('hotel_stars') !== null) {
            $stars = (int) $request->get('hotel_stars');
            if ($stars < 1 || $stars > 5) {
                return Response::error('The "hotel_stars" parameter must be between 1 and 5.');
            }
        }

        $rawPriceTiers = $request->get('price_tiers');
        $validatedBrackets = [];

        if (is_array($rawPriceTiers)) {
            foreach ($rawPriceTiers as $index => $bracket) {
                if (! is_array($bracket)) {
                    return Response::error("Invalid price_tiers item at index {$index}: must be an object/array.");
                }
                $minPax = (int) ($bracket['min_pax'] ?? 0);
                $maxPax = isset($bracket['max_pax']) && $bracket['max_pax'] !== '' ? (int) $bracket['max_pax'] : null;
                $price = isset($bracket['price']) ? (float) $bracket['price'] : -1.0;
                $currency = strtoupper(trim((string) ($bracket['currency'] ?? 'IDR')));

                if ($minPax < 1) {
                    return Response::error("Invalid min_pax at index {$index}: must be at least 1.");
                }
                if ($maxPax !== null && $maxPax < $minPax) {
                    return Response::error("Invalid max_pax at index {$index}: must be greater than or equal to min_pax ({$minPax}).");
                }
                if ($price < 0) {
                    return Response::error("Invalid price at index {$index}: must be 0 or greater.");
                }

                $validatedBrackets[] = [
                    'min_pax' => $minPax,
                    'max_pax' => $maxPax,
                    'price' => $price,
                    'currency' => strlen($currency) === 3 ? $currency : 'IDR',
                ];
            }
        }

        $savedTier = DB::transaction(function () use ($request, $package, $tier, $validatedBrackets): PackageTier {
            if (! $tier) {
                $tier = new PackageTier(['tour_package_id' => $package->id]);
            }

            if ($request->has('name') && ! empty($request->get('name'))) {
                $rawName = $request->get('name');
                $existing = $tier->exists ? $tier->getTranslations('name') : [];
                $incoming = is_array($rawName) ? $rawName : $this->normalizeMultilingualInput($rawName);
                $tier->setTranslations('name', array_merge($existing, array_filter($incoming, fn ($v) => $v !== null && $v !== '')));
            }

            if ($request->has('hotel_stars')) {
                $tier->hotel_stars = $request->get('hotel_stars') !== null ? (int) $request->get('hotel_stars') : null;
            }

            $tier->save();

            if (is_array($request->get('price_tiers'))) {
                $tier->priceTiers()->delete();
                foreach ($validatedBrackets as $bracket) {
                    $tier->priceTiers()->create($bracket);
                }
            }

            return $tier->load('priceTiers');
        });

        return Response::json([
            'message' => 'Tier harga paket tur berhasil disimpan.',
            'tier' => [
                'id' => $savedTier->id,
                'tour_package_id' => $savedTier->tour_package_id,
                'name' => $savedTier->getTranslations('name'),
                'hotel_stars' => $savedTier->hotel_stars,
                'price_tiers' => $savedTier->priceTiers->map(fn (TourPriceTier $pt): array => [
                    'id' => $pt->id,
                    'min_pax' => $pt->min_pax,
                    'max_pax' => $pt->max_pax,
                    'price' => (float) $pt->price,
                    'currency' => $pt->currency,
                ])->all(),
            ],
        ]);
    }
}
