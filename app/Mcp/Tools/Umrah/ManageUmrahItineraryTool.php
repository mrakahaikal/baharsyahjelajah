<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Umrah;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\UmrahPackage;
use App\Models\UmrahPackageItinerary;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('manage_umrah_itinerary')]
#[Title('Manage Umrah Itinerary')]
#[Description('Create, update, bulk-sync, or delete daily pilgrimage itinerary items (Makkah, Madinah, Ziarah) for an Umrah package.')]
class ManageUmrahItineraryTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()->description('Operation to perform: "upsert" (create/update single day), "sync" (replace all itinerary days), or "delete" (remove day). Default: "upsert".'),
            'umrah_package_id' => $schema->integer()->description('Numeric ID of the Umrah package.')->required(),
            'day_number' => $schema->integer()->description('Day number (e.g., 1, 2, 3). Required for "upsert" and "delete".'),
            'title' => $schema->mixed()->description('Agenda title for the day (plain string or multilingual map). Required for "upsert".'),
            'location' => $schema->mixed()->description('City or landmark location (plain string or multilingual map, e.g. "Madinah Al-Munawwarah", "Makkah Al-Mukarramah").'),
            'description' => $schema->mixed()->description('Detailed daily agenda description (plain text, Markdown, or HTML; auto-converted to semantic HTML).'),
            'itineraries' => $schema->array()->description('Array of itinerary items when action is "sync": [{"day_number": 1, "title": "...", "location": "...", "description": "..."}].'),
        ];
    }

    public function handle(Request $request): Response
    {
        $rawAction = strtolower(trim((string) $request->get('action', 'upsert')));
        $action = match ($rawAction) {
            'add', 'create', 'upsert' => 'upsert',
            'sync' => 'sync',
            'delete' => 'delete',
            default => $rawAction,
        };

        if (! in_array($action, ['upsert', 'sync', 'delete'], true)) {
            return Response::error("Invalid action '{$rawAction}'. Allowed values: upsert (or add), sync, delete.");
        }

        if ($action === 'delete') {
            $itinerary = null;
            if ($request->filled('id')) {
                $itinerary = UmrahPackageItinerary::find((int) $request->get('id'));
            } else {
                $packageId = $request->get('umrah_package_id') ?? $request->get('package_id');
                if (empty($packageId)) {
                    return Response::error('The "umrah_package_id" parameter is required when deleting by day_number.');
                }

                $dayNumber = (int) $request->get('day_number', 0);
                if ($dayNumber < 1) {
                    return Response::error('The "day_number" or "id" parameter is required when action is "delete".');
                }

                $itinerary = UmrahPackageItinerary::where('umrah_package_id', (int) $packageId)
                    ->where('day_number', $dayNumber)
                    ->first();
            }

            if (! $itinerary) {
                return Response::error('Itinerary not found.');
            }

            $deletedDay = $itinerary->day_number;
            $itinerary->delete();

            return Response::json([
                'message' => "Item itinerary umrah hari ke-{$deletedDay} berhasil dihapus.",
                'deleted_id' => $itinerary->id,
                'deleted_day_number' => $deletedDay,
            ]);
        }

        $packageId = $request->get('umrah_package_id') ?? $request->get('package_id');
        if (empty($packageId)) {
            return Response::error('The "umrah_package_id" parameter is required.');
        }

        $package = UmrahPackage::find((int) $packageId);
        if (! $package) {
            return Response::error("Umrah package with ID {$packageId} not found.");
        }

        if ($action === 'sync') {
            $rawItineraries = $request->get('itineraries') ?? $request->get('items');
            if (! is_array($rawItineraries) || empty($rawItineraries)) {
                return Response::error('The "itineraries" array parameter is required when action is "sync".');
            }

            $prepared = [];
            foreach ($rawItineraries as $index => $item) {
                if (! is_array($item)) {
                    return Response::error("Invalid itinerary item at index {$index}.");
                }
                $dayNum = (int) ($item['day_number'] ?? ($index + 1));
                if ($dayNum < 1) {
                    return Response::error("Invalid day_number at index {$index}: must be at least 1.");
                }
                if (empty($item['title'])) {
                    return Response::error("Missing title at index {$index} (day {$dayNum}).");
                }

                $prepared[] = [
                    'day_number' => $dayNum,
                    'title' => $this->normalizeMultilingualInput($item['title']),
                    'location' => ! empty($item['location']) ? $this->normalizeMultilingualInput($item['location']) : ['id' => '', 'en' => '', 'ms' => ''],
                    'description' => ! empty($item['description']) ? $this->normalizeRichTextMultilingual($item['description']) : ['id' => '', 'en' => '', 'ms' => ''],
                ];
            }

            DB::transaction(function () use ($package, $prepared): void {
                $package->itineraries()->delete();
                foreach ($prepared as $row) {
                    $package->itineraries()->create($row);
                }
            });

            $package->load('itineraries');

            return Response::json([
                'message' => 'Itinerary umrah berhasil disinkronkan.',
                'itineraries' => $package->itineraries->map(fn (UmrahPackageItinerary $it): array => [
                    'id' => $it->id,
                    'day_number' => (int) $it->day_number,
                    'title' => $it->getTranslations('title'),
                    'location' => $it->getTranslations('location'),
                    'description' => $it->getTranslations('description'),
                ])->all(),
            ]);
        }

        // Action === 'upsert'
        $dayNumber = (int) $request->get('day_number', 0);
        if ($dayNumber < 1) {
            return Response::error('The "day_number" parameter is required and must be at least 1.');
        }

        $itinerary = UmrahPackageItinerary::firstOrNew([
            'umrah_package_id' => $package->id,
            'day_number' => $dayNumber,
        ]);

        if (! $itinerary->exists && empty($request->get('title'))) {
            return Response::error('The "title" parameter is required when creating a new itinerary day.');
        }

        if ($request->has('title') && ! empty($request->get('title'))) {
            $rawTitle = $request->get('title');
            $existing = $itinerary->exists ? $itinerary->getTranslations('title') : [];
            $incoming = is_array($rawTitle) ? $rawTitle : $this->normalizeMultilingualInput($rawTitle);
            $itinerary->setTranslations('title', array_merge($existing, array_filter($incoming, fn ($v) => $v !== null && $v !== '')));
        }

        if ($request->has('location')) {
            $rawLoc = $request->get('location');
            $existing = $itinerary->exists ? $itinerary->getTranslations('location') : [];
            $incoming = is_array($rawLoc) ? $rawLoc : $this->normalizeMultilingualInput($rawLoc);
            $itinerary->setTranslations('location', array_merge($existing, array_filter($incoming, fn ($v) => $v !== null && $v !== '')));
        }

        if ($request->has('description') && ! empty($request->get('description'))) {
            $rawDesc = $request->get('description');
            $existing = $itinerary->exists ? $itinerary->getTranslations('description') : [];
            $incoming = is_array($rawDesc)
                ? array_map(fn ($v) => $this->ensureRichTextHtml((string) $v), $rawDesc)
                : $this->normalizeRichTextMultilingual($rawDesc);
            $itinerary->setTranslations('description', array_merge($existing, array_filter($incoming, fn ($v) => $v !== '')));
        }

        $itinerary->save();

        return Response::json([
            'message' => "Itinerary hari ke-{$dayNumber} berhasil disimpan.",
            'itinerary' => [
                'id' => $itinerary->id,
                'day_number' => (int) $itinerary->day_number,
                'title' => $itinerary->getTranslations('title'),
                'location' => $itinerary->getTranslations('location'),
                'description' => $itinerary->getTranslations('description'),
            ],
        ]);
    }
}
