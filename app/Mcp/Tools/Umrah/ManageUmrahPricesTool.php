<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Umrah;

use App\Enums\UmrahRoomType;
use App\Models\UmrahPackage;
use App\Models\UmrahPackagePrice;
use App\Models\UmrahPackagePrivatePrice;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('manage_umrah_prices')]
#[Title('Manage Umrah Room & Private Pricing')]
#[Description('Create, update, bulk-sync, or delete room-based prices (quad/triple/double/single) or private package pricing brackets for an Umrah package.')]
class ManageUmrahPricesTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()->description('Operation to perform: "upsert" (add/update room price), "sync" (replace room prices array), or "delete" (remove price). Default: "upsert".'),
            'umrah_package_id' => $schema->integer()->description('Numeric ID of the Umrah package.')->required(),
            'pricing_category' => $schema->string()->description('Pricing category: "room" (standard room types) or "private" (private pax matrix). Default: "room".'),
            'price_id' => $schema->integer()->description('Numeric ID of the specific price record (required for "delete").'),
            'room_type' => $schema->string()->description('Room type for standard pricing: "quad", "triple", "double", or "single".'),
            'price_idr' => $schema->integer()->description('Price amount in IDR (min: 1).'),
            'duration_nights' => $schema->integer()->description('Duration in nights (required for private pricing).'),
            'pax' => $schema->integer()->description('Participant count (required for private pricing).'),
            'prices' => $schema->array()->description('Array of room prices for "sync": [{"room_type": "quad", "price_idr": 28500000}, {"room_type": "double", "price_idr": 34000000}].'),
        ];
    }

    public function handle(Request $request): Response
    {
        $action = strtolower(trim((string) $request->get('action', '')));
        if (empty($action)) {
            $action = ($request->has('prices') || $request->has('private_prices')) ? 'sync' : 'upsert';
        }

        if (! in_array($action, ['upsert', 'sync', 'delete'], true)) {
            return Response::error("Invalid action '{$action}'. Allowed: upsert, sync, delete.");
        }

        $packageId = $request->get('umrah_package_id') ?? $request->get('package_id');
        if (empty($packageId)) {
            return Response::error('The "umrah_package_id" parameter is required.');
        }

        $package = UmrahPackage::find((int) $packageId);
        if (! $package) {
            return Response::error("Umrah package with ID {$packageId} not found.");
        }

        $pricingCategory = strtolower(trim((string) $request->get('pricing_category', 'room')));

        if ($action === 'delete') {
            $priceId = $request->get('price_id');
            if (empty($priceId)) {
                return Response::error('The "price_id" parameter is required when action is "delete".');
            }

            if ($pricingCategory === 'private') {
                $record = UmrahPackagePrivatePrice::where('umrah_package_id', $package->id)->find((int) $priceId);
            } else {
                $record = UmrahPackagePrice::where('umrah_package_id', $package->id)->find((int) $priceId);
            }

            if (! $record) {
                return Response::error("Price record with ID {$priceId} not found in Umrah package ID {$package->id}.");
            }

            $record->delete();

            return Response::json([
                'message' => 'Tarif paket umrah berhasil dihapus.',
                'deleted_price_id' => (int) $priceId,
            ]);
        }

        if ($action === 'sync') {
            $rawPrices = $request->get('prices');
            $rawPrivatePrices = $request->get('private_prices');

            if (! is_array($rawPrices) && ! is_array($rawPrivatePrices)) {
                return Response::error('Either "prices" or "private_prices" array parameter is required when action is "sync".');
            }

            DB::transaction(function () use ($package, $rawPrices, $rawPrivatePrices): void {
                if (is_array($rawPrices)) {
                    $prepared = [];
                    foreach ($rawPrices as $index => $row) {
                        if (! is_array($row)) {
                            continue;
                        }
                        $roomType = strtolower(trim((string) ($row['room_type'] ?? '')));
                        $priceIdr = (int) ($row['price_idr'] ?? 0);

                        if (UmrahRoomType::tryFrom($roomType) === null || $priceIdr <= 0) {
                            continue;
                        }

                        $prepared[] = [
                            'room_type' => $roomType,
                            'price_idr' => $priceIdr,
                        ];
                    }

                    $package->prices()->delete();
                    foreach ($prepared as $row) {
                        $package->prices()->create($row);
                    }
                }

                if (is_array($rawPrivatePrices)) {
                    $preparedPrivate = [];
                    foreach ($rawPrivatePrices as $row) {
                        if (! is_array($row)) {
                            continue;
                        }
                        $durationNights = (int) ($row['duration_nights'] ?? 0);
                        $pax = (int) ($row['pax'] ?? 0);
                        $priceIdr = (int) ($row['price_idr'] ?? 0);

                        if ($durationNights > 0 && $pax > 0 && $priceIdr > 0) {
                            $preparedPrivate[] = [
                                'duration_nights' => $durationNights,
                                'pax' => $pax,
                                'price_idr' => $priceIdr,
                            ];
                        }
                    }

                    $package->privatePrices()->delete();
                    foreach ($preparedPrivate as $row) {
                        $package->privatePrices()->create($row);
                    }
                }
            });

            $package->load(['prices', 'privatePrices']);

            return Response::json([
                'message' => 'Daftar harga paket umrah berhasil diperbarui.',
                'prices' => $package->prices->map(fn (UmrahPackagePrice $p): array => [
                    'id' => $p->id,
                    'room_type' => $p->room_type,
                    'price_idr' => (int) $p->price_idr,
                    'formatted_price' => number_format((float) $p->price_idr, 0, ',', '.'),
                ])->all(),
                'private_prices' => $package->privatePrices->map(fn (UmrahPackagePrivatePrice $pp): array => [
                    'id' => $pp->id,
                    'duration_nights' => (int) $pp->duration_nights,
                    'pax' => (int) $pp->pax,
                    'price_idr' => (int) $pp->price_idr,
                    'formatted_price' => number_format((float) $pp->price_idr, 0, ',', '.'),
                ])->all(),
            ]);
        }

        // Action === 'upsert'
        if ($pricingCategory === 'private') {
            $durationNights = (int) $request->get('duration_nights', 0);
            $pax = (int) $request->get('pax', 0);
            $priceIdr = (int) $request->get('price_idr', 0);

            if ($durationNights < 1 || $pax < 1 || $priceIdr < 1) {
                return Response::error('Parameters "duration_nights", "pax", and "price_idr" are all required and must be at least 1 for private pricing.');
            }

            $priceId = $request->get('price_id');
            $record = ! empty($priceId)
                ? UmrahPackagePrivatePrice::where('umrah_package_id', $package->id)->find((int) $priceId)
                : UmrahPackagePrivatePrice::where('umrah_package_id', $package->id)
                    ->where('duration_nights', $durationNights)
                    ->where('pax', $pax)
                    ->first();

            if (! $record) {
                $record = new UmrahPackagePrivatePrice([
                    'umrah_package_id' => $package->id,
                    'duration_nights' => $durationNights,
                    'pax' => $pax,
                ]);
            }

            $record->duration_nights = $durationNights;
            $record->pax = $pax;
            $record->price_idr = $priceIdr;
            $record->save();

            return Response::json([
                'message' => 'Tarif paket umrah private berhasil disimpan.',
                'private_price' => [
                    'id' => $record->id,
                    'duration_nights' => (int) $record->duration_nights,
                    'pax' => (int) $record->pax,
                    'price_idr' => (int) $record->price_idr,
                ],
            ]);
        }

        // Room pricing upsert
        $rawRoom = (string) $request->get('room_type');
        $enumRoom = UmrahRoomType::tryFrom($rawRoom);
        if (! $enumRoom) {
            return Response::error('The "room_type" parameter is required and must be: quad, triple, double, or single.');
        }

        $priceIdr = (int) $request->get('price_idr', 0);
        if ($priceIdr <= 0) {
            return Response::error('The "price_idr" parameter is required and must be greater than 0.');
        }

        $record = UmrahPackagePrice::firstOrNew([
            'umrah_package_id' => $package->id,
            'room_type' => $enumRoom->value,
        ]);
        $record->price_idr = $priceIdr;
        $record->save();

        return Response::json([
            'message' => 'Tarif kamar paket umrah berhasil disimpan.',
            'price' => [
                'id' => $record->id,
                'room_type' => $record->room_type,
                'price_idr' => (int) $record->price_idr,
            ],
        ]);
    }
}
