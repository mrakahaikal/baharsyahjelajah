<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Tours;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Destination;
use App\Models\TourPackage;
use App\Models\TourPackageItinerary;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('manage_tour_itinerary')]
#[Title('Manage Tour Package Itinerary')]
#[Description('Create, update, bulk-replace, or delete daily itinerary items and their visited destinations for a tour package.')]
class ManageTourItineraryTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()->description('Operation to perform: "upsert" (create or update a single day by day_number), "sync" (replace all itinerary days with the itineraries array), or "delete" (remove a specific day). Default: "upsert".'),
            'tour_package_id' => $schema->integer()->description('Numeric ID of the tour package.')->required(),
            'day_number' => $schema->integer()->description('Day number (e.g., 1, 2, 3). Required for "upsert" and "delete".'),
            'title' => $schema->mixed()->description('Activity title for the day (plain string or multilingual map). Required for "upsert".'),
            'description' => $schema->mixed()->description('Detailed schedule/description for the day (plain text, Markdown, or HTML; auto-converted to semantic HTML).'),
            'destination_ids' => $schema->array()->description('Optional array of numeric destination IDs visited on this day.'),
            'itineraries' => $schema->array()->description('Array of daily itinerary items when action is "sync": [{"day_number": 1, "title": "...", "description": "...", "destination_ids": [1, 2]}].'),
        ];
    }

    public function handle(Request $request): Response
    {
        $action = strtolower(trim((string) $request->get('action', 'upsert')));
        if (! in_array($action, ['upsert', 'sync', 'delete'], true)) {
            return Response::error("Invalid action '{$action}'. Allowed values: 'upsert', 'sync', 'delete'.");
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
            $dayNumber = (int) $request->get('day_number', 0);
            if ($dayNumber < 1) {
                return Response::error('The "day_number" parameter is required and must be at least 1 when action is "delete".');
            }

            $itinerary = TourPackageItinerary::where('tour_package_id', $package->id)
                ->where('day_number', $dayNumber)
                ->first();

            if (! $itinerary) {
                return Response::error("Itinerary for day {$dayNumber} not found in tour package ID {$package->id}.");
            }

            $itinerary->delete();

            return Response::json([
                'message' => "Itinerary hari ke-{$dayNumber} berhasil dihapus.",
                'deleted_day_number' => $dayNumber,
            ]);
        }

        if ($action === 'sync') {
            $rawItineraries = $request->get('itineraries');
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

                $destIds = [];
                if (isset($item['destination_ids']) && is_array($item['destination_ids'])) {
                    $destIds = array_values(array_unique(array_map('intval', $item['destination_ids'])));
                    if (! empty($destIds) && Destination::whereIn('id', $destIds)->count() !== count($destIds)) {
                        return Response::error("One or more destination_ids at index {$index} do not exist.");
                    }
                }

                $prepared[] = [
                    'day_number' => $dayNum,
                    'title' => $this->normalizeMultilingualInput($item['title']),
                    'description' => ! empty($item['description'])
                        ? $this->normalizeRichTextMultilingual($item['description'])
                        : ['id' => '', 'en' => '', 'ms' => ''],
                    'destination_ids' => $destIds,
                ];
            }

            DB::transaction(function () use ($package, $prepared): void {
                $package->itineraries()->each(fn (TourPackageItinerary $it) => $it->delete());

                foreach ($prepared as $row) {
                    /** @var TourPackageItinerary $created */
                    $created = $package->itineraries()->create([
                        'day_number' => $row['day_number'],
                        'title' => $row['title'],
                        'description' => $row['description'],
                    ]);

                    if (! empty($row['destination_ids'])) {
                        $created->destinations()->sync($row['destination_ids']);
                    }
                }
            });

            $package->load('itineraries.destinations');

            return Response::json([
                'message' => 'Seluruh jadwal itinerary paket tur berhasil disinkronisasi.',
                'itineraries' => $package->itineraries->map(fn (TourPackageItinerary $it): array => [
                    'id' => $it->id,
                    'day_number' => $it->day_number,
                    'title' => $it->getTranslations('title'),
                    'description' => $it->getTranslations('description'),
                    'destinations' => $it->destinations->map(fn (Destination $d): array => [
                        'id' => $d->id,
                        'name' => $d->getTranslation('name', 'id', false),
                    ])->all(),
                ])->all(),
            ]);
        }

        // Action === 'upsert'
        $dayNumber = (int) $request->get('day_number', 0);
        if ($dayNumber < 1) {
            return Response::error('The "day_number" parameter is required and must be at least 1.');
        }

        $existingItinerary = TourPackageItinerary::where('tour_package_id', $package->id)
            ->where('day_number', $dayNumber)
            ->first();

        if (! $existingItinerary && empty($request->get('title'))) {
            return Response::error('The "title" parameter is required when creating a new itinerary day.');
        }

        $destinationIds = null;
        if ($request->has('destination_ids') && is_array($request->get('destination_ids'))) {
            $rawDestIds = array_values(array_unique(array_map('intval', $request->get('destination_ids'))));
            if (! empty($rawDestIds) && Destination::whereIn('id', $rawDestIds)->count() !== count($rawDestIds)) {
                return Response::error('One or more provided destination_ids do not exist.');
            }
            $destinationIds = $rawDestIds;
        }

        $itinerary = $existingItinerary ?? new TourPackageItinerary([
            'tour_package_id' => $package->id,
            'day_number' => $dayNumber,
        ]);

        if ($request->has('title') && ! empty($request->get('title'))) {
            $rawTitle = $request->get('title');
            $existingTitle = $itinerary->exists ? $itinerary->getTranslations('title') : [];
            $incomingTitle = is_array($rawTitle) ? $rawTitle : $this->normalizeMultilingualInput($rawTitle);
            $itinerary->setTranslations('title', array_merge($existingTitle, array_filter($incomingTitle, fn ($v) => $v !== null && $v !== '')));
        }

        if ($request->has('description') && ! empty($request->get('description'))) {
            $rawDesc = $request->get('description');
            $existingDesc = $itinerary->exists ? $itinerary->getTranslations('description') : [];
            $incomingDesc = is_array($rawDesc)
                ? array_map(fn ($v) => $this->ensureRichTextHtml((string) $v), $rawDesc)
                : $this->normalizeRichTextMultilingual($rawDesc);
            $itinerary->setTranslations('description', array_merge($existingDesc, array_filter($incomingDesc, fn ($v) => $v !== '')));
        }

        $itinerary->save();

        if ($destinationIds !== null) {
            $itinerary->destinations()->sync($destinationIds);
        }

        $itinerary->load('destinations');

        return Response::json([
            'message' => "Itinerary hari ke-{$dayNumber} berhasil disimpan.",
            'itinerary' => [
                'id' => $itinerary->id,
                'tour_package_id' => $itinerary->tour_package_id,
                'day_number' => $itinerary->day_number,
                'title' => $itinerary->getTranslations('title'),
                'description' => $itinerary->getTranslations('description'),
                'destinations' => $itinerary->destinations->map(fn (Destination $d): array => [
                    'id' => $d->id,
                    'name' => $d->getTranslation('name', 'id', false),
                ])->all(),
            ],
        ]);
    }
}
