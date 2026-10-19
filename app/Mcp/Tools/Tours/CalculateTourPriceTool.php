<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Tours;

use App\Models\PackageTier;
use App\Models\TourPackage;
use App\Services\CurrencyService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('calculate_tour_price')]
#[Title('Calculate Tour Package Price Estimate')]
#[Description('Calculate per-person and total tour package price estimates based on participant count (pax), optional package tier, and target currency conversion.')]
class CalculateTourPriceTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'tour_package_id' => $schema->integer()->description('Numeric ID of the tour package.')->required(),
            'pax' => $schema->integer()->description('Number of participants (pax, min: 1).')->required(),
            'package_tier_id' => $schema->integer()->description('Optional specific package tier ID (e.g., Silver, Gold, Platinum). If omitted, estimates for all available tiers are returned.'),
            'currency' => $schema->string()->description('Optional target 3-letter currency code for conversion (e.g., "IDR", "MYR", "USD").'),
            'locale' => $schema->string()->description('Preferred output locale ("id", "en", "ms"). Default: "id".'),
        ];
    }

    public function handle(Request $request): Response
    {
        $packageId = $request->get('tour_package_id');
        if (empty($packageId)) {
            return Response::error('The "tour_package_id" parameter is required.');
        }

        $pax = (int) $request->get('pax', 0);
        if ($pax < 1) {
            return Response::error('The "pax" parameter is required and must be at least 1.');
        }

        $locale = (string) $request->get('locale', 'id');

        $package = TourPackage::with(['tour', 'tiers.priceTiers'])->find((int) $packageId);
        if (! $package) {
            return Response::error("Tour package with ID {$packageId} not found.");
        }

        $tiers = $package->tiers;
        if ($request->filled('package_tier_id')) {
            $tierId = (int) $request->get('package_tier_id');
            $tiers = $tiers->where('id', $tierId)->values();
            if ($tiers->isEmpty()) {
                return Response::error("Package tier with ID {$tierId} not found in tour package ID {$packageId}.");
            }
        }

        $currencyService = app(CurrencyService::class);
        $targetCurrency = $request->filled('currency')
            ? strtoupper(trim((string) $request->get('currency')))
            : null;

        $estimates = $tiers->map(function (PackageTier $tier) use ($pax, $locale, $targetCurrency, $currencyService): array {
            $matchedPriceTier = $tier->priceTierForPax($pax);
            $tierName = $tier->getTranslation('name', $locale, false) ?: $tier->getTranslation('name', 'id', false);

            if (! $matchedPriceTier) {
                return [
                    'package_tier_id' => $tier->id,
                    'tier_name' => $tierName,
                    'hotel_stars' => $tier->hotel_stars,
                    'available_for_pax' => false,
                    'message' => "No pricing rule matches {$pax} pax for tier '{$tierName}'.",
                ];
            }

            $unitPrice = (float) $matchedPriceTier->price;
            $totalPrice = $unitPrice * $pax;
            $baseCurrency = $matchedPriceTier->currency;
            $outputCurrency = $targetCurrency ?: $baseCurrency;

            return [
                'package_tier_id' => $tier->id,
                'tier_name' => $tierName,
                'hotel_stars' => $tier->hotel_stars,
                'available_for_pax' => true,
                'matched_bracket' => [
                    'min_pax' => $matchedPriceTier->min_pax,
                    'max_pax' => $matchedPriceTier->max_pax,
                ],
                'base_currency' => $baseCurrency,
                'price_per_pax' => $unitPrice,
                'total_price' => $totalPrice,
                'formatted_price_per_pax' => $currencyService->convert($unitPrice, $outputCurrency, $baseCurrency),
                'formatted_total_price' => $currencyService->convert($totalPrice, $outputCurrency, $baseCurrency),
                'target_currency' => $outputCurrency,
            ];
        })->all();

        return Response::json([
            'tour_package' => [
                'id' => $package->id,
                'tour_id' => $package->tour_id,
                'tour_name' => $package->tour
                    ? ($package->tour->getTranslation('name', $locale, false) ?: $package->tour->getTranslation('name', 'id', false))
                    : null,
                'package_name' => $package->getTranslation('name', $locale, false) ?: $package->getTranslation('name', 'id', false),
                'duration_days' => (int) $package->duration_days,
                'duration_nights' => (int) $package->duration_nights,
                'duration_label' => $package->duration_label,
            ],
            'pax' => $pax,
            'estimates' => $estimates,
            'locale' => $locale,
        ]);
    }
}
