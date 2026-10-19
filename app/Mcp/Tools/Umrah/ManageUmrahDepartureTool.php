<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Umrah;

use App\Enums\UmrahDepartureStatus;
use App\Models\UmrahDeparture;
use App\Models\UmrahPackage;
use App\Models\UmrahPackagePrice;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('manage_umrah_departure')]
#[Title('Manage Umrah Departure Schedule')]
#[Description('Create, update, or delete an Umrah departure schedule, including departure/return dates, total/booked quota, and optional room price overrides.')]
class ManageUmrahDepartureTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()->description('Operation to perform: "create", "update", or "delete". Default: "create".'),
            'departure_id' => $schema->integer()->description('Numeric ID of the departure schedule (required for "update" and "delete").'),
            'package_id' => $schema->integer()->description('Numeric ID of the parent Umrah package (required for "create").'),
            'departure_date' => $schema->string()->description('Date of departure (format: YYYY-MM-DD). Required for "create".'),
            'return_date' => $schema->string()->description('Date of return (format: YYYY-MM-DD). Auto-calculated from package duration if omitted.'),
            'quota_total' => $schema->integer()->description('Total seat/quota capacity (min: 1). Required for "create".'),
            'quota_booked' => $schema->integer()->description('Currently booked seat count (min: 0, default: 0).'),
            'status' => $schema->string()->description('Optional status override ("open", "nearly_full", "full", "closed"). Auto-inferred from quota percentage if omitted.'),
            'price_override_idr' => $schema->integer()->description('Optional general price override in IDR for this specific departure.'),
            'price_overrides' => $schema->array()->description('Optional array of per-room-type price overrides: [{"room_type": "double", "price_idr": 35000000}].'),
        ];
    }

    public function handle(Request $request): Response
    {
        $action = strtolower(trim((string) $request->get('action', 'create')));
        if (! in_array($action, ['create', 'update', 'delete'], true)) {
            return Response::error("Invalid action '{$action}'. Allowed values: 'create', 'update', 'delete'.");
        }

        if ($action === 'delete') {
            $depId = $request->get('departure_id') ?? $request->get('id');
            if (empty($depId)) {
                return Response::error('The "departure_id" parameter is required when action is "delete".');
            }

            $departure = UmrahDeparture::find((int) $depId);
            if (! $departure) {
                return Response::error("Umrah departure schedule with ID {$depId} not found.");
            }

            DB::transaction(function () use ($departure): void {
                $departure->prices()->delete();
                $departure->delete();
            });

            return Response::json([
                'message' => 'Jadwal keberangkatan umrah berhasil dihapus.',
                'deleted_departure_id' => (int) $depId,
            ]);
        }

        if ($action === 'create') {
            $packageId = $request->get('package_id') ?? $request->get('umrah_package_id');
            if (empty($packageId)) {
                return Response::error('The "package_id" parameter is required when creating a departure.');
            }

            $package = UmrahPackage::find((int) $packageId);
            if (! $package) {
                return Response::error("Umrah package with ID {$packageId} not found.");
            }

            $depDateStr = $request->get('departure_date');
            if (empty($depDateStr)) {
                return Response::error('The "departure_date" parameter is required (YYYY-MM-DD).');
            }

            try {
                $departureDate = Carbon::parse($depDateStr);
            } catch (\Throwable) {
                return Response::error("Invalid departure_date '{$depDateStr}'. Expected YYYY-MM-DD.");
            }

            $returnDate = null;
            if ($request->filled('return_date')) {
                try {
                    $returnDate = Carbon::parse($request->get('return_date'));
                } catch (\Throwable) {
                    return Response::error('Invalid return_date format. Expected YYYY-MM-DD.');
                }
            } else {
                $days = max(1, (int) $package->duration_days);
                $returnDate = (clone $departureDate)->addDays($days - 1);
            }

            $quotaTotal = (int) ($request->get('quota_total') ?? $request->get('quota', 0));
            if ($quotaTotal < 1) {
                return Response::error('The "quota_total" parameter is required and must be at least 1.');
            }

            $quotaBooked = max(0, (int) ($request->get('quota_booked') ?? $request->get('seat_booked', 0)));

            $status = 'open';
            if ($request->filled('status')) {
                $rawStatus = (string) $request->get('status');
                $enumStatus = UmrahDepartureStatus::tryFrom($rawStatus);
                if (! $enumStatus) {
                    return Response::error("Invalid status '{$rawStatus}'. Allowed: open, nearly_full, full, closed.");
                }
                $status = $enumStatus->value;
            }

            $priceOverride = $request->filled('price_override_idr') ? (int) $request->get('price_override_idr') : null;

            $departure = DB::transaction(function () use ($package, $departureDate, $returnDate, $quotaTotal, $quotaBooked, $status, $priceOverride, $request): UmrahDeparture {
                $dep = UmrahDeparture::create([
                    'package_id' => $package->id,
                    'departure_date' => $departureDate->toDateString(),
                    'return_date' => $returnDate->toDateString(),
                    'quota_total' => $quotaTotal,
                    'quota_booked' => $quotaBooked,
                    'status' => $status,
                    'price_override_idr' => $priceOverride,
                ]);

                if ($request->has('price_overrides') && is_array($request->get('price_overrides'))) {
                    $this->syncDeparturePrices($dep, $request->get('price_overrides'));
                }

                return $dep;
            });

            return Response::json([
                'message' => 'Jadwal keberangkatan umrah berhasil dibuat.',
                'departure' => [
                    'id' => $departure->id,
                    'package_id' => $departure->package_id,
                    'departure_date' => $departure->departure_date?->toDateString(),
                    'return_date' => $departure->return_date?->toDateString(),
                    'quota_total' => (int) $departure->quota_total,
                    'quota_booked' => (int) $departure->quota_booked,
                    'quota_sisa' => (int) $departure->quota_sisa,
                    'status' => $departure->status,
                    'price_override_idr' => $departure->price_override_idr,
                ],
            ]);
        }

        // Action === 'update'
        $depId = $request->get('departure_id') ?? $request->get('id');
        if (empty($depId)) {
            return Response::error('The "departure_id" parameter is required when updating a departure.');
        }

        $departure = UmrahDeparture::find((int) $depId);
        if (! $departure) {
            return Response::error("Umrah departure schedule with ID {$depId} not found.");
        }

        if ($request->filled('departure_date')) {
            try {
                $departure->departure_date = Carbon::parse($request->get('departure_date'))->toDateString();
            } catch (\Throwable) {
                return Response::error('Invalid departure_date format.');
            }
        }

        if ($request->filled('return_date')) {
            try {
                $departure->return_date = Carbon::parse($request->get('return_date'))->toDateString();
            } catch (\Throwable) {
                return Response::error('Invalid return_date format.');
            }
        }

        if ($request->filled('quota_total') || $request->filled('quota')) {
            $total = (int) ($request->get('quota_total') ?? $request->get('quota'));
            if ($total < 1) {
                return Response::error('The "quota_total" must be at least 1.');
            }
            $departure->quota_total = $total;
        }

        if ($request->has('quota_booked') || $request->has('seat_booked')) {
            $departure->quota_booked = max(0, (int) ($request->get('quota_booked') ?? $request->get('seat_booked')));
        }

        if ($request->filled('status')) {
            $rawStatus = (string) $request->get('status');
            $enumStatus = UmrahDepartureStatus::tryFrom($rawStatus);
            if (! $enumStatus) {
                return Response::error("Invalid status '{$rawStatus}'.");
            }
            $departure->status = $enumStatus->value;
        }

        if ($request->has('price_override_idr')) {
            $override = $request->get('price_override_idr');
            $departure->price_override_idr = $override !== null ? (int) $override : null;
        }

        DB::transaction(function () use ($departure, $request): void {
            $departure->save();

            if ($request->has('price_overrides') && is_array($request->get('price_overrides'))) {
                $this->syncDeparturePrices($departure, $request->get('price_overrides'));
            }
        });

        return Response::json([
            'message' => 'Jadwal keberangkatan umrah berhasil diperbarui.',
            'departure' => [
                'id' => $departure->id,
                'package_id' => $departure->package_id,
                'departure_date' => $departure->departure_date?->toDateString(),
                'return_date' => $departure->return_date?->toDateString(),
                'quota_total' => (int) $departure->quota_total,
                'quota_booked' => (int) $departure->quota_booked,
                'quota_sisa' => (int) $departure->quota_sisa,
                'status' => $departure->status,
                'price_override_idr' => $departure->price_override_idr,
            ],
        ]);
    }

    /**
     * @param  array<int, mixed>  $overrides
     */
    protected function syncDeparturePrices(UmrahDeparture $departure, array $overrides): void
    {
        $departure->prices()->delete();

        foreach ($overrides as $item) {
            if (! is_array($item)) {
                continue;
            }

            $priceIdr = isset($item['price_idr']) ? (int) $item['price_idr'] : 0;
            if ($priceIdr <= 0) {
                continue;
            }

            $packagePriceId = $item['umrah_package_price_id'] ?? null;

            if (empty($packagePriceId) && ! empty($item['room_type'])) {
                $matched = UmrahPackagePrice::where('umrah_package_id', $departure->package_id)
                    ->where('room_type', (string) $item['room_type'])
                    ->first();
                $packagePriceId = $matched?->id;
            }

            if (! empty($packagePriceId)) {
                $departure->prices()->create([
                    'umrah_package_price_id' => (int) $packagePriceId,
                    'price_idr' => $priceIdr,
                ]);
            }
        }
    }
}
