<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Umrah;

use App\Enums\UmrahRoomType;
use App\Models\UmrahDeparture;
use App\Models\UmrahPackage;
use App\Services\CurrencyService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('calculate_umrah_price')]
#[Title('Calculate Umrah Package Price Estimate')]
#[Description('Calculate per-pilgrim and total Umrah package price estimates based on departure date, room type (quad/triple/double/single), participant count, and currency conversion.')]
class CalculateUmrahPriceTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'umrah_package_id' => $schema->integer()->description('Numeric ID of the Umrah package.')->required(),
            'departure_id' => $schema->integer()->description('Optional specific departure schedule ID (applies date-specific price overrides if present).'),
            'room_type' => $schema->string()->description('Optional room type: "quad" (4 persons), "triple" (3 persons), "double" (2 persons), or "single" (1 person). If omitted, estimates for all available room types are returned.'),
            'pax' => $schema->integer()->description('Number of pilgrims (pax, min: 1, default: 1).'),
            'currency' => $schema->string()->description('Optional target 3-letter currency code for conversion (e.g., "IDR", "MYR", "USD", "SAR").'),
            'locale' => $schema->string()->description('Preferred output locale ("id", "en", "ms"). Default: "id".'),
        ];
    }

    public function handle(Request $request): Response
    {
        $packageId = $request->get('umrah_package_id');
        if (empty($packageId)) {
            return Response::error('The "umrah_package_id" parameter is required.');
        }

        $package = UmrahPackage::with(['prices', 'privatePrices'])->find((int) $packageId);
        if (! $package) {
            return Response::error("Umrah package with ID {$packageId} not found.");
        }

        $pax = max(1, (int) $request->get('pax', 1));
        $locale = (string) $request->get('locale', 'id');
        $currencyService = app(CurrencyService::class);
        $targetCurrency = $request->filled('currency')
            ? strtoupper(trim((string) $request->get('currency')))
            : null;

        $departure = null;
        if ($request->filled('departure_id')) {
            $departure = UmrahDeparture::with('prices.packagePrice')
                ->where('package_id', $package->id)
                ->find((int) $request->get('departure_id'));

            if (! $departure) {
                return Response::error("Departure schedule ID {$request->get('departure_id')} not found in Umrah package ID {$package->id}.");
            }
        }

        $estimates = [];

        if ($package->package_type === 'private') {
            $privateRates = $package->privatePrices;
            if ($privateRates->isNotEmpty()) {
                $matchedRate = $privateRates->where('pax', '<=', $pax)->sortByDesc('pax')->first()
                    ?? $privateRates->first();

                if ($matchedRate) {
                    $unitPrice = (float) $matchedRate->price_idr;
                    $totalPrice = $unitPrice * $pax;
                    $outputCurrency = $targetCurrency ?: 'IDR';

                    $estimates[] = [
                        'type' => 'private',
                        'duration_nights' => $matchedRate->duration_nights,
                        'pax_bracket' => $matchedRate->pax,
                        'unit_price_idr' => $unitPrice,
                        'total_price_idr' => $totalPrice,
                        'formatted_price_per_pax' => $currencyService->convert($unitPrice, $outputCurrency, 'IDR'),
                        'formatted_total_price' => $currencyService->convert($totalPrice, $outputCurrency, 'IDR'),
                        'target_currency' => $outputCurrency,
                    ];
                }
            }
        }

        if (empty($estimates)) {
            $roomTypes = $package->prices;
            if ($request->filled('room_type')) {
                $rawRoom = (string) $request->get('room_type');
                $enumRoom = UmrahRoomType::tryFrom($rawRoom);
                if (! $enumRoom) {
                    return Response::error("Invalid room_type '{$rawRoom}'. Allowed: quad, triple, double, single.");
                }
                $roomTypes = $roomTypes->where('room_type', $enumRoom->value)->values();
                if ($roomTypes->isEmpty()) {
                    return Response::error("Room type '{$rawRoom}' not configured for Umrah package ID {$package->id}.");
                }
            }

            if ($roomTypes->isEmpty()) {
                // Fallback to base package price
                $unitPrice = (float) ($departure?->price_override_idr ?: $package->price_idr);
                $totalPrice = $unitPrice * $pax;
                $outputCurrency = $targetCurrency ?: 'IDR';

                $estimates[] = [
                    'room_type' => 'standard',
                    'room_type_label' => 'Standar',
                    'unit_price_idr' => $unitPrice,
                    'total_price_idr' => $totalPrice,
                    'formatted_price_per_pax' => $currencyService->convert($unitPrice, $outputCurrency, 'IDR'),
                    'formatted_total_price' => $currencyService->convert($totalPrice, $outputCurrency, 'IDR'),
                    'target_currency' => $outputCurrency,
                ];
            } else {
                foreach ($roomTypes as $roomPrice) {
                    $unitPrice = (float) $package->getPriceForDeparture($departure, $roomPrice);
                    $totalPrice = $unitPrice * $pax;
                    $outputCurrency = $targetCurrency ?: 'IDR';
                    $enumObj = UmrahRoomType::tryFrom((string) $roomPrice->room_type);

                    $estimates[] = [
                        'room_type' => $roomPrice->room_type,
                        'room_type_label' => $enumObj?->getLabel() ?? ucfirst((string) $roomPrice->room_type),
                        'unit_price_idr' => $unitPrice,
                        'total_price_idr' => $totalPrice,
                        'formatted_price_per_pax' => $currencyService->convert($unitPrice, $outputCurrency, 'IDR'),
                        'formatted_total_price' => $currencyService->convert($totalPrice, $outputCurrency, 'IDR'),
                        'target_currency' => $outputCurrency,
                    ];
                }
            }
        }

        $message = $package->package_type === 'private'
            ? 'Estimasi harga paket umrah private berhasil dihitung.'
            : 'Estimasi harga paket umrah berhasil dihitung.';

        return Response::json([
            'message' => $message,
            'package' => [
                'id' => $package->id,
                'name' => $package->getTranslation('name', $locale, false) ?: $package->getTranslation('name', 'id', false),
                'package_type' => $package->package_type,
                'duration_days' => (int) $package->duration_days,
                'airline' => $package->airline,
                'hotel_makkah' => $package->hotel_makkah ? "{$package->hotel_makkah} ({$package->hotel_makkah_stars} Bintang)" : null,
                'hotel_madinah' => $package->hotel_madinah ? "{$package->hotel_madinah} ({$package->hotel_madinah_stars} Bintang)" : null,
                'visa_included' => (bool) $package->visa_included,
                'handling_included' => (bool) $package->handling_included,
            ],
            'departure' => $departure ? [
                'id' => $departure->id,
                'departure_date' => $departure->departure_date?->toDateString(),
                'return_date' => $departure->return_date?->toDateString(),
                'status' => $departure->status,
                'quota_sisa' => (int) $departure->quota_sisa,
            ] : null,
            'pax' => $pax,
            'estimates' => $estimates,
            'locale' => $locale,
        ]);
    }
}
