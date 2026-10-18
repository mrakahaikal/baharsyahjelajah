<?php

declare(strict_types=1);

namespace App\Mcp\Tools\VisaServices;

use App\Enums\VisaEntryType;
use App\Enums\VisaItemType;
use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Country;
use App\Models\VisaService;
use App\Models\VisaServiceItem;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('update_visa_service')]
#[Title('Update Visa Service')]
#[Description('Update an existing visa service details, pricing, processing durations, or sync its checklist items.')]
class UpdateVisaServiceTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The numeric ID of the visa service to update.'),
            'slug' => $schema->string()->description('The URL slug of the visa service to update (used if "id" is omitted).'),
            'country_id' => $schema->integer()->description('New country ID if changing country.'),
            'country_slug' => $schema->string()->description('New country slug if changing country.'),
            'name' => $schema->mixed()->description('New title. Single string or multilingual map.'),
            'new_slug' => $schema->string()->description('New URL slug for the visa service.'),
            'visa_type' => $schema->mixed()->description('New visa type. Single string or multilingual map.'),
            'summary' => $schema->mixed()->description('New summary highlight. Single string or multilingual map.'),
            'description' => $schema->mixed()->description('New detailed description. Single string or multilingual map.'),
            'entry_type' => $schema->string()->description('New entry type: "single" or "multiple".'),
            'processing_days_min' => $schema->integer()->description('Minimum estimated processing duration in business days.'),
            'processing_days_max' => $schema->integer()->description('Maximum estimated processing duration in business days.'),
            'validity_days' => $schema->integer()->description('Visa validity duration in days.'),
            'maximum_stay_days' => $schema->integer()->description('Maximum permitted duration of stay per visit in days.'),
            'currency' => $schema->string()->description('Currency code (e.g. "SAR", "USD", "IDR").'),
            'price' => $schema->number()->description('Official fee in the specified currency.'),
            'price_idr' => $schema->integer()->description('Manual override for the price in IDR.'),
            'is_active' => $schema->boolean()->description('Publish status.'),
            'is_featured' => $schema->boolean()->description('Feature on home/landing page.'),
            'sort_order' => $schema->integer()->description('Display sorting weight.'),
            'items' => $schema->array()->description('If provided, replaces existing checklist items with this list. Each item: {type: "requirement"|"term"|"included"|"excluded"|"note", content: string|map, details: string|map, is_mandatory: boolean, sort_order: integer}.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $slug = $request->get('slug');

        if (empty($id) && empty($slug)) {
            return Response::error('Either "id" or "slug" must be provided.');
        }

        /** @var VisaService|null $service */
        $service = ! empty($id)
            ? VisaService::find((int) $id)
            : VisaService::where('slug', (string) $slug)->first();

        if (! $service) {
            $identifier = ! empty($id) ? "ID {$id}" : "slug '{$slug}'";

            return Response::error("Visa service with {$identifier} not found.");
        }

        $attributesToUpdate = [];

        // 1. Country Change
        if ($request->has('country_id') || $request->has('country_slug')) {
            $newCountryId = $request->get('country_id');
            $newCountrySlug = $request->get('country_slug');

            /** @var Country|null $country */
            $country = ! empty($newCountryId)
                ? Country::find((int) $newCountryId)
                : Country::where('slug', (string) $newCountrySlug)->first();

            if (! $country) {
                return Response::error('Specified target country was not found.');
            }

            $attributesToUpdate['country_id'] = $country->id;
        }

        // 2. Multilingual Name
        if ($request->has('name')) {
            $rawName = $request->get('name');
            if (empty($rawName)) {
                return Response::error('Visa service name cannot be empty.');
            }
            $currentName = $service->getTranslations('name');
            $attributesToUpdate['name'] = $this->mergeTranslations($currentName, $rawName);
        }

        // 3. New Slug
        if ($request->has('new_slug')) {
            $newSlug = Str::slug(trim((string) $request->get('new_slug')));
            if (empty($newSlug)) {
                return Response::error('New slug cannot be empty.');
            }
            $conflict = VisaService::query()
                ->where('slug', $newSlug)
                ->where('id', '!=', $service->id)
                ->exists();
            if ($conflict) {
                return Response::error("A visa service with slug '{$newSlug}' already exists.");
            }
            $attributesToUpdate['slug'] = $newSlug;
        }

        // 4. Visa Type
        if ($request->has('visa_type')) {
            $rawType = $request->get('visa_type');
            if (empty($rawType)) {
                return Response::error('Visa type cannot be empty.');
            }
            $currentType = $service->getTranslations('visa_type');
            $attributesToUpdate['visa_type'] = $this->mergeTranslations($currentType, $rawType);
        }

        // 5. Summary & Description
        if ($request->has('summary')) {
            $currentSummary = $service->getTranslations('summary');
            $attributesToUpdate['summary'] = $this->mergeTranslations($currentSummary, $request->get('summary'));
        }

        if ($request->has('description')) {
            $currentDesc = $service->getTranslations('description');
            $attributesToUpdate['description'] = $this->mergeTranslations($currentDesc, $request->get('description'));
        }

        // 6. Entry Type
        if ($request->has('entry_type')) {
            $val = $request->get('entry_type');
            if (empty($val)) {
                $attributesToUpdate['entry_type'] = null;
            } else {
                $entryType = VisaEntryType::tryFrom((string) $val);
                if (! $entryType) {
                    return Response::error("Invalid entry_type '{$val}'. Allowed: 'single', 'multiple'.");
                }
                $attributesToUpdate['entry_type'] = $entryType;
            }
        }

        // 7. Durations
        foreach (['processing_days_min', 'processing_days_max', 'validity_days', 'maximum_stay_days'] as $field) {
            if ($request->has($field)) {
                $attributesToUpdate[$field] = $request->get($field) !== null ? (int) $request->get($field) : null;
            }
        }

        // 8. Pricing
        if ($request->has('currency')) {
            $attributesToUpdate['currency'] = filled($request->get('currency'))
                ? Str::upper(trim((string) $request->get('currency')))
                : 'IDR';
        }

        if ($request->has('price')) {
            $attributesToUpdate['price'] = $request->get('price') !== null ? (float) $request->get('price') : null;
        }

        if ($request->has('price_idr')) {
            $attributesToUpdate['price_idr'] = $request->get('price_idr') !== null ? (int) $request->get('price_idr') : null;
        }

        // 9. Status & sort
        if ($request->has('is_active')) {
            $attributesToUpdate['is_active'] = (bool) $request->get('is_active');
        }

        if ($request->has('is_featured')) {
            $attributesToUpdate['is_featured'] = (bool) $request->get('is_featured');
        }

        if ($request->has('sort_order')) {
            $attributesToUpdate['sort_order'] = (int) $request->get('sort_order');
        }

        $rawItems = $request->has('items') ? $request->get('items') : null;

        DB::transaction(function () use ($service, $attributesToUpdate, $rawItems): void {
            if (! empty($attributesToUpdate)) {
                $service->update($attributesToUpdate);
            }

            if (is_array($rawItems)) {
                // Delete existing items and rebuild
                $service->items()->delete();

                $order = 0;
                foreach ($rawItems as $rawItem) {
                    if (! is_array($rawItem) || empty($rawItem['content'])) {
                        continue;
                    }

                    $typeVal = (string) ($rawItem['type'] ?? 'requirement');
                    $itemType = VisaItemType::tryFrom($typeVal) ?? VisaItemType::Requirement;

                    $content = $this->normalizeMultilingualInput($rawItem['content']);
                    $details = ! empty($rawItem['details']) ? $this->normalizeMultilingualInput($rawItem['details']) : null;
                    $isMandatory = isset($rawItem['is_mandatory']) ? (bool) $rawItem['is_mandatory'] : true;
                    $sortOrder = isset($rawItem['sort_order']) ? (int) $rawItem['sort_order'] : $order++;

                    VisaServiceItem::create([
                        'visa_service_id' => $service->id,
                        'type' => $itemType,
                        'content' => $content,
                        'details' => $details,
                        'is_mandatory' => $isMandatory,
                        'sort_order' => $sortOrder,
                    ]);
                }
            }
        });

        $service->refresh();
        $service->load(['country', 'items']);
        $serviceName = $service->getTranslation('name', 'id', false);

        return Response::json([
            'message' => "Visa service '{$serviceName}' updated successfully.",
            'service' => [
                'id' => $service->id,
                'name' => $serviceName,
                'slug' => $service->slug,
                'country' => $service->country?->getTranslation('name', 'id', false),
                'visa_type' => $service->getTranslation('visa_type', 'id', false),
                'entry_type' => $service->entry_type?->value,
                'pricing' => [
                    'currency' => $service->currency,
                    'price' => (float) $service->price,
                    'price_idr' => $service->price_idr,
                ],
                'items_count' => $service->items->count(),
                'is_active' => $service->is_active,
                'is_featured' => $service->is_featured,
                'updated_at' => $service->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Merge current translations with input.
     *
     * @param  array<string, mixed>  $current
     * @return array<string, string>
     */
    protected function mergeTranslations(array $current, mixed $input): array
    {
        if (is_string($input)) {
            $current['id'] = trim($input);

            return $current;
        }

        if (is_array($input)) {
            foreach ($input as $loc => $val) {
                if (filled($val)) {
                    $current[(string) $loc] = trim((string) $val);
                }
            }
        }

        return $current;
    }
}
