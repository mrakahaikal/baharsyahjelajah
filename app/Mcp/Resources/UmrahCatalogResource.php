<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Models\Country;
use App\Models\UmrahPackage;
use App\Models\UmrahPackagePrice;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('umrah_catalog')]
#[Title('Katalog Paket Ibadah Umrah (Umrah Catalog)')]
#[Description('Katalog terstruktur seluruh paket ibadah Umrah aktif, klasifikasi (reguler, plus, VIP, ramadan, private), akomodasi hotel Makkah & Madinah, maskapai, rentang tarif, dan jadwal keberangkatan terdekat.')]
#[Uri('umrah://catalog')]
#[MimeType('application/json')]
class UmrahCatalogResource extends Resource
{
    public function handle(Request $request): Response
    {
        $packages = UmrahPackage::query()
            ->active()
            ->with([
                'prices',
                'upcomingDepartures',
                'countries',
            ])
            ->withCount('testimonials')
            ->withAvg('testimonials', 'rating')
            ->orderByDesc('is_featured')
            ->orderBy('id')
            ->get()
            ->map(function (UmrahPackage $pkg): array {
                $nextDep = $pkg->upcomingDepartures->first();

                return [
                    'id' => $pkg->id,
                    'name' => $pkg->getTranslations('name'),
                    'slug' => $pkg->getTranslations('slug'),
                    'package_type' => $pkg->package_type,
                    'package_type_label' => $pkg->package_type_label,
                    'duration_days' => (int) $pkg->duration_days,
                    'starting_price_idr' => (int) $pkg->starting_price_idr,
                    'formatted_price' => $pkg->formatted_price,
                    'airline' => $pkg->airline,
                    'hotel_makkah' => $pkg->hotel_makkah ? "{$pkg->hotel_makkah} ({$pkg->hotel_makkah_stars} Bintang)" : null,
                    'hotel_madinah' => $pkg->hotel_madinah ? "{$pkg->hotel_madinah} ({$pkg->hotel_madinah_stars} Bintang)" : null,
                    'visa_included' => (bool) $pkg->visa_included,
                    'handling_included' => (bool) $pkg->handling_included,
                    'is_featured' => (bool) $pkg->is_featured,
                    'has_availability' => $pkg->has_availability,
                    'next_departure' => $nextDep ? [
                        'id' => $nextDep->id,
                        'departure_date' => $nextDep->departure_date?->toDateString(),
                        'status' => $nextDep->status,
                        'quota_sisa' => (int) $nextDep->quota_sisa,
                    ] : null,
                    'room_prices' => $pkg->prices->map(fn (UmrahPackagePrice $p): array => [
                        'room_type' => $p->room_type,
                        'price_idr' => (int) $p->price_idr,
                        'formatted_price' => $p->formatted_price,
                    ])->all(),
                    'countries' => $pkg->countries->map(fn (Country $c): array => [
                        'id' => $c->id,
                        'name' => $c->getTranslation('name', 'id', false),
                        'code' => $c->code,
                    ])->all(),
                    'average_rating' => round((float) ($pkg->testimonials_avg_rating ?? 0), 1),
                    'testimonials_count' => (int) ($pkg->testimonials_count ?? 0),
                ];
            })
            ->all();

        return Response::json([
            'resource' => 'umrah://catalog',
            'total_active_packages' => count($packages),
            'packages' => $packages,
        ]);
    }
}
