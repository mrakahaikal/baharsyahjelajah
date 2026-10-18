<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use App\Models\Destination;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;

#[Name('audit_destinations_readiness')]
#[Title('Audit Kesiapan Data & Konten Destinasi')]
#[Description('Panduan prompt bagi AI untuk mengaudit kelengkapan data destinasi: terjemahan multilingual, koordinat peta, asosiasi negara, dan artikel/paket tur pendukung.')]
class AuditDestinationsReadinessPrompt extends Prompt
{
    /**
     * Handle the prompt request.
     */
    public function handle(Request $request): Response
    {
        $destinations = Destination::query()
            ->with(['countries'])
            ->withCount(['posts', 'tourPackages'])
            ->orderBy('id')
            ->get();

        $destList = $destinations->map(function (Destination $d): string {
            $nameId = $d->getTranslation('name', 'id', false) ?: '-';
            $nameEn = $d->getTranslation('name', 'en', false) ?: 'Kosong';
            $hasMap = ! empty($d->map_url) ? 'Ada Peta' : 'Peta Belum Ada';
            $countries = $d->countries->pluck('slug')->implode(', ') ?: 'Tanpa Negara';
            $hasDescEn = ! empty($d->getTranslation('description', 'en', false)) ? 'Desc EN: Ada' : 'Desc EN: Kosong';

            return "- ID {$d->id} [{$d->slug}]: \"{$nameId}\" (EN: \"{$nameEn}\") | {$hasDescEn} | {$hasMap} | Negara: [{$countries}] | Terhubung: {$d->posts_count} artikel, {$d->tour_packages_count} tur";
        })->implode("\n");

        $instruction = <<<PROMPT
Anda adalah Quality Assurance & Content Ops Lead untuk platform wisata Baharsyah Jelajah.

Tugas Anda adalah mengaudit kesiapan data seluruh destinasi wisata saat ini:

### Daftar Destinasi Saat Ini:
{$destList}

### Aspek Pemeriksaan Wajib:
1. **Kelengkapan Multilingual**:
   - Identifikasi destinasi yang belum memiliki nama atau deskripsi bahasa Inggris ('en') atau bahasa Melayu ('ms').
2. **Konektivitas Geografis & Peta**:
   - Identifikasi destinasi yang belum memiliki tautan `map_url` Google Maps yang valid.
   - Identifikasi destinasi yang belum terhubung ke entitas `Country`.
3. **Ketersediaan Konten Pendukung**:
   - Temukan destinasi yang memiliki 0 artikel blog dan 0 paket tur (destinasi yatim / kurang terekspos).
4. **Rencana Tindakan**:
   - Berikan rekomendasi pembaruan langsung menggunakan tool `update_destination` (untuk melengkapi peta/translasi/negara).
   - Rekomendasikan pembuatan artikel baru dengan tool `create_article` untuk destinasi yang belum memiliki artikel.
PROMPT;

        return Response::text($instruction);
    }

    /**
     * @return array<int, mixed>
     */
    public function arguments(): array
    {
        return [];
    }
}
