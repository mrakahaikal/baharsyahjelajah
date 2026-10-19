<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use App\Models\Destination;
use App\Models\PackageTier;
use App\Models\TourPackage;
use App\Models\TourPackageInclude;
use App\Models\TourPackageItinerary;
use App\Services\CurrencyService;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('draft_tour_quotation')]
#[Title('Susun Draf Penawaran Resmi Paket Wisata (Tour Quotation)')]
#[Description('Panduan bagi AI untuk menyusun dokumen penawaran resmi (quotation) paket wisata lengkap dengan rincian harga per pax, total biaya, jadwal itinerary harian, dan syarat fasilitas siap kirim ke WhatsApp atau Email.')]
class DraftTourQuotationPrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $packageId = (int) $request->get('tour_package_id', 0);
        $customerName = (string) $request->get('customer_name', 'Bapak/Ibu Pelanggan Yth.');
        $pax = max(1, (int) $request->get('pax', 2));
        $tierNameFilter = (string) $request->get('tier_name', '');
        $targetCurrency = strtoupper(trim((string) $request->get('currency', '')));
        $language = (string) $request->get('language', 'id');

        $package = $packageId > 0
            ? TourPackage::with(['tour.category', 'destinations', 'tiers.priceTiers', 'itineraries.destinations', 'includes'])->find($packageId)
            : TourPackage::with(['tour.category', 'destinations', 'tiers.priceTiers', 'itineraries.destinations', 'includes'])->first();

        if (! $package) {
            return Response::text('Data paket wisata belum ditemukan di database. Gunakan tool `list_tour_packages` untuk melihat ID paket yang tersedia.');
        }

        $tourName = $package->tour?->getTranslation('name', $language, false) ?: $package->tour?->getTranslation('name', 'id', false) ?? 'Paket Wisata';
        $pkgName = $package->getTranslation('name', $language, false) ?: $package->getTranslation('name', 'id', false);
        $currencyService = app(CurrencyService::class);

        $tiers = $package->tiers;
        if (! empty($tierNameFilter)) {
            $filtered = $tiers->filter(function (PackageTier $t) use ($tierNameFilter): bool {
                $tName = strtolower((string) ($t->getTranslation('name', 'id', false) ?: ''));

                return str_contains($tName, strtolower($tierNameFilter));
            });
            if ($filtered->isNotEmpty()) {
                $tiers = $filtered;
            }
        }

        $pricingSummary = $tiers->map(function (PackageTier $tier) use ($pax, $language, $targetCurrency, $currencyService): string {
            $tName = $tier->getTranslation('name', $language, false) ?: $tier->getTranslation('name', 'id', false);
            $stars = $tier->hotel_stars ? " (Hotel Bintang {$tier->hotel_stars})" : '';
            $pt = $tier->priceTierForPax($pax) ?? $tier->priceTiers->first();

            if (! $pt) {
                return "- **{$tName}{$stars}**: Harga khusus grup (Hubungi Admin)";
            }

            $outCurr = ! empty($targetCurrency) ? $targetCurrency : $pt->currency;
            $perPaxFormatted = $currencyService->convert((float) $pt->price, $outCurr, $pt->currency);
            $totalFormatted = $currencyService->convert((float) $pt->price * $pax, $outCurr, $pt->currency);

            return "- **{$tName}{$stars}**: {$perPaxFormatted} / pax | **Total ({$pax} Pax): {$totalFormatted}**";
        })->implode("\n");

        $itinerarySummary = $package->itineraries->map(function (TourPackageItinerary $it) use ($language): string {
            $title = $it->getTranslation('title', $language, false) ?: $it->getTranslation('title', 'id', false);
            $desc = strip_tags((string) ($it->getTranslation('description', $language, false) ?: $it->getTranslation('description', 'id', false)));
            $dests = $it->destinations->map(fn (Destination $d) => $d->getTranslation('name', $language, false) ?: $d->getTranslation('name', 'id', false))->implode(', ');
            $destInfo = ! empty($dests) ? " [Destinasi: {$dests}]" : '';

            return "- **Hari ke-{$it->day_number}: {$title}**{$destInfo}\n  {$desc}";
        })->implode("\n");

        $includesList = $package->includes->where('type', 'include')
            ->map(fn (TourPackageInclude $i) => '  + '.($i->getTranslation('item', $language, false) ?: $i->getTranslation('item', 'id', false)))
            ->implode("\n");

        $excludesList = $package->includes->where('type', 'exclude')
            ->map(fn (TourPackageInclude $i) => '  - '.($i->getTranslation('item', $language, false) ?: $i->getTranslation('item', 'id', false)))
            ->implode("\n");

        $promptText = <<<PROMPT
Anda adalah Senior Sales & Quotation Specialist di **Baharsyah Jelajah**. Susunlah draf surat penawaran (Tour Quotation) yang rapi, ramah, dan siap dikirimkan kepada klien.

### Data Penawaran:
- **Kepada**: {$customerName}
- **Program Tur**: {$tourName} — {$pkgName}
- **Durasi**: {$package->duration_label}
- **Jumlah Peserta**: {$pax} Orang (Pax)
- **Bahasa Output**: {$language}

### Estimasi Harga Paket ({$pax} Pax):
{$pricingSummary}

### Ringkasan Rencana Perjalanan (Itinerary):
{$itinerarySummary}

### Fasilitas Termasuk (Includes):
{$includesList}

### Fasilitas Tidak Termasuk (Excludes):
{$excludesList}

### Instruksi Format Output:
1. Buat pembuka yang sopan dan hangat kepada **{$customerName}**.
2. Sajikan detail program tur, durasi, dan tabel/poin harga per pax beserta total untuk **{$pax} orang**.
3. Rangkum jadwal perjalanan harian (Day-by-Day Itinerary) secara menarik.
4. Cantumkan rincian fasilitas yang sudah termasuk dan belum termasuk dengan jelas.
5. Tutup dengan langkah konfirmasi pemesanan (booking) dan kontak tim Baharsyah Jelajah.
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
                name: 'tour_package_id',
                description: 'ID numerik paket tur (TourPackage) yang akan dibuatkan penawaran. Wajib.',
                required: true,
            ),
            new Argument(
                name: 'customer_name',
                description: 'Nama klien atau instansi penerima penawaran (contoh: "Bapak Ahmad & Keluarga", "PT Nusantara Jaya").',
                required: false,
            ),
            new Argument(
                name: 'pax',
                description: 'Jumlah peserta perjalanan (contoh: 2, 6, 15). Default: 2.',
                required: false,
            ),
            new Argument(
                name: 'tier_name',
                description: 'Filter nama tier spesifik jika diinginkan (contoh: "Silver", "Gold", "Platinum").',
                required: false,
            ),
            new Argument(
                name: 'currency',
                description: 'Kode mata uang konversi 3 huruf (contoh: "IDR", "MYR", "USD").',
                required: false,
            ),
            new Argument(
                name: 'language',
                description: 'Bahasa penawaran ("id", "en", "ms"). Default: "id".',
                required: false,
            ),
        ];
    }
}
