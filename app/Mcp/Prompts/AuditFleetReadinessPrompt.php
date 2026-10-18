<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use App\Enums\VehicleCategory;
use App\Models\Vehicle;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('audit_fleet_readiness')]
#[Title('Audit Kesiapan Data & Tarif Armada Sewa')]
#[Description('Panduan prompt bagi AI untuk mengaudit kelengkapan data armada: tarif sewa harian/trip, biaya overtime, kapasitas penumpang & bagasi, ketersediaan foto armada, dan tarif wilayah aktif.')]
class AuditFleetReadinessPrompt extends Prompt
{
    /**
     * Handle the prompt request.
     */
    public function handle(Request $request): Response
    {
        $rawCategory = $request->get('category');

        $query = Vehicle::query()
            ->with(['countries', 'rentalRates'])
            ->withCount('rentalRates')
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('id');

        if (! empty($rawCategory)) {
            $cat = VehicleCategory::tryFrom((string) $rawCategory);
            if ($cat) {
                $query->where('category', $cat);
            }
        }

        $vehicles = $query->get();

        $vehicleList = $vehicles->map(function (Vehicle $v): string {
            $nameId = $v->getTranslation('name', 'id', false) ?: '-';
            $cat = $v->category?->getLabel() ?: 'Umum';
            $pax = $v->capacity_pax ? "{$v->capacity_pax} Pax" : 'Pax Kosong';
            $luggage = $v->capacity_luggage !== null ? "{$v->capacity_luggage} Bagasi" : 'Bagasi Kosong';
            $dayPrice = $v->price_per_day_idr ? 'Rp '.number_format($v->price_per_day_idr, 0, ',', '.') : 'Harga Harian Kosong';
            $overtime = $v->overtime_rate_idr ? 'Rp '.number_format($v->overtime_rate_idr, 0, ',', '.') : 'Overtime Kosong';
            $ratesCount = $v->rental_rates_count > 0 ? "{$v->rental_rates_count} tarif wilayah" : 'Tanpa Tarif Wilayah';
            $hasCover = ! empty($v->thumbnail_url) ? 'Ada Foto' : 'Foto Kosong';

            return "- ID {$v->id} [{$v->catalog_code}] \"{$nameId}\" ({$cat}) | {$pax}, {$luggage} | {$dayPrice} | Overtime: {$overtime} | {$ratesCount} | {$hasCover}";
        })->implode("\n");

        if (empty($vehicleList)) {
            $vehicleList = 'Tidak ditemukan armada aktif yang cocok dengan kriteria audit.';
        }

        $instruction = <<<PROMPT
Anda adalah Fleet Operations & Quality Assurance Lead untuk platform sewa kendaraan Baharsyah Jelajah.

Tugas Anda adalah mengaudit kesiapan operasional dan kelengkapan data armada saat ini:

### Data Armada yang Diaudit:
{$vehicleList}

### Aspek Pemeriksaan Wajib:
1. **Kepastian Tarif Sewa & Overtime**:
   - Identifikasi kendaraan yang belum mengisi `price_per_day_idr` atau `overtime_rate_idr`.
2. **Keterisian Tarif Wilayah Operasional (VehicleRentalRate)**:
   - Temukan armada yang belum memiliki tarif khusus per wilayah (misal: tarif sewa di Balikpapan vs Samarinda vs IKN).
3. **Kelengkapan Spesifikasi Fisik**:
   - Pastikan kapasitas penumpang (`capacity_pax`) dan bagasi (`capacity_luggage`) terisi realistis sesuai jenis armada.
4. **Ketersediaan Foto / Media Aset**:
   - Pastikan setiap kendaraan memiliki foto cover atau thumbnail representatif untuk katalog web.
5. **Rekomendasi Pembaruan**:
   - Berikan rekomendasi pembaruan langsung menggunakan tool `update_vehicle` untuk melengkapi tarif atau spesifikasi yang belum lengkap.
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
                name: 'category',
                description: 'Filter kategori kendaraan untuk audit (contoh: "car", "minibus", "bus"). Kosongkan untuk mengaudit semua kategori.',
                required: false,
            ),
        ];
    }
}
