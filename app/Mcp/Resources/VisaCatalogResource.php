<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Models\VisaService;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('visa_catalog')]
#[Title('Katalog Layanan Visa')]
#[Description('Katalog terstruktur seluruh layanan visa aktif terkelompok per negara, jenis visa, durasi proses, jenis izin masuk, dan informasi biaya resmi.')]
#[Uri('visas://catalog')]
#[MimeType('application/json')]
class VisaCatalogResource extends Resource
{
    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $services = VisaService::query()
            ->active()
            ->with(['country', 'items'])
            ->withCount('items')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (VisaService $s): array => [
                'id' => $s->id,
                'name' => [
                    'id' => $s->getTranslation('name', 'id', false),
                    'en' => $s->getTranslation('name', 'en', false),
                    'ms' => $s->getTranslation('name', 'ms', false),
                ],
                'slug' => $s->slug,
                'visa_type' => [
                    'id' => $s->getTranslation('visa_type', 'id', false),
                    'en' => $s->getTranslation('visa_type', 'en', false),
                    'ms' => $s->getTranslation('visa_type', 'ms', false),
                ],
                'country' => $s->country ? [
                    'id' => $s->country->id,
                    'name' => $s->country->getTranslation('name', 'id', false),
                    'slug' => $s->country->slug,
                    'iso_alpha_2' => $s->country->iso_alpha_2,
                ] : null,
                'entry_type' => $s->entry_type?->value,
                'entry_type_label' => $s->entry_type?->getLabel(),
                'pricing' => [
                    'currency' => $s->currency,
                    'price' => $s->price !== null ? (float) $s->price : null,
                    'price_idr' => $s->price_idr,
                    'formatted_price' => $s->formatted_price,
                ],
                'processing_days_min' => $s->processing_days_min,
                'processing_days_max' => $s->processing_days_max,
                'validity_days' => $s->validity_days,
                'maximum_stay_days' => $s->maximum_stay_days,
                'items_count' => $s->items_count,
                'is_featured' => $s->is_featured,
                'cover_url' => $s->cover_url,
            ])
            ->all();

        return Response::json([
            'resource' => 'visas://catalog',
            'total_active_services' => count($services),
            'services' => $services,
        ]);
    }
}
