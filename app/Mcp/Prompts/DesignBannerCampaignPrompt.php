<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use App\Models\Banner;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('design_banner_campaign')]
#[Title('Desain Kampanye Promosi Banner')]
#[Description('Panduan AI dalam merancang copywriting banner beranda (headline memikat, subheadline informatif, penentuan CTA, dan penempatan banner hero vs promo).')]
class DesignBannerCampaignPrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $campaignTopic = (string) $request->get('topic', 'Promo Liburan & Umrah Hemat');
        $targetAudience = (string) $request->get('audience', 'Keluarga & Jamaah Indonesia');
        $rawPlacement = (string) $request->get('placement', 'home_hero');
        $locale = (string) $request->get('locale', 'id');

        $activeBanners = Banner::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $activeList = $activeBanners->map(function (Banner $b) use ($locale): string {
            $t = $b->getTranslation('title', $locale, false) ?: $b->getTranslation('title', 'id', false);
            $p = $b->placement?->getLabel() ?: 'Promo';
            $cta = $b->getTranslation('cta_label', $locale, false) ?: 'Lihat Detail';

            return "- [{$p}] \"{$t}\" -> CTA: [{$cta}]";
        })->implode("\n");

        if (empty($activeList)) {
            $activeList = 'Belum ada banner aktif saat ini.';
        }

        $instruction = <<<PROMPT
Anda adalah Creative Director & Head of Marketing untuk Baharsyah Jelajah.

Tugas Anda adalah merancang konsep kampanye banner promosi website yang menarik, bernilai konversi tinggi, dan elegan.

Topik / Event Kampanye:
"{$campaignTopic}"

Target Audiens:
"{$targetAudience}"

Rencana Penempatan:
"{$rawPlacement}"

Banner Yang Sedang Tayang (Untuk Referensi & Menghindari Duplikasi Tema):
{$activeList}

Instruksi Output:
1. **Pilihan Headline (Judul Utama)**: Berikan 3 opsi headline memikat (Pendek, Emosional, Berorientasi Manfaat) dalam Bahasa Indonesia, Inggris, dan Melayu.
2. **Subheadline (Deskripsi Pendukung)**: 1-2 kalimat pendukung yang menjelaskan penawaran utama, diskon, atau keunggulan paket.
3. **Call-to-Action (CTA)**:
   - Tipe CTA yang disarankan (`route` ke katalog tur/umrah, `url` eksternal, atau `whatsapp` ke CS).
   - Teks tombol CTA dalam 3 bahasa (misal: "Pesan Sekarang", "Book Now", "Tempah Sekarang").
4. **Arahan Visual Fotografi**: Rekomendasi jenis foto/aset gambar yang cocok dijadikan background banner (lanskap Masjidil Haram, balon udara Cappadocia, armada Alphard mewah, dll.).
5. **Rekomendasi Pemanggilan Tool**: Tuliskan payload parameter yang siap dimasukkan ke tool `create_banner`.
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
                name: 'topic',
                description: 'Topik atau tema promosi (contoh: "Promo Umrah Awal Musim", "Early Bird Tour Turki Musim Gugur").',
                required: false,
            ),
            new Argument(
                name: 'audience',
                description: 'Target segmen pelanggan (contoh: "Keluarga Besar", "Pasangan Muda", "Traveler Solo").',
                required: false,
            ),
            new Argument(
                name: 'placement',
                description: 'Lokasi penempatan: "home_hero" (banner slide utama) atau "home_promo" (banner promo tengah).',
                required: false,
            ),
            new Argument(
                name: 'locale',
                description: 'Bahasa preferensi ("id", "en", "ms"). Default: "id".',
                required: false,
            ),
        ];
    }
}
