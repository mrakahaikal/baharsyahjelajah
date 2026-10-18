<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use App\Models\Vehicle;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('recommend_vehicle_for_trip')]
#[Title('Rekomendasi Armada Transportasi & Rute Perjalanan')]
#[Description('Panduan prompt bagi AI untuk merekomendasikan pilihan armada transportasi terbaik berdasarkan jumlah penumpang, kapasitas bagasi, rute perjalanan (misal: Balikpapan - Samarinda - IKN), dan durasi sewa.')]
class RecommendVehicleForTripPrompt extends Prompt
{
    /**
     * Handle the prompt request.
     */
    public function handle(Request $request): Response
    {
        $passengers = max(1, (int) $request->get('passenger_count', 4));
        $luggage = (int) $request->get('luggage_count', 2);
        $route = (string) $request->get('destination_or_route', 'Perjalanan Wisata & Operasional (Balikpapan - IKN Nusantara)');
        $duration = max(1, (int) $request->get('duration_days', 1));

        // Find candidate vehicles matching passenger capacity
        $candidates = Vehicle::query()
            ->active()
            ->where('capacity_pax', '>=', $passengers)
            ->orderBy('capacity_pax')
            ->take(6)
            ->get();

        $candidateContext = '';
        if ($candidates->isNotEmpty()) {
            $fleetList = $candidates->map(function (Vehicle $v): string {
                $name = $v->getTranslation('name', 'id', false);
                $pax = $v->capacity_pax;
                $luggage = $v->capacity_luggage;
                $dayPrice = $v->price_per_day_idr ? 'Rp '.number_format($v->price_per_day_idr, 0, ',', '.') : 'Hubungi Admin';
                $overtime = $v->overtime_rate_idr ? 'Rp '.number_format($v->overtime_rate_idr, 0, ',', '.').'/jam' : 'Sesuai S&K';
                $ac = $v->has_ac ? 'AC' : 'Non-AC';
                $trans = $v->transmission;

                return "- [{$v->catalog_code}] **{$name}** ({$v->category?->getLabel()}) | Kapasitas: {$pax} Pax, {$luggage} Koper | Transmisi: {$trans}, {$ac} | Sewa Harian: {$dayPrice} | Overtime: {$overtime}";
            })->implode("\n");

            $candidateContext = <<<INFO
### Pilihan Armada yang Tersedia untuk Kapasitas {$passengers}+ Pax:
{$fleetList}
INFO;
        } else {
            $candidateContext = "Catatan: Tidak ditemukan armada aktif tunggal dengan kapasitas minimum {$passengers} penumpang. Pertimbangkan membagi penumpang ke beberapa armada (konvoi minibus/mobil).";
        }

        $instruction = <<<PROMPT
Anda adalah Senior Fleet & Transport Logistic Advisor di Baharsyah Jelajah (spesialis transportasi darat Kalimantan Timur, IKN Nusantara, dan rute lintas kota).

{$candidateContext}

### Parameter Kebutuhan Sewa:
- **Jumlah Penumpang**: {$passengers} Orang
- **Estimasi Bagasi**: {$luggage} Koper/Tas Besar
- **Rute / Tujuan**: {$route}
- **Durasi Sewa**: {$duration} Hari

### Format Rekomendasi yang Wajib Disusun:
1. **Rekomendasi Armada Paling Ideal**:
   - Pilih armada yang paling seimbang dari segi kenyamanan ruang kaki, kapasitas bagasi yang memadai, dan efisiensi biaya.
2. **Kalkulasi Estimasi Biaya Sewa**:
   - Hitung total estimasi biaya sewa dasar ({$duration} hari x tarif sewa harian).
   - Berikan informasi mengenai biaya overtime per jam dan ketentuan jam operasional standar (12 jam/hari).
3. **Analisis Rute & Medan Jalan**:
   - Berikan saran khusus mengenai karakteristik rute yang dituju (misal: rute tol Balikpapan-Samarinda, jalan darat ke IKN Nusantara, tanjakan/belokan perbukitan).
4. **Fasilitas Tambahan & Layanan Driver**:
   - Jelaskan bahwa seluruh sewa armada di Baharsyah Jelajah telah didampingi oleh driver profesional, ramah, dan berpengalaman di medan lokal.
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
                name: 'passenger_count',
                description: 'Jumlah penumpang yang akan ikut dalam perjalanan (contoh: 4, 10, 25). Wajib.',
                required: true,
            ),
            new Argument(
                name: 'luggage_count',
                description: 'Estimasi jumlah koper atau bagasi bawaan (contoh: 3, 6, 15).',
                required: false,
            ),
            new Argument(
                name: 'destination_or_route',
                description: 'Rute atau destinasi yang dituju (contoh: "City Tour Samarinda", "Balikpapan - Sepaku IKN Nusantara", "Berau - Biduk-biduk").',
                required: false,
            ),
            new Argument(
                name: 'duration_days',
                description: 'Durasi hari sewa kendaraan (contoh: 1, 3, 7). Default: 1.',
                required: false,
            ),
        ];
    }
}
