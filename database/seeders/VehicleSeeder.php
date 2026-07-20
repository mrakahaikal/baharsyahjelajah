<?php

namespace Database\Seeders;

use App\Enums\VehicleCategory;
use App\Enums\VehicleRentalTermType;
use App\Models\Vehicle;
use App\Models\VehicleRentalArea;
use App\Models\VehicleRentalRate;
use App\Models\VehicleRentalTerm;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $areas = $this->seedAreas();
        $vehicles = $this->seedVehicles();

        $this->seedRates($areas, $vehicles);
        $this->seedTerms();
        $this->deactivateObsoleteDummyVehicles();
    }

    /** @return array<string, VehicleRentalArea> */
    private function seedAreas(): array
    {
        $areas = [
            ['slug' => 'jakarta', 'name' => 'Jakarta', 'minimum' => 1],
            ['slug' => 'bandung', 'name' => 'Bandung', 'minimum' => 1],
            ['slug' => 'yogyakarta', 'name' => 'Yogyakarta', 'minimum' => 1],
            ['slug' => 'malang', 'name' => 'Malang', 'minimum' => 5],
            ['slug' => 'bromo-banyuwangi', 'name' => 'Bromo / Banyuwangi', 'minimum' => 6],
        ];

        return collect($areas)->mapWithKeys(function (array $area, int $index): array {
            $record = VehicleRentalArea::query()->updateOrCreate(
                ['slug' => $area['slug']],
                [
                    'name' => $this->translations($area['name']),
                    'description' => [
                        'id' => 'Wilayah layanan sewa kendaraan '.$area['name'].'.',
                        'en' => 'Vehicle rental service area for '.$area['name'].'.',
                        'ms' => 'Kawasan perkhidmatan sewa kenderaan '.$area['name'].'.',
                    ],
                    'minimum_rental_days' => $area['minimum'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );

            return [$area['slug'] => $record];
        })->all();
    }

    /** @return array<string, Vehicle> */
    private function seedVehicles(): array
    {
        $vehicles = [
            ['code' => 'innova-reborn-6-seat', 'name' => 'Innova Reborn 6 Seat', 'brand' => 'Toyota', 'model' => 'Innova Reborn', 'category' => VehicleCategory::Car, 'capacity' => 6, 'overtime' => 115000],
            ['code' => 'fortuner-pajero', 'name' => 'Fortuner / Pajero', 'brand' => null, 'model' => null, 'category' => VehicleCategory::Car],
            ['code' => 'innova-zenix-q', 'name' => 'Innova Zenix Q', 'brand' => 'Toyota', 'model' => 'Innova Zenix Q', 'category' => VehicleCategory::Car, 'overtime' => 115000],
            ['code' => 'innova-zenix-g', 'name' => 'Innova Zenix G', 'brand' => 'Toyota', 'model' => 'Innova Zenix G', 'category' => VehicleCategory::Car, 'overtime' => 115000],
            ['code' => 'innova-reborn', 'name' => 'Innova Reborn', 'brand' => 'Toyota', 'model' => 'Innova Reborn', 'category' => VehicleCategory::Car, 'overtime' => 115000, 'legacy_model' => 'Innova Reborn'],
            ['code' => 'new-avanza', 'name' => 'New Avanza', 'brand' => 'Toyota', 'model' => 'Avanza', 'category' => VehicleCategory::Car, 'legacy_model' => 'Avanza'],
            ['code' => 'brv-xpander', 'name' => 'BR-V / Xpander', 'brand' => null, 'model' => null, 'category' => VehicleCategory::Car],
            ['code' => 'avanza-xenia-mobilio', 'name' => 'Avanza / Xenia / Mobilio', 'brand' => null, 'model' => null, 'category' => VehicleCategory::Car],
            ['code' => 'brio', 'name' => 'Brio', 'brand' => 'Honda', 'model' => 'Brio', 'category' => VehicleCategory::Car],
            ['code' => 'hiace-commuter', 'name' => 'Hiace Commuter', 'brand' => 'Toyota', 'model' => 'HiAce Commuter', 'category' => VehicleCategory::Minibus, 'capacity' => 14, 'capacity_label' => '14+1 seat', 'overtime' => 172500, 'legacy_model' => 'HiAce Commuter'],
            ['code' => 'hiace-premio', 'name' => 'Hiace Premio', 'brand' => 'Toyota', 'model' => 'Hiace Premio', 'category' => VehicleCategory::Minibus, 'overtime' => 172500],
            ['code' => 'hiace-premio-luxury', 'name' => 'Hiace Premio Luxury', 'brand' => 'Toyota', 'model' => 'Hiace Premio Luxury', 'category' => VehicleCategory::Minibus, 'overtime' => 172500],
            ['code' => 'elf-19-seat', 'name' => 'Elf 19 Seat', 'brand' => 'Isuzu', 'model' => 'Elf', 'category' => VehicleCategory::Minibus, 'capacity' => 19, 'overtime' => 230000],
            ['code' => 'elf-premium-19-seat', 'name' => 'Elf Premium 19 Seat', 'brand' => 'Isuzu', 'model' => 'Elf Premium', 'category' => VehicleCategory::Minibus, 'capacity' => 19, 'overtime' => 230000],
            ['code' => 'new-giga-2025', 'name' => 'New Giga 2025', 'brand' => 'Isuzu', 'model' => 'Giga', 'category' => VehicleCategory::Minibus, 'capacity' => 19, 'year' => 2025, 'overtime' => 230000],
            ['code' => 'medium-bus-2025', 'name' => 'Medium Bus 2025', 'brand' => null, 'model' => null, 'category' => VehicleCategory::Bus, 'capacity' => 35, 'year' => 2025, 'overtime' => 230000],
            ['code' => 'big-bus-2025', 'name' => 'Big Bus 2025', 'brand' => null, 'model' => null, 'category' => VehicleCategory::Bus, 'capacity' => 50, 'year' => 2025, 'overtime' => 230000],
            ['code' => 'big-bus-2017-2018', 'name' => 'Big Bus 2017 / 2018', 'brand' => null, 'model' => null, 'category' => VehicleCategory::Bus, 'capacity' => 59, 'overtime' => 230000],
        ];

        return collect($vehicles)->mapWithKeys(function (array $data, int $index): array {
            $record = Vehicle::query()->where('catalog_code', $data['code'])->first();

            if (! $record && isset($data['legacy_model'])) {
                $record = Vehicle::query()
                    ->whereNull('catalog_code')
                    ->where('model', $data['legacy_model'])
                    ->first();
            }

            $record ??= new Vehicle;
            $record->fill([
                'catalog_code' => $data['code'],
                'name' => $this->translations($data['name']),
                'slug' => $this->translations(Str::slug($data['name'])),
                'description' => $this->vehicleDescription($data['category']),
                'brand' => $data['brand'],
                'model' => $data['model'],
                'year' => $data['year'] ?? null,
                'category' => $data['category'],
                'capacity_pax' => $data['capacity'] ?? null,
                'capacity_label' => isset($data['capacity_label']) ? $this->translations($data['capacity_label']) : null,
                'capacity_luggage' => null,
                'transmission' => null,
                'has_ac' => true,
                'has_wifi' => false,
                'features' => ['id' => [], 'en' => [], 'ms' => []],
                'overtime_rate_idr' => $data['overtime'] ?? null,
                'is_active' => true,
                'is_featured' => $index < 6,
                'sort_order' => $index + 1,
                'price_per_day_idr' => null,
                'price_per_trip_idr' => null,
            ])->save();

            return [$data['code'] => $record];
        })->all();
    }

    /**
     * @param  array<string, VehicleRentalArea>  $areas
     * @param  array<string, Vehicle>  $vehicles
     */
    private function seedRates(array $areas, array $vehicles): void
    {
        $rates = [
            'jakarta' => [
                'innova-reborn-6-seat' => 1150000, 'fortuner-pajero' => 1725000, 'innova-zenix-q' => 1725000,
                'innova-zenix-g' => 1265000, 'innova-reborn' => 1092500, 'new-avanza' => 977500,
                'brv-xpander' => 977500, 'avanza-xenia-mobilio' => 805000, 'brio' => 805000,
                'hiace-commuter' => 1610000, 'hiace-premio' => 2070000, 'hiace-premio-luxury' => 2875000,
                'elf-19-seat' => 1495000, 'elf-premium-19-seat' => 1840000, 'new-giga-2025' => 2300000,
                'medium-bus-2025' => 3795000, 'big-bus-2025' => 5520000, 'big-bus-2017-2018' => 4370000,
            ],
            'bandung' => [
                'innova-reborn-6-seat' => 1495000, 'fortuner-pajero' => 1725000, 'innova-zenix-q' => 1725000,
                'innova-zenix-g' => 1265000, 'innova-reborn' => 1092500, 'new-avanza' => 977500,
                'brv-xpander' => 977500, 'avanza-xenia-mobilio' => 805000, 'brio' => 805000,
                'hiace-commuter' => 1610000, 'hiace-premio' => 2070000, 'hiace-premio-luxury' => 2875000,
                'elf-19-seat' => 2185000, 'elf-premium-19-seat' => 2415000, 'new-giga-2025' => 2300000,
                'medium-bus-2025' => 3795000, 'big-bus-2025' => 5520000, 'big-bus-2017-2018' => 4370000,
            ],
            'yogyakarta' => ['innova-reborn-6-seat' => 1725000, 'hiace-commuter' => 2012500, 'elf-19-seat' => 2127500, 'elf-premium-19-seat' => 2357500],
            'malang' => ['innova-reborn-6-seat' => 1725000, 'hiace-commuter' => 2070000, 'elf-19-seat' => 2300000, 'elf-premium-19-seat' => 2530000],
            'bromo-banyuwangi' => ['innova-reborn-6-seat' => 1725000, 'hiace-commuter' => 2070000, 'elf-19-seat' => 2300000, 'elf-premium-19-seat' => 2530000],
        ];

        foreach ($rates as $areaSlug => $vehicleRates) {
            foreach ($vehicleRates as $vehicleCode => $price) {
                VehicleRentalRate::query()->updateOrCreate(
                    [
                        'vehicle_id' => $vehicles[$vehicleCode]->id,
                        'vehicle_rental_area_id' => $areas[$areaSlug]->id,
                        'valid_from' => '2026-01-01',
                    ],
                    ['price_per_day_idr' => $price, 'valid_until' => '2026-12-31', 'is_active' => true],
                );
            }
        }
    }

    private function seedTerms(): void
    {
        $terms = [
            ['code' => 'usage-area', 'type' => VehicleRentalTermType::UsageArea, 'title' => 'Wilayah Penggunaan', 'content' => 'Harga mengikuti kelas kendaraan dan zona pada itinerary awal. Penggunaan di luar zona asal dapat dikenakan biaya tambahan dan harus dikonfirmasi kepada admin.'],
            ['code' => 'operating-hours-private', 'type' => VehicleRentalTermType::OperatingHours, 'category' => VehicleCategory::Car, 'title' => 'Waktu Operasional Mobil', 'content' => 'Operasional pengemudi berada dalam rentang 05:00–23:00 WIB dengan penggunaan maksimal 12–13 jam per hari. Kelebihan waktu dikenakan tarif lembur sesuai kelas kendaraan.'],
            ['code' => 'operating-hours-minibus', 'type' => VehicleRentalTermType::OperatingHours, 'category' => VehicleCategory::Minibus, 'title' => 'Waktu Operasional Minibus', 'content' => 'Operasional pengemudi berada dalam rentang 05:00–23:00 WIB dengan penggunaan maksimal 12–13 jam per hari. Kelebihan waktu dikenakan tarif lembur sesuai kelas kendaraan.'],
            ['code' => 'operating-hours-bus', 'type' => VehicleRentalTermType::OperatingHours, 'category' => VehicleCategory::Bus, 'title' => 'Waktu Operasional Bus', 'content' => 'Penggunaan Medium Bus dan Big Bus maksimal 12 jam per hari sesuai peraturan keselamatan lalu lintas. Kelebihan waktu dikenakan tarif lembur yang berlaku.'],
            ['code' => 'included', 'type' => VehicleRentalTermType::Included, 'title' => 'Harga Termasuk', 'content' => 'Kendaraan yang bersih dan ber-AC, pengemudi berpengalaman, serta bahan bakar minyak (BBM).'],
            ['code' => 'excluded', 'type' => VehicleRentalTermType::Excluded, 'title' => 'Harga Tidak Termasuk', 'content' => 'Tol, parkir, tiket wisata, uang makan pengemudi sekitar Rp50.000 per hari, dan penginapan pengemudi apabila perjalanan mengharuskan menginap di luar zona asal.'],
            ['code' => 'booking', 'type' => VehicleRentalTermType::Booking, 'title' => 'Pemesanan', 'content' => 'Pemesanan dianggap sah setelah uang muka dikonfirmasi dan tetap bergantung pada ketersediaan armada.'],
            ['code' => 'reschedule', 'type' => VehicleRentalTermType::Reschedule, 'title' => 'Perubahan Jadwal', 'content' => 'Perubahan tanggal dapat diajukan maksimal H-10 sebelum kedatangan. Ketentuan ini tidak berlaku pada high season seperti Hari Raya, libur sekolah, dan Tahun Baru.'],
        ];

        foreach ($terms as $index => $term) {
            VehicleRentalTerm::query()->updateOrCreate(
                ['code' => $term['code']],
                [
                    'type' => $term['type'],
                    'vehicle_category' => $term['category'] ?? null,
                    'title' => $this->termTitleTranslations($term['title']),
                    'content' => $this->termTranslations($term['content']),
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }
    }

    private function deactivateObsoleteDummyVehicles(): void
    {
        Vehicle::query()
            ->whereNull('catalog_code')
            ->whereIn('model', ['Alphard', 'RN285'])
            ->update(['is_active' => false, 'is_featured' => false]);
    }

    /** @return array{id: string, en: string, ms: string} */
    private function translations(string $value): array
    {
        return ['id' => $value, 'en' => $value, 'ms' => $value];
    }

    /** @return array{id: string, en: string, ms: string} */
    private function vehicleDescription(VehicleCategory $category): array
    {
        return match ($category) {
            VehicleCategory::Car => [
                'id' => 'Kelas mobil ber-AC dengan pengemudi berpengalaman dan BBM untuk perjalanan sesuai wilayah layanan.',
                'en' => 'An air-conditioned car class with an experienced driver and fuel for trips within the service area.',
                'ms' => 'Kelas kereta berhawa dingin dengan pemandu berpengalaman dan bahan api untuk perjalanan dalam kawasan perkhidmatan.',
            ],
            VehicleCategory::Minibus => [
                'id' => 'Kelas minibus ber-AC untuk perjalanan rombongan dengan pengemudi berpengalaman dan BBM.',
                'en' => 'An air-conditioned minibus class for group travel with an experienced driver and fuel.',
                'ms' => 'Kelas minibus berhawa dingin untuk perjalanan berkumpulan dengan pemandu berpengalaman dan bahan api.',
            ],
            VehicleCategory::Bus => [
                'id' => 'Kelas bus pariwisata ber-AC untuk perjalanan rombongan dengan pengemudi berpengalaman dan BBM.',
                'en' => 'An air-conditioned tour bus class for group travel with an experienced driver and fuel.',
                'ms' => 'Kelas bas pelancongan berhawa dingin untuk perjalanan berkumpulan dengan pemandu berpengalaman dan bahan api.',
            ],
        };
    }

    /** @return array{id: string, en: string, ms: string} */
    private function termTranslations(string $indonesian): array
    {
        [$english, $malay] = match ($indonesian) {
            'Harga mengikuti kelas kendaraan dan zona pada itinerary awal. Penggunaan di luar zona asal dapat dikenakan biaya tambahan dan harus dikonfirmasi kepada admin.' => [
                'Pricing follows the vehicle class and service area in the original itinerary. Travel outside the original area may incur an additional charge and must be confirmed with our team.',
                'Harga mengikut kelas kenderaan dan kawasan perkhidmatan dalam itinerari asal. Perjalanan di luar kawasan asal mungkin dikenakan caj tambahan dan mesti disahkan dengan pasukan kami.',
            ],
            'Operasional pengemudi berada dalam rentang 05:00–23:00 WIB dengan penggunaan maksimal 12–13 jam per hari. Kelebihan waktu dikenakan tarif lembur sesuai kelas kendaraan.' => [
                'Driver operations are within 05:00–23:00 WIB, with a maximum use of 12–13 hours per day. Additional hours are charged at the applicable overtime rate.',
                'Operasi pemandu adalah dalam tempoh 05:00–23:00 WIB, dengan penggunaan maksimum 12–13 jam sehari. Masa tambahan dikenakan kadar lebih masa yang berkenaan.',
            ],
            'Penggunaan Medium Bus dan Big Bus maksimal 12 jam per hari sesuai peraturan keselamatan lalu lintas. Kelebihan waktu dikenakan tarif lembur yang berlaku.' => [
                'Medium and Big Bus use is limited to 12 hours per day under Indonesian traffic safety regulations. Additional hours are charged at the applicable overtime rate.',
                'Penggunaan Bas Sederhana dan Bas Besar dihadkan kepada 12 jam sehari mengikut peraturan keselamatan lalu lintas Indonesia. Masa tambahan dikenakan kadar lebih masa yang berkenaan.',
            ],
            'Kendaraan yang bersih dan ber-AC, pengemudi berpengalaman, serta bahan bakar minyak (BBM).' => [
                'A clean air-conditioned vehicle, an experienced driver, and fuel.',
                'Kenderaan bersih berhawa dingin, pemandu berpengalaman, dan bahan api.',
            ],
            'Tol, parkir, tiket wisata, uang makan pengemudi sekitar Rp50.000 per hari, dan penginapan pengemudi apabila perjalanan mengharuskan menginap di luar zona asal.' => [
                'Tolls, parking, attraction tickets, driver meals of approximately IDR 50,000 per day, and driver accommodation when an overnight stay outside the original area is required.',
                'Tol, parkir, tiket tarikan, makan pemandu sekitar IDR 50,000 sehari, dan penginapan pemandu apabila perlu bermalam di luar kawasan asal.',
            ],
            'Pemesanan dianggap sah setelah uang muka dikonfirmasi dan tetap bergantung pada ketersediaan armada.' => [
                'A booking is valid after the deposit is confirmed and remains subject to vehicle availability.',
                'Tempahan sah selepas deposit disahkan dan masih tertakluk pada ketersediaan kenderaan.',
            ],
            'Perubahan tanggal dapat diajukan maksimal H-10 sebelum kedatangan. Ketentuan ini tidak berlaku pada high season seperti Hari Raya, libur sekolah, dan Tahun Baru.' => [
                'Date changes may be requested no later than 10 days before arrival. This policy does not apply during high season, including religious holidays, school holidays, and New Year.',
                'Perubahan tarikh boleh dimohon selewat-lewatnya 10 hari sebelum ketibaan. Polisi ini tidak terpakai pada musim puncak termasuk hari perayaan, cuti sekolah, dan Tahun Baru.',
            ],
        };

        return ['id' => $indonesian, 'en' => $english, 'ms' => $malay];
    }

    /** @return array{id: string, en: string, ms: string} */
    private function termTitleTranslations(string $indonesian): array
    {
        [$english, $malay] = match ($indonesian) {
            'Wilayah Penggunaan' => ['Service Area', 'Kawasan Penggunaan'],
            'Waktu Operasional Mobil' => ['Car Operating Hours', 'Waktu Operasi Kereta'],
            'Waktu Operasional Minibus' => ['Minibus Operating Hours', 'Waktu Operasi Minibus'],
            'Waktu Operasional Bus' => ['Bus Operating Hours', 'Waktu Operasi Bas'],
            'Harga Termasuk' => ['Included in the Rate', 'Termasuk dalam Kadar'],
            'Harga Tidak Termasuk' => ['Not Included in the Rate', 'Tidak Termasuk dalam Kadar'],
            'Pemesanan' => ['Booking', 'Tempahan'],
            'Perubahan Jadwal' => ['Schedule Changes', 'Perubahan Jadual'],
        };

        return ['id' => $indonesian, 'en' => $english, 'ms' => $malay];
    }
}
