<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Models\VehicleRentalTerm;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('vehicle_rental_terms')]
#[Title('Ketentuan & Kebijakan Sewa Kendaraan')]
#[Description('Ringkasan seluruh ketentuan sewa armada aktif terkelompok per kategori kendaraan: wilayah operasional, jam sewa, harga termasuk/tidak termasuk, dan aturan pemesanan/reschedule.')]
#[Uri('vehicles://rental-terms')]
#[MimeType('application/json')]
class VehicleRentalTermsResource extends Resource
{
    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $terms = VehicleRentalTerm::query()
            ->active()
            ->orderBy('vehicle_category')
            ->orderBy('sort_order')
            ->get();

        $grouped = [];
        foreach ($terms as $t) {
            $catKey = $t->vehicle_category?->value ?? 'general';
            if (! isset($grouped[$catKey])) {
                $grouped[$catKey] = [
                    'category' => $catKey,
                    'category_label' => $t->vehicle_category?->getLabel() ?? 'Umum',
                    'terms' => [],
                ];
            }

            $grouped[$catKey]['terms'][] = [
                'type' => $t->type?->value,
                'type_label' => $t->type?->getLabel(),
                'title' => [
                    'id' => $t->getTranslation('title', 'id', false),
                    'en' => $t->getTranslation('title', 'en', false),
                ],
                'content' => [
                    'id' => $t->getTranslation('content', 'id', false),
                    'en' => $t->getTranslation('content', 'en', false),
                ],
            ];
        }

        return Response::json([
            'resource' => 'vehicles://rental-terms',
            'categories_count' => count($grouped),
            'terms_by_category' => array_values($grouped),
        ]);
    }
}
