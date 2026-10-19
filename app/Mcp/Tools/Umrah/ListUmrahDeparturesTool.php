<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Umrah;

use App\Enums\UmrahDepartureStatus;
use App\Models\UmrahDeparture;
use App\Models\UmrahDeparturePrice;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_umrah_departures')]
#[Title('List Umrah Departures')]
#[Description('List and filter scheduled Umrah pilgrimage departures by package ID, quota availability status (open/nearly_full/full/closed), and date ranges.')]
class ListUmrahDeparturesTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'package_id' => $schema->integer()->description('Filter departures by Umrah package ID.'),
            'status' => $schema->string()->description('Filter by quota status: "open", "nearly_full", "full", or "closed".'),
            'upcoming_only' => $schema->boolean()->description('Filter only future departures from today onward (default: true).'),
            'from_date' => $schema->string()->description('Filter departures on or after this date (format: YYYY-MM-DD).'),
            'to_date' => $schema->string()->description('Filter departures on or before this date (format: YYYY-MM-DD).'),
            'per_page' => $schema->integer()->description('Number of results per page (1-50, default: 20).'),
            'page' => $schema->integer()->description('Page number for pagination (default: 1).'),
            'locale' => $schema->string()->description('Preferred output locale ("id", "en", "ms"). Default: "id".'),
        ];
    }

    public function handle(Request $request): Response
    {
        $locale = (string) $request->get('locale', 'id');
        $perPage = min(50, max(1, (int) $request->get('per_page', 20)));
        $page = max(1, (int) $request->get('page', 1));
        $upcomingOnly = $request->has('upcoming_only') ? (bool) $request->get('upcoming_only') : true;

        $query = UmrahDeparture::query()->with(['package', 'prices.packagePrice']);

        $pkgId = $request->get('package_id') ?? $request->get('umrah_package_id');
        if (! empty($pkgId)) {
            $query->where('package_id', (int) $pkgId);
        }

        if ($request->filled('status')) {
            $status = (string) $request->get('status');
            $enumStatus = UmrahDepartureStatus::tryFrom($status);
            $query->where('status', $enumStatus?->value ?? $status);
        }

        if ($upcomingOnly) {
            $query->where('departure_date', '>=', now()->toDateString());
        }

        if ($request->filled('from_date')) {
            $query->where('departure_date', '>=', (string) $request->get('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->where('departure_date', '<=', (string) $request->get('to_date'));
        }

        $paginator = $query->orderBy('departure_date')->paginate(perPage: $perPage, page: $page);

        $departures = collect($paginator->items())->map(function (UmrahDeparture $dep) use ($locale): array {
            return [
                'id' => $dep->id,
                'package_id' => $dep->package_id,
                'package_name' => $dep->package
                    ? ($dep->package->getTranslation('name', $locale, false) ?: $dep->package->getTranslation('name', 'id', false))
                    : null,
                'package_type' => $dep->package?->package_type,
                'airline' => $dep->package?->airline,
                'departure_date' => $dep->departure_date?->toDateString(),
                'return_date' => $dep->return_date?->toDateString(),
                'quota_total' => (int) $dep->quota_total,
                'quota_booked' => (int) $dep->quota_booked,
                'quota_sisa' => (int) $dep->quota_sisa,
                'quota_percentage' => (int) $dep->quota_percentage,
                'status' => $dep->status,
                'status_label' => UmrahDepartureStatus::tryFrom((string) $dep->status)?->getLabel() ?? ucfirst((string) $dep->status),
                'price_override_idr' => $dep->price_override_idr ? (int) $dep->price_override_idr : null,
                'price_overrides' => $dep->prices->map(fn (UmrahDeparturePrice $dp): array => [
                    'id' => $dp->id,
                    'room_type' => $dp->packagePrice?->room_type,
                    'price_idr' => (int) $dp->price_idr,
                    'formatted_price' => $dp->formatted_price,
                ])->all(),
            ];
        })->all();

        return Response::json([
            'departures' => $departures,
            'pagination' => [
                'total' => $paginator->total(),
                'count' => count($departures),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
            ],
            'locale' => $locale,
        ]);
    }
}
