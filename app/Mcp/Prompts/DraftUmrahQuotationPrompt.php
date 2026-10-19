<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use App\Models\UmrahDeparture;
use App\Models\UmrahInclude;
use App\Models\UmrahPackage;
use App\Models\UmrahPackageItinerary;
use App\Models\UmrahPackagePrice;
use App\Services\CurrencyService;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('draft_umrah_quotation')]
#[Title('Susun Draf Penawaran Resmi Paket Umrah (Umrah Quotation)')]
#[Description('Panduan bagi AI untuk menyusun dokumen penawaran resmi program ibadah Umrah lengkap dengan rincian jadwal penerbangan, hotel Makkah/Madinah, tarif per tipe kamar, fasilitas include/exclude, dan persyaratan pendaftaran.')]
class DraftUmrahQuotationPrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $packageId = (int) ($request->get('umrah_package_id') ?? $request->get('package_id', 0));
        $depId = (int) $request->get('departure_id', 0);
        $customerName = (string) ($request->get('customer_name') ?? $request->get('client_name', 'Bapak/Ibu Calon Jamaah Yth.'));
        $pax = max(1, (int) $request->get('pax', 2));
        $roomTypeFilter = strtolower(trim((string) $request->get('room_type', '')));
        $targetCurrency = strtoupper(trim((string) $request->get('currency', 'IDR')));
        $language = (string) $request->get('language', 'id');

        $package = $packageId > 0
            ? UmrahPackage::with(['upcomingDepartures', 'prices', 'itineraries', 'includes', 'destinations'])->find($packageId)
            : UmrahPackage::with(['upcomingDepartures', 'prices', 'itineraries', 'includes', 'destinations'])->first();

        if (! $package) {
            return Response::text('Data paket umrah belum ditemukan di database. Gunakan tool `list_umrah_packages` untuk melihat ID paket yang tersedia.');
        }

        $departure = $depId > 0
            ? UmrahDeparture::where('package_id', $package->id)->find($depId)
            : $package->upcomingDepartures->first();

        $currencyService = app(CurrencyService::class);
        $pkgName = $package->getTranslation('name', $language, false) ?: $package->getTranslation('name', 'id', false);

        $depDateFormatted = $departure
            ? $departure->departure_date?->translatedFormat('d F Y')
            : 'Sesuai Request Jamaah / Jadwal Mendatang';
        $returnDateFormatted = $departure?->return_date
            ? $departure->return_date->translatedFormat('d F Y')
            : '-';

        $roomPrices = $package->prices;
        if (! empty($roomTypeFilter)) {
            $filtered = $roomPrices->where('room_type', $roomTypeFilter);
            if ($filtered->isNotEmpty()) {
                $roomPrices = $filtered;
            }
        }

        $pricingRows = $roomPrices->map(function (UmrahPackagePrice $price) use ($package, $departure, $pax, $targetCurrency, $currencyService): string {
            $unitPrice = $package->getPriceForDeparture($departure, $price);
            $total = $unitPrice * $pax;
            $formattedUnit = $currencyService->convert($unitPrice, $targetCurrency, 'IDR');
            $formattedTotal = $currencyService->convert($total, $targetCurrency, 'IDR');
            $roomName = ucfirst($price->room_type);

            return "- **Kamar {$roomName}**: {$formattedUnit} / orang | **Total ({$pax} Pax): {$formattedTotal}**";
        })->implode("\n");

        if (empty($pricingRows)) {
            $unitPrice = $package->getPriceForDeparture($departure);
            $total = $unitPrice * $pax;
            $formattedUnit = $currencyService->convert($unitPrice, $targetCurrency, 'IDR');
            $formattedTotal = $currencyService->convert($total, $targetCurrency, 'IDR');
            $pricingRows = "- **Tarif Dasar**: {$formattedUnit} / orang | **Total ({$pax} Pax): {$formattedTotal}**";
        }

        $makkahHotel = $package->hotel_makkah ? "{$package->hotel_makkah} (Bintang {$package->hotel_makkah_stars})" : 'Hotel Bintang di Makkah';
        $madinahHotel = $package->hotel_madinah ? "{$package->hotel_madinah} (Bintang {$package->hotel_madinah_stars})" : 'Hotel Bintang di Madinah';
        $airlineInfo = $package->airline ?: 'Penerbangan Internasional PP';

        $itinerarySummary = $package->itineraries->map(function (UmrahPackageItinerary $it) use ($language): string {
            $title = $it->getTranslation('title', $language, false) ?: $it->getTranslation('title', 'id', false);
            $loc = $it->getTranslation('location', $language, false) ?: $it->getTranslation('location', 'id', false);
            $desc = strip_tags((string) ($it->getTranslation('description', $language, false) ?: $it->getTranslation('description', 'id', false)));
            $locInfo = ! empty($loc) ? " [{$loc}]" : '';

            return "- **Hari ke-{$it->day_number}: {$title}**{$locInfo}\n  {$desc}";
        })->implode("\n");

        $includesList = $package->includes->where('type', 'include')
            ->map(fn (UmrahInclude $i) => '  + '.($i->getTranslation('item', $language, false) ?: $i->getTranslation('item', 'id', false)))
            ->implode("\n");

        $excludesList = $package->includes->where('type', 'exclude')
            ->map(fn (UmrahInclude $i) => '  - '.($i->getTranslation('item', $language, false) ?: $i->getTranslation('item', 'id', false)))
            ->implode("\n");

        $requirementsList = $package->includes->where('type', 'requirement')
            ->map(fn (UmrahInclude $i) => '  * '.($i->getTranslation('item', $language, false) ?: $i->getTranslation('item', 'id', false)))
            ->implode("\n");

        $promptText = <<<PROMPT
