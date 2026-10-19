<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use App\Models\UmrahPackage;
use App\Models\UmrahPackagePrice;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('consult_umrah_package')]
#[Title('Konsultasi & Rekomendasi Paket Umrah (Umrah Advisor)')]
#[Description('Panduan bagi AI Konsultan Umrah untuk merekomendasikan paket ibadah dan jadwal keberangkatan terbaik berdasarkan preferensi bulan keberangkatan, tipe kamar (Quad/Triple/Double), kelas hotel, dan anggaran jamaah.')]
class ConsultUmrahPackagePrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $month = (string) $request->get('month', 'Bulan Mendatang');
        $pax = max(1, (int) $request->get('pax', 2));
        $roomType = (string) $request->get('room_type', 'quad');
        $hotelStars = (int) $request->get('hotel_stars', 0);
        $packageType = (string) $request->get('package_type', '');
        $rawBudget = $request->get('budget') ?? $request->get('budget_per_person');
        $budget = is_numeric($rawBudget)
            ? 'Rp '.number_format((float) $rawBudget, 0, ',', '.')
            : (string) ($rawBudget ?: 'Fleksibel');
        $language = (string) $request->get('language', 'id');

        $query = UmrahPackage::query()
            ->active()
            ->with(['prices', 'upcomingDepartures']);

        if (! empty($packageType)) {
            $query->where('package_type', $packageType);
        }

        if ($hotelStars > 0) {
            $query->where(function ($q) use ($hotelStars): void {
                $q->where('hotel_makkah_stars', '>=', $hotelStars)
                    ->orWhere('hotel_madinah_stars', '>=', $hotelStars);
            });
        }

        $packages = $query->orderByDesc('is_featured')->take(6)->get();

        $catalogSummary = $packages->map(function (UmrahPackage $pkg): string {
            $name = $pkg->getTranslation('name', 'id', false);
            $type = $pkg->package_type_label;
            $airline = $pkg->airline ?: 'Penerbangan Reguler';
            $makkah = $pkg->hotel_makkah ? "{$pkg->hotel_makkah} (★{$pkg->hotel_makkah_stars})" : 'Hotel Standar Makkah';
            $madinah = $pkg->hotel_madinah ? "{$pkg->hotel_madinah} (★{$pkg->hotel_madinah_stars})" : 'Hotel Standar Madinah';

            $roomInfo = $pkg->prices->map(fn (UmrahPackagePrice $p) => "{$p->room_type}: {$p->formatted_price}")->implode(', ');
            if (empty($roomInfo)) {
                $roomInfo = "Mulai {$pkg->formatted_price}";
            }

            $depInfo = $pkg->upcomingDepartures->take(3)->map(function ($d): string {
                $date = $d->departure_date?->translatedFormat('d M Y') ?? '-';

                return "{$date} [Sisa Kuota: {$d->quota_sisa}]";
            })->implode(', ');

            if (empty($depInfo)) {
                $depInfo = 'Jadwal baru segera dibuka';
            }

            return <<<ITEM
- **[ID: {$pkg->id}] {$name}** ({$type} — {$pkg->duration_days} Hari)
  * Penerbangan: {$airline} | Hotel: {$makkah} & {$madinah}
  * Pilihan Kamar: {$roomInfo}
  * Jadwal Tersedia: {$depInfo}
ITEM;
        })->implode("\n\n");

        if (empty($catalogSummary)) {
            $catalogSummary = 'Saat ini belum ada data paket umrah yang cocok dengan kriteria filter.';
        }

        $promptText = <<<PROMPT
Anda adalah Senior Umrah Consultant & Pembimbing Ibadah di **Baharsyah Jelajah**. Tugas Anda adalah memberikan konsultasi ibadah umrah yang hangat, amanah, profesional, dan memberikan rekomendasi terbaik sesuai kebutuhan calon jamaah.

### Parameter Kebutuhan Calon Jamaah:
- **Perkiraan Waktu / Bulan**: {$month}
- **Jumlah Jamaah**: {$pax} Orang
- **Preferensi Kamar**: Tipe {$roomType}
- **Standar Hotel**: Minimal {$hotelStars} Bintang (0 = Bebas)
- **Kategori Paket**: {$packageType}
- **Anggaran**: {$budget}
- **Bahasa Komunikasi**: {$language}

### Paket Umrah yang Tersedia:
{$catalogSummary}

### Panduan Penyusunan Rekomendasi:
1. **Rekomendasi Utama (1-2 Paket Terbaik)**:
   - Pilih paket yang paling seimbang antara kedekatan hotel ke Masjidil Haram/Masjid Nabawi, maskapai (direct flight jika ada), dan anggaran.
   - Informasikan jadwal keberangkatan yang masih memiliki kuota kosong.
2. **Penjelasan Rincian Biaya per Kamar**:
   - Jelaskan perbedaan biaya kamar Quad (sekamar ber-4), Triple (ber-3), Double (ber-2), atau Single (sendiri).
   - Informasikan bahwa harga sudah mencakup visa umrah dan handling perlengkapan.
3. **Poin Plus Layanan Baharsyah Jelajah**:
   - Manasik umrah intensif sebelum keberangkatan, bimbingan Muthawwif berpengalaman sesuai Sunnah, dan audio transmitter mutawwif saat thawaf/sai.
4. **Call to Action (CTA)**:
   - Ajak calon jamaah mengamankan seat/kuota dengan DP atau tawarkan pembuatan surat penawaran resmi (Quotation) untuk keluarga/grup.
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
                name: 'month',
                description: 'Bulan rencana keberangkatan (contoh: "Ramadan", "Oktober 2026", "Desember (Liburan)").',
                required: false,
            ),
            new Argument(
                name: 'pax',
                description: 'Jumlah calon jamaah yang berangkat (contoh: 2, 4, 10). Default: 2.',
                required: false,
            ),
            new Argument(
                name: 'room_type',
                description: 'Pilihan tipe kamar: "quad", "triple", "double", atau "single". Default: "quad".',
                required: false,
            ),
            new Argument(
                name: 'hotel_stars',
                description: 'Preferensi bintang hotel minimal (3, 4, atau 5).',
                required: false,
            ),
            new Argument(
                name: 'package_type',
                description: 'Kategori paket: "regular", "plus", "vip", "ramadan", atau "private".',
                required: false,
            ),
            new Argument(
                name: 'budget',
                description: 'Kisaran anggaran per jamaah (contoh: "Rp 30-35 juta/orang").',
                required: false,
            ),
            new Argument(
                name: 'language',
                description: 'Bahasa pengantar ("id", "en", "ms"). Default: "id".',
                required: false,
            ),
        ];
    }
}
