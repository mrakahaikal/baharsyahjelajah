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

#[Name('visa_requirements_summary')]
#[Title('Ringkasan Persyaratan & Ketentuan Visa')]
#[Description('Ringkasan komprehensif seluruh checklist berkas persyaratan, ketentuan permohonan, dan durasi pengurusan visa aktif per negara.')]
#[Uri('visas://requirements-summary')]
#[MimeType('application/json')]
class VisaRequirementsSummaryResource extends Resource
{
    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $services = VisaService::query()
            ->active()
            ->with(['country', 'items'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (VisaService $s): array {
                $requirements = [];
                $terms = [];

                foreach ($s->items as $item) {
                    $content = $item->getTranslation('content', 'id', false);
                    $details = $item->getTranslation('details', 'id', false);

                    if ($item->type?->value === 'requirement') {
                        $requirements[] = [
                            'item' => $content,
                            'details' => $details,
                            'is_mandatory' => $item->is_mandatory,
                        ];
                    } elseif ($item->type?->value === 'term') {
                        $terms[] = [
                            'item' => $content,
                            'details' => $details,
                        ];
                    }
                }

                return [
                    'visa_id' => $s->id,
                    'visa_name' => $s->getTranslation('name', 'id', false),
                    'visa_slug' => $s->slug,
                    'country' => $s->country ? [
                        'name' => $s->country->getTranslation('name', 'id', false),
                        'iso' => $s->country->iso_alpha_2,
                    ] : null,
                    'processing_time' => $s->processing_days_min !== null
                        ? "{$s->processing_days_min} - {$s->processing_days_max} hari kerja"
                        : 'Menyesuaikan kedutaan',
                    'price_formatted' => $s->formatted_price,
                    'requirements_checklist' => $requirements,
                    'terms' => $terms,
                ];
            })
            ->all();

        return Response::json([
            'resource' => 'visas://requirements-summary',
            'total_services' => count($services),
            'requirements_by_visa' => $services,
        ]);
    }
}
