<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use App\Models\Country;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('audit_country_readiness')]
#[Title('Audit Kesiapan Data & Konten Negara')]
#[Description('Panduan prompt bagi AI untuk mengaudit kelengkapan data negara: validitas kode ISO Alpha-2 & Alpha-3, kelengkapan translasi (id, en, ms), mata uang, ibukota, serta keterkaitan destinasi dan layanan visa.')]
class AuditCountryReadinessPrompt extends Prompt
{
    /**
     * Handle the prompt request.
     */
    public function handle(Request $request): Response
    {
        $identifier = $request->get('country_identifier');

        $query = Country::query()
            ->withCount(['destinations', 'visaServices', 'tourPackages', 'posts'])
            ->orderBy('sort_order')
            ->orderBy('id');

        if (! empty($identifier)) {
            if (is_numeric($identifier)) {
                $query->where('id', (int) $identifier);
            } else {
                $raw = trim((string) $identifier);
                $code = Str::upper($raw);
                $query->where(function ($q) use ($raw, $code) {
                    $q->where('slug', $raw)
                        ->orWhere('iso_alpha_2', $code)
                        ->orWhere('iso_alpha_3', $code);
                });
            }
        }

        $countries = $query->get();

        $countryList = $countries->map(function (Country $c): string {
            $nameId = $c->getTranslation('name', 'id', false) ?: '-';
            $nameEn = $c->getTranslation('name', 'en', false) ?: 'Kosong';
            $hasIso3 = ! empty($c->iso_alpha_3) ? $c->iso_alpha_3 : 'Kosong';
            $hasCurrency = ! empty($c->currency_code) ? $c->currency_code : 'Kosong';
            $hasCapital = ! empty($c->getTranslation('capital_city', 'id', false)) ? 'Ada' : 'Kosong';
            $hasDescEn = ! empty($c->getTranslation('description', 'en', false)) ? 'Ada' : 'Kosong';
            $hasRequirements = ! empty($c->getTranslation('travel_requirements_summary', 'id', false)) ? 'Ada' : 'Kosong';

            return "- ID {$c->id} [{$c->slug}] ISO: {$c->iso_alpha_2}/{$hasIso3} | \"{$nameId}\" (EN: \"{$nameEn}\") | Mata Uang: {$hasCurrency} | Ibukota: {$hasCapital} | Desc EN: {$hasDescEn} | Syarat Perjalanan: {$hasRequirements} | Terhubung: {$c->destinations_count} destinasi, {$c->visa_services_count} visa, {$c->posts_count} artikel";
        })->implode("\n");

        if (empty($countryList)) {
            $countryList = "Tidak ditemukan negara yang cocok dengan identifier '{$identifier}'.";
        }

        $instruction = <<<PROMPT
Anda adalah Quality Assurance & Master Data Lead untuk platform wisata Baharsyah Jelajah.

Tugas Anda adalah mengaudit kesiapan data dan kelengkapan konten entitas negara:

### Data Negara yang Diaudit:
{$countryList}

### Aspek Pemeriksaan Wajib:
1. **Standarisasi Kode ISO & Finansial**:
   - Pastikan kode ISO Alpha-2 (2 huruf) dan ISO Alpha-3 (3 huruf) terisi lengkap serta valid secara standar internasional.
   - Periksa ketersediaan kode mata uang resmi (`currency_code`).
2. **Kelengkapan Multilingual**:
   - Identifikasi negara yang belum memiliki translasi nama, ibukota, bahasa, atau deskripsi dalam bahasa Inggris ('en') atau Melayu ('ms').
3. **Kesiapan Informasi Perjalanan Wisatawan**:
   - Evaluasi kelengkapan ringkasan syarat perjalanan (`travel_requirements_summary`) dan musim terbaik berkunjung (`best_time_to_visit`).
4. **Konektivitas Ekosistem (Destinasi & Visa)**:
   - Identifikasi negara yang belum memiliki destinasi wisata terhubung (0 destinasi).
   - Identifikasi negara yang belum memiliki data layanan visa terhubung (0 visa services).
5. **Rekomendasi Tindakan Korektif**:
   - Rekomendasikan pembaruan data menggunakan tool `update_country` untuk melengkapi translasi, ISO Alpha-3, atau syarat perjalanan.
   - Sarankan penambahan destinasi terkait menggunakan tool `create_destination` (dengan menyertakan `country_slugs`).
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
                description: 'ID, slug, atau kode ISO Alpha-2/Alpha-3 negara target yang ingin diaudit secara spesifik. Kosongkan untuk mengaudit semua negara.',
                required: false,
            ),
        ];
    }
}
