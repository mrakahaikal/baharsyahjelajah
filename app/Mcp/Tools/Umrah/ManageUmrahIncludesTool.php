<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Umrah;

use App\Enums\UmrahItemType;
use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\UmrahInclude;
use App\Models\UmrahPackage;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('manage_umrah_includes')]
#[Title('Manage Umrah Inclusions, Exclusions & Requirements')]
#[Description('Create, update, bulk-sync, or delete facilities (include/exclude), registration requirements, and notes for an Umrah package.')]
class ManageUmrahIncludesTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()->description('Operation to perform: "upsert" (create/update single item), "sync" (replace all items), or "delete" (remove item). Default: "upsert".'),
            'umrah_package_id' => $schema->integer()->description('Numeric ID of the Umrah package.')->required(),
            'include_id' => $schema->integer()->description('Numeric ID of the specific include item (required for "delete", optional for "upsert").'),
            'type' => $schema->string()->description('Item type: "include" (fasilitas termasuk), "exclude" (tidak termasuk), "requirement" (persyaratan dokumen/vaksin), or "note" (catatan penting). Default: "include".'),
            'item' => $schema->union(['string', 'object'])->description('Item description (plain string or multilingual map). Required for "upsert".'),
            'sort_order' => $schema->integer()->description('Display sort order (default: 0).'),
            'items' => $schema->array()->description('Array of items when action is "sync": [{"type": "include", "item": "Tiket Pesawat PP", "sort_order": 1}].'),
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
            $includeId = $request->get('include_id') ?? $request->get('id');
            if (empty($includeId)) {
                return Response::error('The "include_id" parameter is required when action is "delete".');
            }

            $include = UmrahInclude::find((int) $includeId);
            if (! $include) {
                return Response::error("Include item with ID {$includeId} not found.");
            }

            $include->delete();

            return Response::json([
                'message' => 'Item fasilitas/persyaratan umrah berhasil dihapus.',
                'deleted_include_id' => (int) $includeId,
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
            $rawItems = $request->get('items');
            if (! is_array($rawItems) || empty($rawItems)) {
                return Response::error('The "items" array parameter is required when action is "sync".');
            }

            $defaultType = strtolower(trim((string) $request->get('type', 'include')));
            $prepared = [];
            foreach ($rawItems as $index => $row) {
                if (is_string($row)) {
                    $itemVal = $row;
                    $itemType = $defaultType;
                    $order = $index;
                } elseif (is_array($row)) {
                    $itemVal = $row['item'] ?? $row['description'] ?? null;
                    $itemType = strtolower(trim((string) ($row['type'] ?? $defaultType)));
                    $order = max(0, (int) ($row['sort_order'] ?? $index));
                } else {
                    return Response::error("Invalid item at index {$index}.");
                }

                if (empty($itemVal)) {
                    return Response::error("Missing item description at index {$index}.");
                }

                if (UmrahItemType::tryFrom($itemType) === null) {
                    return Response::error("Invalid type '{$itemType}' at index {$index}. Allowed: include, exclude, requirement, note.");
                }

                $prepared[] = [
                    'type' => $itemType,
                    'item' => $this->normalizeMultilingualInput($itemVal),
                    'sort_order' => $order,
                ];
            }

            DB::transaction(function () use ($package, $prepared, $request): void {
                if ($request->filled('type')) {
                    $targetType = strtolower(trim((string) $request->get('type')));
                    $package->includes()->where('type', $targetType)->delete();
                } else {
                    $package->includes()->delete();
                }

                foreach ($prepared as $row) {
                    $package->includes()->create($row);
                }
            });

            $package->load('includes');

            return Response::json([
                'message' => 'Daftar fasilitas/ketentuan umrah berhasil disinkronkan.',
                'total_items' => $package->includes->count(),
                'includes' => $package->includes->map(fn (UmrahInclude $inc): array => [
                    'id' => $inc->id,
                    'type' => $inc->type,
                    'item' => $inc->getTranslations('item'),
                    'sort_order' => (int) $inc->sort_order,
                ])->all(),
            ]);
        }

        // Action === 'upsert'
        $includeId = $request->get('include_id') ?? $request->get('id');
        $include = null;

        if (! empty($includeId)) {
            $include = UmrahInclude::where('package_id', $package->id)->find((int) $includeId);
            if (! $include) {
                return Response::error("Include item with ID {$includeId} not found in Umrah package ID {$package->id}.");
            }
        }

        $incomingItem = $request->get('item') ?? $request->get('description');
        if (! $include && empty($incomingItem)) {
            return Response::error('The "item" parameter is required when creating a new include item.');
        }

        $type = strtolower(trim((string) $request->get('type', $include?->type ?? 'include')));
        if (UmrahItemType::tryFrom($type) === null) {
            return Response::error("Invalid type '{$type}'. Allowed: include, exclude, requirement, note.");
        }

        if (! $include) {
            $include = new UmrahInclude(['package_id' => $package->id]);
        }

        $include->type = $type;

        if (! empty($incomingItem)) {
            $existing = $include->exists ? $include->getTranslations('item') : [];
            $incoming = is_array($incomingItem) ? $incomingItem : $this->normalizeMultilingualInput($incomingItem);
            $include->setTranslations('item', array_merge($existing, array_filter($incoming, fn ($v) => $v !== null && $v !== '')));
        }

        if ($request->has('sort_order')) {
            $include->sort_order = max(0, (int) $request->get('sort_order'));
        } elseif (! $include->exists) {
            $include->sort_order = 0;
        }

        $include->save();

        return Response::json([
            'message' => 'Item fasilitas/persyaratan umrah berhasil disimpan.',
            'include' => [
                'id' => $include->id,
                'package_id' => $include->package_id,
                'type' => $include->type,
                'item' => $include->getTranslations('item'),
                'sort_order' => (int) $include->sort_order,
            ],
        ]);
    }
}
