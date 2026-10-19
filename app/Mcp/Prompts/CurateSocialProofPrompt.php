<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use App\Models\Testimonial;
use App\Models\Tour;
use App\Models\UmrahPackage;
use App\Models\Vehicle;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('curate_social_proof')]
#[Title('Kurasi Social Proof & Ulasan Pelanggan')]
#[Description('Panduan AI dalam memilih testimoni traveler yang paling persuasif dan relevan untuk landing page, proposal perjalanan, atau materi promosi.')]
class CurateSocialProofPrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $rawProductType = (string) $request->get('product_type', '');
        $theme = (string) $request->get('theme', 'Kepuasan umum & profesionalisme pelayanan');
        $locale = (string) $request->get('locale', 'id');

        $query = Testimonial::query()
            ->active()
            ->with('product')
            ->orderByDesc('is_featured')
            ->orderByDesc('rating')
            ->take(15);

        if (! empty($rawProductType)) {
            $productType = match (strtolower($rawProductType)) {
                'tour' => Tour::class,
                'vehicle' => Vehicle::class,
                'umrah', 'umrah_package' => UmrahPackage::class,
                default => $rawProductType,
            };
            $query->where('product_type', $productType);
        }

        $testimonials = $query->get();

        $reviewsList = $testimonials->map(function (Testimonial $item) use ($locale): string {
            $reviewer = $item->getTranslation('reviewer_name', $locale, false) ?: $item->getTranslation('reviewer_name', 'id', false);
            $country = $item->reviewer_country ?: 'ID';
            $flag = $item->reviewer_flag ?: '🇮🇩';
            $pType = class_basename($item->product_type);
            $content = $item->getTranslation('content', $locale, false) ?: $item->getTranslation('content', 'id', false);

            return "- **{$reviewer}** ({$flag} {$country}) | Rating: {$item->stars} ({$item->rating}/5) [{$pType}]\n  \"{$content}\"";
        })->implode("\n\n");

        if (empty($reviewsList)) {
            $reviewsList = 'Belum ada data ulasan aktif untuk kriteria produk ini.';
        }

        $instruction = <<<PROMPT
Anda adalah Marketing Copywriter & Conversion Rate Optimization (CRO) Specialist untuk Baharsyah Jelajah.

Tugas Anda adalah mengkurasi dan menyusun narasi social proof dari ulasan asli traveler kami untuk meyakinkan calon pelanggan.

Target Tema Promosi:
"{$theme}"

Kategori Produk:
"{$rawProductType}"

Daftar Ulasan Asli Pelanggan (Database Baharsyah Jelajah):
{$reviewsList}

Instruksi Output:
1. **Top 3 Ulasan Paling Persuasif**: Pilih 3 ulasan dengan poin emosional terkuat (kenyamanan, ketepatan waktu, keramahan tim, fasilitas terbaik).
2. **Kutipan Kunci (Key Pull-Quotes)**: Ekstrak 1-2 kalimat pendek yang cocok dijadikan badge testimonial atau headline banner.
3. **Copywriting Social Proof**: Tuliskan paragraf pengantar singkat (2-3 kalimat) bertema trust-building yang dapat langsung disisipkan ke landing page atau brosur digital.
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
                name: 'product_type',
                description: 'Filter jenis produk yang ingin dikurasi ulasannya: "tour", "vehicle", atau "umrah". Kosongkan untuk semua produk.',
                required: false,
            ),
            new Argument(
                name: 'theme',
                description: 'Fokus sudut pandang promosi (contoh: "Keluarga & Kenyamanan Lansia", "Kecepatan Respons & Mobil Bersih").',
                required: false,
            ),
            new Argument(
                name: 'locale',
                description: 'Bahasa output preferensi ("id", "en", "ms"). Default: "id".',
                required: false,
            ),
        ];
    }
}
