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

#[Name('curate_country_guide')]
#[Title('Kurasi Panduan Negara & Ekosistem Perjalanan')]
#[Description('Panduan prompt terstruktur bagi AI untuk menyusun panduan perjalanan komprehensif suatu negara: overview destinasi, persyaratan visa, budaya lokal, musim kunjungan, dan keterkaitan konten.')]
class CurateCountryGuidePrompt extends Prompt
{
    /**
     * Handle the prompt request.
     */
    public function handle(Request $request): Response
    {
        $identifier = $request->get('country_identifier');
        $theme = (string) $request->get('focus_theme', 'Eksplorasi Budaya, Wisata Ikonik & Religi');
        $audience = (string) $request->get('target_audience', 'Jamaah dan wisatawan Indonesia');

        $country = null;
        if (! empty($identifier)) {
            if (is_numeric($identifier)) {
                $country = Country::with(['destinations', 'visaServices', 'tourPackages', 'posts'])->find((int) $identifier);
            } else {
                $raw = trim((string) $identifier);
                $code = Str::upper($raw);
                $country = Country::with(['destinations', 'visaServices', 'tourPackages', 'posts'])
                    ->where('slug', $raw)
                    ->orWhere('iso_alpha_2', $code)
                    ->orWhere('iso_alpha_3', $code)
                    ->first();
            }
        }

        $countryContext = '';
        if ($country) {
            $nameId = $country->getTranslation('name', 'id', false);
            $nameEn = $country->getTranslation('name', 'en', false);
            $capital = $country->getTranslation('capital_city', 'id', false) ?: '-';
            $language = $country->getTranslation('language', 'id', false) ?: '-';
            $currency = $country->currency_code ?: '-';
            $bestTime = $country->getTranslation('best_time_to_visit', 'id', false) ?: 'Sepanjang tahun sesuai preferensi musim.';
            $requirements = $country->getTranslation('travel_requirements_summary', 'id', false) ?: 'Periksa paspor dan izin visa yang berlaku.';
            $destinationsList = $country->destinations->take(8)->map(fn ($d) => $d->getTranslation('name', 'id', false))->filter()->implode(', ') ?: 'Belum ada destinasi terdaftar.';
            $visaList = $country->visaServices->take(5)->map(fn ($v) => $v->getTranslation('name', 'id', false))->filter()->implode(', ') ?: 'Layanan visa tersedia berdasarkan permohonan.';
            $postsCount = $country->posts->count();

            $countryContext = <<<INFO
### Profil Negara yang Dikurasi:
- **Nama**: {$nameId} (EN: {$nameEn})
- **Kode ISO**: {$country->iso_alpha_2} / {$country->iso_alpha_3}
- **Ibukota**: {$capital}
- **Mata Uang**: {$currency}
- **Bahasa**: {$language}
- **Musim Terbaik**: {$bestTime}
- **Ringkasan Syarat Perjalanan**: {$requirements}
- **Destinasi Wisata Terdaftar**: {$destinationsList}
- **Layanan Visa Terhubung**: {$visaList}
- **Artikel Terhubung Saat Ini**: {$postsCount} artikel
INFO;
        } else {
            $countryContext = 'Catatan: Negara spesifik belum dipilih. Gunakan daftar negara aktif dari resource "countries://catalog" untuk menentukan negara yang ingin dikurasi.';
        }

        $instruction = <<<PROMPT
Anda adalah Senior International Travel Specialist & Content Strategist untuk Baharsyah Jelajah.

{$countryContext}

### Parameter Kurasi:
- **Fokus Tema**: {$theme}
- **Target Segmen**: {$audience}

### Tugas Panduan Perjalanan Komprehensif yang Wajib Disusun:
1. **Ikhtisar Negara & Daya Tarik Utama (*Country Overview*)**:
   - Jelaskan mengapa negara ini istimewa bagi wisatawan nusantara, identitas budaya utama, dan nuansa pengalaman perjalanan yang ditawarkan.
2. **Konektivitas & Rekomendasi Destinasi**:
   - Analisis destinasi wisata yang ada atau sarankan destinasi utama baru yang perlu didaftarkan menggunakan tool `create_destination`.
   - Klasifikasikan spot wisata ke dalam kategori religi, alam, belanja, atau warisan sejarah.
3. **Persiapan Perjalanan & Panduan Visa**:
   - Paparkan jenis visa yang dibutuhkan wisatawan asal Indonesia, estimasi waktu proses, dan dokumen esensial.
4. **Waktu Terbaik & Tips Praktis**:
   - Informasi musim (suhu, cuaca, peak season vs low season).
   - Etika lokal, budaya berpakaian, dan tips pembayaran/mata uang.
5. **Rekomendasi Publikasi & Konten**:
   - Sarankan topik artikel blog spesifik yang dapat ditulis menggunakan tool `create_article` dalam format RichText HTML semantik.
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
                description: 'ID, slug, atau kode ISO Alpha-2/Alpha-3 negara target (contoh: "SA", "arab-saudi", "TR", "jepang").',
                required: false,
            ),
            new Argument(
                name: 'focus_theme',
                description: 'Tema perjalanan yang ingin difokuskan (contoh: "Wisata Halal & Ziarah", "Liburan Musim Dingin", "Eksplorasi Kota & Kuliner").',
                required: false,
            ),
            new Argument(
                name: 'target_audience',
                description: 'Target wisatawan yang dituju (contoh: "Jamaah umrah mandiri", "Wisatawan keluarga", "Pebisnis").',
                required: false,
            ),
        ];
    }
}
