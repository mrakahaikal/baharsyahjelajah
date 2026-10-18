<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use App\Models\VisaService;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('audit_visa_services_readiness')]
#[Title('Audit Kesiapan Operasional Layanan Visa')]
#[Description('Panduan prompt bagi AI untuk mengaudit kesiapan data layanan visa: kelengkapan berkas persyaratan, durasi proses, sinkronisasi kurs mata uang/IDR, dan kualitas terjemahan multilingual.')]
class AuditVisaServicesReadinessPrompt extends Prompt
{
    /**
     * Handle the prompt request.
     */
    public function handle(Request $request): Response
    {
        $countryIdentifier = $request->get('country_identifier');

        $query = VisaService::query()
            ->with(['country', 'items'])
            ->withCount('items')
            ->orderBy('sort_order')
            ->orderBy('id');

        if (! empty($countryIdentifier)) {
            $raw = trim((string) $countryIdentifier);
            $code = Str::upper($raw);

            $query->whereHas('country', function ($q) use ($raw, $code): void {
                $q->where('id', is_numeric($raw) ? (int) $raw : 0)
                    ->orWhere('slug', $raw)
                    ->orWhere('iso_alpha_2', $code)
                    ->orWhere('iso_alpha_3', $code);
            });
        }

        $services = $query->get();

        $visaList = $services->map(function (VisaService $s): string {
            $nameId = $s->getTranslation('name', 'id', false) ?: '-';
            $nameEn = $s->getTranslation('name', 'en', false) ?: 'Kosong';
            $countryName = $s->country ? $s->country->getTranslation('name', 'id', false) : 'Tanpa Negara';
            $hasPricing = $s->price !== null || $s->price_idr !== null ? "{$s->currency} {$s->price} (IDR: {$s->price_idr})" : 'Harga Belum Diisi';
            $hasDuration = $s->processing_days_min !== null ? "{$s->processing_days_min}-{$s->processing_days_max} hari" : 'Durasi Kosong';
            $itemsCount = $s->items_count;
            $itemsStatus = $itemsCount > 0 ? "{$itemsCount} butir berkas" : 'PERINGATAN: 0 berkas persyaratan';

            return "- ID {$s->id} [{$s->slug}] \"{$nameId}\" (EN: \"{$nameEn}\") | Negara: {$countryName} | Harga: {$hasPricing} | Durasi: {$hasDuration} | Item: {$itemsStatus}";
        })->implode("\n");

        if (empty($visaList)) {
            $visaList = "Tidak ditemukan layanan visa yang cocok dengan kriteria negara '{$countryIdentifier}'.";
        }

        $instruction = <<<PROMPT
Anda adalah Visa Operations & Quality Assurance Lead untuk Baharsyah Jelajah.

Tugas Anda adalah mengaudit kesiapan operasional seluruh katalog layanan visa:

### Data Layanan Visa yang Diaudit:
{$visaList}

### Aspek Pemeriksaan Wajib:
1. **Kelengkapan Butir Persyaratan (Visa Checklist Items)**:
   - Identifikasi visa yang memiliki 0 items persyaratan berkas. Layanan visa tanpa checklist berkas akan membingungkan calon wisatawan.
2. **Keterisian & Sinkronisasi Harga**:
   - Pastikan setiap visa memiliki mata uang (`currency`) dan harga resmi, serta nilai tukar IDR (`price_idr`) terisi.
3. **Kepastian Durasi Pengurusan**:
   - Temukan visa yang belum mengisi `processing_days_min` atau `processing_days_max`.
4. **Kelengkapan Multilingual**:
   - Periksa ketersediaan nama, ringkasan, dan tipe visa dalam bahasa Inggris ('en') untuk menyasar ekspatriat atau wisatawan mancanegara.
5. **Rekomendasi Tindakan Korektif**:
   - Berikan rekomendasi pembaruan data menggunakan tool `update_visa_service` (misal: menambahkan items persyaratan, melengkapi durasi proses).
PROMPT;

        return Response::text($instruction);
    }

    /**
     * @return array<int, Argument>
     */
    public function arguments(): array
    {
        return [
            new Argument(
                name: 'country_identifier',
                description: 'ID, slug, atau kode ISO negara untuk mengaudit visa negara tertentu saja. Kosongkan untuk mengaudit semua.',
                required: false,
            ),
        ];
    }
}
