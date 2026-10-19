<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Tours;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\TourPackage;
use App\Models\TourPackageInclude;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('manage_tour_includes')]
#[Title('Manage Tour Package Inclusions & Exclusions')]
#[Description('Create, update, bulk-sync, or delete inclusions (include), exclusions (exclude), and notes (note) for a tour package.')]
class ManageTourIncludesTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()->description('Operation to perform: "upsert" (create or update single item), "sync" (replace all items for this package), or "delete" (delete single item by include_id). Default: "upsert".'),
            'tour_package_id' => $schema->integer()->description('Numeric ID of the tour package.')->required(),
            'include_id' => $schema->integer()->description('Numeric ID of an existing include/exclude item (required for "delete", optional for "upsert").'),
            'type' => $schema->string()->description('Item type: "include", "exclude", or "note". Default: "include".'),
            'item' => $schema->mixed()->description('Facility or note text (plain string or multilingual map). Required for "upsert".'),
            'sort_order' => $schema->integer()->description('Display sort order (default: 0).'),
            'items' => $schema->array()->description('Array of items when action is "sync": [{"type": "include", "item": "Tiket Pesawat PP", "sort_order": 1}].'),
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
            $includeId = $request->get('include_id');
            if (empty($includeId)) {
                return Response::error('The "include_id" parameter is required when action is "delete".');
            }

            $include = TourPackageInclude::where('tour_package_id', $package->id)->find((int) $includeId);
            if (! $include) {
                return Response::error("Include item with ID {$includeId} not found in tour package ID {$package->id}.");
            }

            $include->delete();

            return Response::json([
                'message' => 'Item fasilitas/layanan paket tur berhasil dihapus.',
                'deleted_include_id' => (int) $includeId,
            ]);
        }

        if ($action === 'sync') {
            $rawItems = $request->get('items');
            if (! is_array($rawItems) || empty($rawItems)) {
                return Response::error('The "items" array parameter is required when action is "sync".');
            }

            $prepared = [];
            foreach ($rawItems as $index => $row) {
                if (! is_array($row) || empty($row['item'])) {
                    return Response::error("Invalid or missing 'item' at index {$index}.");
                }
                $type = strtolower(trim((string) ($row['type'] ?? 'include')));
                if (! in_array($type, ['include', 'exclude', 'note'], true)) {
                    return Response::error("Invalid type '{$type}' at index {$index}. Allowed values: 'include', 'exclude', 'note'.");
                }

                $prepared[] = [
                    'type' => $type,
                    'item' => $this->normalizeMultilingualInput($row['item']),
                    'sort_order' => max(0, (int) ($row['sort_order'] ?? $index)),
                ];
            }

            DB::transaction(function () use ($package, $prepared): void {
                $package->includes()->delete();
                foreach ($prepared as $row) {
                    $package->includes()->create($row);
                }
            });

            $package->load('includes');

            return Response::json([
                'message' => 'Daftar fasilitas paket tur berhasil disinkronisasi.',
                'includes' => $package->includes->map(fn (TourPackageInclude $inc): array => [
                    'id' => $inc->id,
                    'type' => $inc->type,
                    'item' => $inc->getTranslations('item'),
                    'sort_order' => $inc->sort_order,
                ])->all(),
            ]);
        }

        // Action === 'upsert'
        $includeId = $request->get('include_id');
        $include = null;

        if (! empty($includeId)) {
            $include = TourPackageInclude::where('tour_package_id', $package->id)->find((int) $includeId);
            if (! $include) {
                return Response::error("Include item with ID {$includeId} not found in tour package ID {$package->id}.");
            }
        }

        if (! $include && empty($request->get('item'))) {
            return Response::error('The "item" parameter is required when creating a new facility item.');
        }

        $type = strtolower(trim((string) $request->get('type', $include?->type ?? 'include')));
        if (! in_array($type, ['include', 'exclude', 'note'], true)) {
            return Response::error("Invalid type '{$type}'. Allowed values: 'include', 'exclude', 'note'.");
        }

        if (! $include) {
            $include = new TourPackageInclude(['tour_package_id' => $package->id]);
        }

        $include->type = $type;

        if ($request->has('item') && ! empty($request->get('item'))) {
            $rawItem = $request->get('item');
            $existing = $include->exists ? $include->getTranslations('item') : [];
            $incoming = is_array($rawItem) ? $rawItem : $this->normalizeMultilingualInput($rawItem);
            $include->setTranslations('item', array_merge($existing, array_filter($incoming, fn ($v) => $v !== null && $v !== '')));
        }

        if ($request->has('sort_order')) {
            $include->sort_order = max(0, (int) $request->get('sort_order'));
        } elseif (! $include->exists) {
            $include->sort_order = 0;
        }

        $include->save();

        return Response::json([
            'message' => 'Item fasilitas/layanan paket tur berhasil disimpan.',
            'include' => [
                'id' => $include->id,
                'tour_package_id' => $include->tour_package_id,
                'type' => $include->type,
                'item' => $include->getTranslations('item'),
                'sort_order' => $include->sort_order,
            ],
        ]);
    }
}
