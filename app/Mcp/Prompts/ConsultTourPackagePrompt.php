<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use App\Models\Tour;
use App\Models\TourPackage;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('consult_tour_package')]
#[Title('Konsultasi & Rekomendasi Paket Wisata (Tour Advisor)')]
#[Description('Panduan bagi AI Travel Consultant untuk merekomendasikan paket wisata terbaik berdasarkan preferensi destinasi, jumlah peserta (pax), durasi hari, dan estimasi anggaran.')]
class ConsultTourPackagePrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $destination = (string) $request->get('destination', 'Semua Destinasi');
        $pax = max(1, (int) $request->get('pax', 2));
        $durationDays = (int) $request->get('duration_days', 0);
        $budget = (string) $request->get('budget', 'Fleksibel');
        $tourType = (string) $request->get('tour_type', '');
        $language = (string) $request->get('language', 'id');

        $query = Tour::query()
            ->active()
            ->with(['category', 'countries', 'packages.tiers.priceTiers', 'packages.destinations']);

        if (! empty($tourType)) {
            $query->where('tour_type', $tourType);
        }

        $tours = $query->orderByDesc('is_featured')->take(8)->get();

        $catalogSummary = $tours->map(function (Tour $tour) use ($pax, $durationDays): string {
            $name = $tour->getTranslation('name', 'id', false);
            $cat = $tour->category?->getTranslation('name', 'id', false) ?? 'Umum';
            $type = $tour->tour_type?->getLabel() ?? $tour->tour_type?->value;

            $pkgStrings = $tour->packages
                ->when($durationDays > 0, fn ($c) => $c->where('duration_days', $durationDays))
                ->map(function (TourPackage $pkg) use ($pax): string {
                    $pkgName = $pkg->getTranslation('name', 'id', false);
                    $tierInfo = $pkg->tiers->map(function ($tier) use ($pax): string {
                        $tName = $tier->getTranslation('name', 'id', false);
                        $pt = $tier->priceTierForPax($pax) ?? $tier->priceTiers->first();
                        $priceStr = $pt ? "{$pt->currency} ".number_format((float) $pt->price, 0, ',', '.').'/pax' : 'Hubungi Admin';

                        return "{$tName}: {$priceStr}";
                    })->implode(', ');

                    return "  * [Package ID: {$pkg->id}] {$pkgName} ({$pkg->duration_label}) -> {$tierInfo}";
                })->implode("\n");

            if (empty($pkgStrings)) {
                $pkgStrings = '  * Belum ada paket dengan durasi spesifik tersebut.';
            }

            return "- **[Tour ID: {$tour->id}] {$name}** ({$cat} - {$type})\n{$pkgStrings}";
        })->implode("\n\n");

        if (empty($catalogSummary)) {
            $catalogSummary = 'Saat ini belum ada data tur aktif yang cocok dengan filter tersebut.';
        }

        $promptText = <<<PROMPT
Anda adalah Senior Travel Consultant di **Baharsyah Jelajah**. Tugas Anda adalah memberikan rekomendasi paket wisata yang personal, akurat, dan persuasif kepada calon wisatawan.

### Parameter Kebutuhan Calon Wisatawan:
- **Preferensi Destinasi / Tema**: {$destination}
- **Jumlah Peserta (Pax)**: {$pax} Orang
- **Preferensi Durasi**: {$durationDays} Hari (0 = Fleksibel)
- **Preferensi Anggaran**: {$budget}
- **Bahasa Pengantar**: {$language}

### Ringkasan Katalog Tur Aktif & Estimasi Harga untuk {$pax} Pax:
{$catalogSummary}

### Instruksi Penyusunan Rekomendasi:
1. **Rekomendasi Utama (Top 1-3 Paket)**:
   - Pilih paket yang paling relevan dengan destinasi, durasi, dan jumlah peserta ({$pax} pax).
   - Gunakan tool `get_tour_package` dan `calculate_tour_price` jika Anda memerlukan detail itinerary harian atau kalkulasi total biaya pasti untuk {$pax} orang.
2. **Perbandingan Tier & Fasilitas**:
   - Jelaskan pilihan tier kualitas yang tersedia (misal: kelas bintang hotel) beserta harga per pax dan total untuk {$pax} orang.
   - Sebutkan fasilitas utama yang sudah termasuk (`include`) dan belum termasuk (`exclude`).
3. **Highlight Itinerary**:
   - Berikan gambaran singkat daya tarik destinasi yang akan dikunjungi dari hari ke hari.
4. **Call to Action (CTA)**:
   - Ajak calon wisatawan untuk memilih tanggal keberangkatan dan tawarkan pembuatan draf penawaran resmi (quotation).
PROMPT;

        return Response::text($promptText);
    }

    /**
     * @return array<int, Argument>
     */
    public function arguments(): array
    {
        return [
            new Argument(
                name: 'destination',
                description: 'Destinasi atau tema wisata yang diminati (contoh: "Derawan", "IKN Nusantara", "Turki", "Jepang").',
                required: false,
            ),
            new Argument(
                name: 'pax',
                description: 'Jumlah peserta perjalanan (contoh: 2, 4, 10). Default: 2.',
                required: false,
            ),
            new Argument(
                name: 'duration_days',
                description: 'Preferensi durasi perjalanan dalam hari (contoh: 3, 4, 7).',
                required: false,
            ),
            new Argument(
                name: 'budget',
                description: 'Kisaran anggaran per orang atau total (contoh: "Hemat", "Premium", "Rp 5.000.000/pax").',
                required: false,
            ),
            new Argument(
                name: 'tour_type',
                description: 'Tipe tur: "domestic" atau "international".',
                required: false,
            ),
            new Argument(
                name: 'language',
                description: 'Bahasa respons ("id", "en", "ms"). Default: "id".',
                required: false,
            ),
        ];
    }
}
