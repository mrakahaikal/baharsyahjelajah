<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Models\UmrahDeparture;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('umrah_departures_schedule')]
#[Title('Jadwal Keberangkatan & Ketersediaan Kuota Umrah')]
#[Description('Daftar seluruh tanggal keberangkatan umrah mendatang yang masih aktif, kapasitas kuota, sisa seat yang tersedia, dan status pemesanan.')]
#[Uri('umrah://departures')]
#[MimeType('application/json')]
class UmrahDeparturesScheduleResource extends Resource
{
    public function handle(Request $request): Response
    {
        $departures = UmrahDeparture::query()
            ->upcoming()
            ->with('package')
            ->get()
            ->map(fn (UmrahDeparture $dep): array => [
                'id' => $dep->id,
                'package_id' => $dep->package_id,
                'package_name' => $dep->package?->getTranslation('name', 'id', false),
                'package_type' => $dep->package?->package_type,
                'airline' => $dep->package?->airline,
                'duration_days' => $dep->package?->duration_days,
                'departure_date' => $dep->departure_date?->toDateString(),
                'return_date' => $dep->return_date?->toDateString(),
                'quota_total' => (int) $dep->quota_total,
                'quota_booked' => (int) $dep->quota_booked,
                'quota_sisa' => (int) $dep->quota_sisa,
                'quota_percentage' => (int) $dep->quota_percentage,
                'status' => $dep->status,
                'starting_price_idr' => (int) ($dep->price_override_idr ?: $dep->package?->starting_price_idr),
            ])
            ->all();

        return Response::json([
            'resource' => 'umrah://departures',
            'total_upcoming_departures' => count($departures),
            'departures' => $departures,
        ]);
    }
}
