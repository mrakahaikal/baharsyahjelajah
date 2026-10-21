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

#[Name('create_visa_service')]
#[Title('Create Visa Service')]
#[Description('Create a new visa service with pricing, processing durations, country association, and optional checklist items (requirements, terms, inclusions) in a single atomic transaction.')]
class CreateVisaServiceTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'country_id' => $schema->integer()->description('The numeric ID of the country.'),
            'country_slug' => $schema->string()->description('The URL slug of the country (used if country_id is omitted).'),
            'country_iso' => $schema->string()->description('The 2-letter or 3-letter ISO code of the country (e.g. "SA", "JPN", "TUR").'),
            'name' => $schema->union(['string', 'object'])->description('The visa service title. Accepts a single string or a multilingual map {"id": "...", "en": "...", "ms": "..."}. Required.'),
            'slug' => $schema->string()->description('The unique URL slug for the visa service. Auto-generated if omitted.'),
            'visa_type' => $schema->union(['string', 'object'])->description('The type/category of visa (e.g. "Tourist eVisa", "Visa Umrah", "Business Visa"). Single string or multilingual map. Required.'),
            'summary' => $schema->union(['string', 'object'])->description('Short highlight or summary. Single string or multilingual map.'),
            'description' => $schema->union(['string', 'object'])->description('Comprehensive explanation or RichText content. Single string or multilingual map.'),
            'entry_type' => $schema->string()->description('Entry validity type: "single" or "multiple".'),
            'processing_days_min' => $schema->integer()->description('Minimum estimated processing duration in business days.'),
            'processing_days_max' => $schema->integer()->description('Maximum estimated processing duration in business days.'),
            'validity_days' => $schema->integer()->description('Visa validity duration in days (e.g. 90, 365).'),
            'maximum_stay_days' => $schema->integer()->description('Maximum permitted duration of stay per visit in days (e.g. 30, 90).'),
            'currency' => $schema->string()->description('Currency code of the price (e.g. "SAR", "USD", "IDR"). Default is "IDR".'),
            'price' => $schema->number()->description('Official fee in the specified currency. Converted automatically to IDR on save if foreign.'),
            'price_idr' => $schema->integer()->description('Optional manual override for the price in IDR.'),
            'is_active' => $schema->boolean()->description('Publish status. Default is true.'),
            'is_featured' => $schema->boolean()->description('Feature on home/landing page. Default is false.'),
            'sort_order' => $schema->integer()->description('Display sorting weight. Default is 0.'),
            'items' => $schema->array()->description('List of checklist/terms items to attach. Each item: {type: "requirement"|"term"|"included"|"excluded"|"note", content: string|map, details: string|map, is_mandatory: boolean, sort_order: integer}.'),
        ];
    }

    public function handle(Request $request): Response
    {
        // 1. Resolve Country
        $countryId = $request->get('country_id');
        $countrySlug = $request->get('country_slug');
        $countryIso = $request->get('country_iso');

        /** @var Country|null $country */
        if (! empty($countryId)) {
            $country = Country::find((int) $countryId);
        } elseif (! empty($countrySlug)) {
            $country = Country::where('slug', (string) $countrySlug)->first();
        } elseif (! empty($countryIso)) {
            $code = Str::upper(trim((string) $countryIso));
            $country = Country::where('iso_alpha_2', $code)->orWhere('iso_alpha_3', $code)->first();
        } else {
            return Response::error('A target country must be specified via "country_id", "country_slug", or "country_iso".');
        }

        if (! $country) {
            return Response::error('Specified country was not found.');
        }

        // 2. Validate and normalize name
        $rawName = $request->get('name');
        if (empty($rawName)) {
            return Response::error('The "name" parameter is required.');
        }
        $name = $this->normalizeMultilingualInput($rawName);
        if (empty($name['id'])) {
            return Response::error('Visa service name for Indonesian (id) cannot be empty.');
        }

        // 3. Validate and normalize visa_type
        $rawType = $request->get('visa_type');
        if (empty($rawType)) {
            return Response::error('The "visa_type" parameter is required.');
        }
        $visaType = $this->normalizeMultilingualInput($rawType);

        // 4. Resolve slug
        $rawSlug = $request->get('slug');
        $slug = filled($rawSlug)
            ? Str::slug((string) $rawSlug)
            : Str::slug($name['id']);

        if (empty($slug)) {
            $slug = 'visa-'.Str::random(8);
        }

        if (VisaService::query()->where('slug', $slug)->exists()) {
            return Response::error("Visa service with slug '{$slug}' already exists.");
        }

        // 5. Entry Type
        $entryType = null;
        if ($request->has('entry_type') && filled($request->get('entry_type'))) {
            $val = (string) $request->get('entry_type');
            $entryType = VisaEntryType::tryFrom($val);
            if (! $entryType) {
                return Response::error("Invalid entry_type '{$val}'. Allowed: 'single', 'multiple'.");
            }
        }

        // 6. Multilingual summary & description
        $summary = filled($request->get('summary')) ? $this->normalizeMultilingualInput($request->get('summary')) : null;
        $description = filled($request->get('description')) ? $this->normalizeMultilingualInput($request->get('description')) : null;

        // 7. Pricing
        $currency = filled($request->get('currency')) ? Str::upper(trim((string) $request->get('currency'))) : 'IDR';
        $price = $request->has('price') && $request->get('price') !== null ? (float) $request->get('price') : null;
        $priceIdr = $request->has('price_idr') && $request->get('price_idr') !== null ? (int) $request->get('price_idr') : null;

        $serviceData = [
            'country_id' => $country->id,
            'name' => $name,
            'slug' => $slug,
            'visa_type' => $visaType,
            'summary' => $summary,
            'description' => $description,
            'entry_type' => $entryType,
            'processing_days_min' => $request->has('processing_days_min') ? (int) $request->get('processing_days_min') : null,
            'processing_days_max' => $request->has('processing_days_max') ? (int) $request->get('processing_days_max') : null,
            'validity_days' => $request->has('validity_days') ? (int) $request->get('validity_days') : null,
            'maximum_stay_days' => $request->has('maximum_stay_days') ? (int) $request->get('maximum_stay_days') : null,
            'currency' => $currency,
            'price' => $price,
            'price_idr' => $priceIdr,
            'is_active' => (bool) $request->get('is_active', true),
            'is_featured' => (bool) $request->get('is_featured', false),
            'sort_order' => (int) $request->get('sort_order', 0),
        ];

        $rawItems = $request->get('items', []);

        /** @var VisaService $service */
        $service = DB::transaction(function () use ($serviceData, $rawItems): VisaService {
            /** @var VisaService $created */
            $created = VisaService::create($serviceData);

            if (is_array($rawItems) && ! empty($rawItems)) {
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
                        'visa_service_id' => $created->id,
                        'type' => $itemType,
                        'content' => $content,
                        'details' => $details,
                        'is_mandatory' => $isMandatory,
                        'sort_order' => $sortOrder,
                    ]);
                }
            }

            return $created;
        });

        $service->load(['country', 'items']);
        $serviceName = $service->getTranslation('name', 'id', false);

        return Response::json([
            'message' => "Visa service '{$serviceName}' created successfully.",
            'service' => [
                'id' => $service->id,
                'name' => $serviceName,
                'slug' => $service->slug,
                'country' => $country->getTranslation('name', 'id', false),
                'visa_type' => $service->getTranslation('visa_type', 'id', false),
                'entry_type' => $service->entry_type?->value,
                'pricing' => [
                    'currency' => $service->currency,
                    'price' => (float) $service->price,
                    'price_idr' => $service->price_idr,
                ],
                'items_created_count' => $service->items->count(),
                'is_active' => $service->is_active,
                'is_featured' => $service->is_featured,
            ],
        ]);
    }
}
