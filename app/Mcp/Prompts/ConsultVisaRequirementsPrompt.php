<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use App\Models\Country;
use App\Models\VisaService;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('consult_visa_requirements')]
#[Title('Konsultasi Persyaratan Visa & Panduan Pengajuan')]
#[Description('Panduan prompt terstruktur bagi AI untuk memberikan konsultasi visa menyeluruh: rekomendasi tipe visa, checklist berkas, estimasi durasi dan biaya, serta tips biometrik/kedutaan.')]
class ConsultVisaRequirementsPrompt extends Prompt
{
    /**
     * Handle the prompt request.
     */
    public function handle(Request $request): Response
    {
        $countryIdentifier = $request->get('country_identifier');
        $profile = (string) $request->get('applicant_profile', 'Warga Negara Indonesia (pemegang paspor biasa)');
        $purpose = (string) $request->get('travel_purpose', 'Wisata / Liburan Keluarga');

        $country = null;
        $visas = collect();

        if (! empty($countryIdentifier)) {
            $raw = trim((string) $countryIdentifier);
            $code = Str::upper($raw);

            $country = Country::query()
                ->where('id', is_numeric($raw) ? (int) $raw : 0)
                ->orWhere('slug', $raw)
                ->orWhere('iso_alpha_2', $code)
                ->orWhere('iso_alpha_3', $code)
                ->first();

            if ($country) {
                $visas = VisaService::query()
                    ->where('country_id', $country->id)
                    ->active()
                    ->with('items')
                    ->get();
            }
        }

        $visaContext = '';
        if ($country && $visas->isNotEmpty()) {
            $countryName = $country->getTranslation('name', 'id', false);
            $visaBlocks = $visas->map(function (VisaService $v): string {
                $name = $v->getTranslation('name', 'id', false);
                $type = $v->getTranslation('visa_type', 'id', false);
                $entry = $v->entry_type?->getLabel() ?: 'Standar';
                $price = $v->formatted_price;
                $duration = $v->processing_days_min !== null ? "{$v->processing_days_min}-{$v->processing_days_max} hari kerja" : 'Variatif';

                $reqs = $v->items->where('type.value', 'requirement')->map(fn ($i) => '- '.$i->getTranslation('content', 'id', false))->implode("\n");
                if (empty($reqs)) {
                    $reqs = '- Menyesuaikan arahan kedutaan.';
                }

                return <<<ITEM
#### {$name} (Tipe: {$type} | Izin Masuk: {$entry})
- Biaya Resmi: {$price}
- Estimasi Proses: {$duration}
- Persyaratan Berkas:
{$reqs}
ITEM;
            })->implode("\n\n");

            $visaContext = <<<INFO
### Profil Negara & Opsi Visa Terdaftar ({$countryName}):
{$visaBlocks}
INFO;
        } elseif ($country) {
            $countryName = $country->getTranslation('name', 'id', false);
            $visaContext = "Catatan: Belum ada layanan visa terdaftar untuk negara '{$countryName}'. Gunakan resource \"countries://catalog\" atau berikan panduan umum kedutaan terkait.";
        } else {
            $visaContext = 'Catatan: Negara target belum ditentukan. Gunakan resource "visas://catalog" untuk melihat seluruh layanan visa yang tersedia di sistem Baharsyah Jelajah.';
        }

        $instruction = <<<PROMPT
Anda adalah Senior Visa & Immigration Specialist di Baharsyah Jelajah.

{$visaContext}

### Profil Pemohon & Rencana Perjalanan:
- **Profil Pemohon**: {$profile}
- **Tujuan Kunjungan**: {$purpose}

### Format Konsultasi yang Wajib Diberikan kepada Pengguna:
1. **Rekomendasi Jenis Visa**:
   - Tentukan jenis visa yang paling sesuai dan efisien (apakah eVisa, Visa on Arrival, atau Visa Stiker Kedutaan).
2. **Checklist Berkas Persyaratan Terperinci**:
   - Kelompokkan dokumen menjadi: Dokumen Utama (Paspor, Foto), Dokumen Finansial (Rekening Koran, Surat Referensi Bank), dan Dokumen Perjalanan (Tiket, Reservasi Hotel, Asuransi).
3. **Simulasi Waktu & Biaya**:
   - Jelaskan kapan pemohon harus mulai mengajukan berkas (lead time) dan rincian biaya resmi.
4. **Tips Kritis & Mitigasi Penolakan**:
   - Faktor umum penolakan visa dan cara menghindarinya.
   - Ketentuan paspor (masa berlaku minimum 6 bulan, lembar kosong yang disyaratkan).
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
                description: 'ID, slug, atau kode ISO negara tujuan (contoh: "SA", "jepang", "turki", "MY").',
                required: false,
            ),
            new Argument(
                name: 'applicant_profile',
                description: 'Profil pemohon visa (contoh: "Keluarga 4 orang (2 dewasa, 2 anak)", "Karyawan swasta solo traveler", "Jamaah lansia").',
                required: false,
            ),
            new Argument(
                name: 'travel_purpose',
                description: 'Tujuan perjalanan (contoh: "Liburan", "Ibadah Umrah Mandiri", "Kunjungan Bisnis").',
                required: false,
            ),
        ];
    }
}