Anda adalah Senior Umrah Quotation Specialist di **Baharsyah Jelajah**. Susunlah draf penawaran resmi ibadah Umrah yang profesional, syar'i, tertata rapi, dan siap dikirimkan kepada calon jamaah.

### Data Calon Jamaah:
- **Penerima Penawaran**: {$customerName}
- **Program Umrah**: {$pkgName} ({$package->duration_days} Hari)
- **Jadwal Keberangkatan**: {$depDateFormatted} s/d {$returnDateFormatted}
- **Jumlah Jamaah**: {$pax} Orang (Pax)
- **Bahasa Surat**: {$language}

### Fasilitas Akomodasi & Penerbangan:
- **Maskapai**: {$airlineInfo}
- **Hotel Makkah**: {$makkahHotel}
- **Hotel Madinah**: {$madinahHotel}

### Rincian Biaya Paket ({$pax} Pax):
{$pricingRows}

### Agenda Perjalanan Singkat:
{$itinerarySummary}

### Fasilitas Termasuk (Includes):
{$includesList}

### Fasilitas Belum Termasuk (Excludes):
{$excludesList}

### Persyaratan Pendaftaran (Requirements):
{$requirementsList}

### Format Output Surat Penawaran:
1. Pembuka dengan salam Islami hangat kepada **{$customerName}**.
2. Rangkuman program umrah, tanggal keberangkatan, maskapai, dan hotel di Tanah Suci.
3. Tabel/poin rincian biaya per jamaah dan total untuk **{$pax} orang**.
4. Ringkasan jadwal ibadah (Ziarah Madinah, Umrah 1 & 2 di Makkah).
5. Fasilitas yang sudah termasuk, belum termasuk, dan berkas persyaratan pendaftaran.
6. Langkah konfirmasi pemesanan seat (DP/Booking) dan rekening resmi Baharsyah Jelajah.
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
                name: 'umrah_package_id',
                description: 'ID paket umrah yang ingin dibuatkan penawaran. Wajib.',
                required: true,
            ),
            new Argument(
                name: 'departure_id',
                description: 'ID jadwal keberangkatan spesifik.',
                required: false,
            ),
            new Argument(
                name: 'customer_name',
                description: 'Nama calon jamaah atau kepala rombongan (contoh: "Bapak H. Sukardi & Keluarga").',
                required: false,
            ),
            new Argument(
                name: 'pax',
                description: 'Jumlah jamaah (contoh: 2, 4, 15). Default: 2.',
                required: false,
            ),
            new Argument(
                name: 'room_type',
                description: 'Filter tipe kamar khusus: "quad", "triple", "double", atau "single".',
                required: false,
            ),
            new Argument(
                name: 'currency',
                description: 'Mata uang penawaran: "IDR", "USD", "SAR", atau "MYR". Default: "IDR".',
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
