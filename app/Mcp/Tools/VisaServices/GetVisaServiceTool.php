<?php

declare(strict_types=1);

namespace App\Mcp\Tools\VisaServices;

use App\Models\VisaService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('get_visa_service')]
#[Title('Get Visa Service')]
#[Description('Retrieve full profile of a visa service by ID or slug, including requirements, terms, inclusions, exclusions, pricing, and country details.')]
class GetVisaServiceTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The numeric ID of the visa service.'),
            'slug' => $schema->string()->description('The URL slug of the visa service (used if "id" is omitted).'),
            'locale' => $schema->string()->description('Preferred display locale ("id", "en", or "ms"). Default is "id".'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $slug = $request->get('slug');
        $locale = (string) $request->get('locale', 'id');

        if (empty($id) && empty($slug)) {
            return Response::error('Either "id" or "slug" must be provided.');
        }

        $query = VisaService::query()->with(['country', 'items']);

        /** @var VisaService|null $service */
        $service = ! empty($id)
            ? $query->find((int) $id)
            : $query->where('slug', (string) $slug)->first();

        if (! $service) {
            $identifier = ! empty($id) ? "ID {$id}" : "slug '{$slug}'";

            return Response::error("Visa service with {$identifier} not found.");
        }

        // Group items by type
        $groupedItems = [
            'requirements' => [],
            'terms' => [],
            'included' => [],
            'excluded' => [],
            'notes' => [],
        ];

        foreach ($service->items as $item) {
            $typeKey = match ($item->type?->value) {
                'requirement' => 'requirements',
                'term' => 'terms',
                'included' => 'included',
                'excluded' => 'excluded',
                default => 'notes',
            };

            $groupedItems[$typeKey][] = [
                'id' => $item->id,
                'content' => $item->getTranslation('content', $locale, false) ?: $item->getTranslation('content', 'id', false),
                'details' => $item->getTranslation('details', $locale, false) ?: $item->getTranslation('details', 'id', false),
                'is_mandatory' => $item->is_mandatory,
                'translations' => [
                    'content' => $item->getTranslations('content'),
                    'details' => $item->getTranslations('details'),
                ],
            ];
        }

        $countryName = $service->country
            ? ($service->country->getTranslation('name', $locale, false) ?: $service->country->getTranslation('name', 'id', false))
            : null;

        return Response::json([
            'id' => $service->id,
            'name' => $service->getTranslation('name', $locale, false) ?: $service->getTranslation('name', 'id', false),
            'slug' => $service->slug,
            'visa_type' => $service->getTranslation('visa_type', $locale, false) ?: $service->getTranslation('visa_type', 'id', false),
            'summary' => $service->getTranslation('summary', $locale, false) ?: $service->getTranslation('summary', 'id', false),
            'description' => $service->getTranslation('description', $locale, false) ?: $service->getTranslation('description', 'id', false),
            'entry_type' => $service->entry_type?->value,
            'entry_type_label' => $service->entry_type?->getLabel(),
            'country' => $service->country ? [
                'id' => $service->country->id,
                'name' => $countryName,
                'slug' => $service->country->slug,
                'iso_alpha_2' => $service->country->iso_alpha_2,
                'iso_alpha_3' => $service->country->iso_alpha_3,
            ] : null,
            'pricing' => [
                'currency' => $service->currency,
                'price' => $service->price !== null ? (float) $service->price : null,
                'price_idr' => $service->price_idr,
                'formatted_price' => $service->formatted_price,
            ],
            'processing_time' => [
                'days_min' => $service->processing_days_min,
                'days_max' => $service->processing_days_max,
                'display' => $service->processing_days_min !== null
                    ? ($service->processing_days_max !== null
                        ? "{$service->processing_days_min} - {$service->processing_days_max} hari kerja"
                        : "{$service->processing_days_min} hari kerja")
                    : null,
            ],
            'validity' => [
                'validity_days' => $service->validity_days,
                'maximum_stay_days' => $service->maximum_stay_days,
            ],
            'items' => $groupedItems,
            'translations' => [
                'name' => $service->getTranslations('name'),
                'visa_type' => $service->getTranslations('visa_type'),
                'summary' => $service->getTranslations('summary'),
                'description' => $service->getTranslations('description'),
            ],
            'is_active' => $service->is_active,
            'is_featured' => $service->is_featured,
            'cover_url' => $service->cover_url,
            'gallery_urls' => $service->gallery_urls,
            'updated_at' => $service->updated_at?->toIso8601String(),
        ]);
    }
}
