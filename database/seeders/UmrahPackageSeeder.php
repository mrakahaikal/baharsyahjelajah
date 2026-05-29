<?php

namespace Database\Seeders;

use App\Models\UmrahDeparture;
use App\Models\UmrahInclude;
use App\Models\UmrahPackage;
use Illuminate\Database\Seeder;

class UmrahPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => [
                    'id' => 'Paket Umrah Regular',
                    'en' => 'Regular Umrah Package',
                    'ms' => 'Pakej Umrah Biasa',
                ],
                'description' => [
                    'id' => 'Paket umrah hemat dengan fasilitas lengkap dan nyaman. Cocok bagi jamaah yang ingin beribadah dengan khusyuk tanpa biaya berlebihan.',
                    'en' => 'An affordable umrah package with complete and comfortable facilities. Ideal for pilgrims who want a focused worship experience without overspending.',
                    'ms' => 'Pakej umrah yang berpatutan dengan kemudahan lengkap dan selesa. Sesuai untuk jemaah yang ingin beribadat dengan khusyuk tanpa kos yang tinggi.',
                ],
                'package_type'       => 'regular',
                'duration_days'      => 9,
                'price_idr'          => 28000000,
                'airline'            => 'Garuda Indonesia',
                'hotel_makkah'       => 'Ajyad Makkah Hotel',
                'hotel_makkah_stars' => 3,
                'hotel_madinah'      => 'Al Shohada Hotel',
                'hotel_madinah_stars'=> 3,
                'room_type'          => 'quad',
                'visa_included'      => true,
                'handling_included'  => true,
                'is_active'          => true,
                'thumbnail'          => 'https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?w=800&q=80&fit=crop',
                'departures'         => [
                    ['departure_date' => '2026-07-15', 'return_date' => '2026-07-23', 'quota_total' => 40, 'quota_booked' => 12, 'status' => 'open'],
                    ['departure_date' => '2026-08-20', 'return_date' => '2026-08-28', 'quota_total' => 40, 'quota_booked' => 28, 'status' => 'nearly_full'],
                    ['departure_date' => '2026-09-10', 'return_date' => '2026-09-18', 'quota_total' => 40, 'quota_booked' => 5, 'status' => 'open'],
                ],
                'includes' => [
                    ['type' => 'include', 'item' => ['id' => 'Tiket pesawat PP Banjarmasin – Jeddah (Garuda Indonesia)', 'en' => 'Round-trip airfare Banjarmasin – Jeddah (Garuda Indonesia)', 'ms' => 'Tiket kapal terbang PP Banjarmasin – Jeddah (Garuda Indonesia)'], 'sort_order' => 1],
                    ['type' => 'include', 'item' => ['id' => 'Akomodasi hotel bintang 3 di Makkah & Madinah', 'en' => '3-star hotel accommodation in Makkah & Madinah', 'ms' => 'Penginapan hotel bintang 3 di Makkah & Madinah'], 'sort_order' => 2],
                    ['type' => 'include', 'item' => ['id' => 'Visa Umrah', 'en' => 'Umrah Visa', 'ms' => 'Visa Umrah'], 'sort_order' => 3],
                    ['type' => 'include', 'item' => ['id' => 'Transportasi darat selama di Arab Saudi', 'en' => 'Ground transportation in Saudi Arabia', 'ms' => 'Pengangkutan darat semasa di Arab Saudi'], 'sort_order' => 4],
                    ['type' => 'include', 'item' => ['id' => 'Konsumsi (makan 3x sehari)', 'en' => 'Full board meals (3 times daily)', 'ms' => 'Makan 3 kali sehari'], 'sort_order' => 5],
                    ['type' => 'include', 'item' => ['id' => 'Pembimbing ibadah berpengalaman', 'en' => 'Experienced spiritual guide', 'ms' => 'Pembimbing ibadah berpengalaman'], 'sort_order' => 6],
                    ['type' => 'include', 'item' => ['id' => 'Perlengkapan umrah (kain ihram, mukena, buku panduan)', 'en' => 'Umrah kit (ihram cloth, prayer garment, guidebook)', 'ms' => 'Perlengkapan umrah (kain ihram, telekung, buku panduan)'], 'sort_order' => 7],
                    ['type' => 'exclude', 'item' => ['id' => 'Pengeluaran pribadi', 'en' => 'Personal expenses', 'ms' => 'Perbelanjaan peribadi'], 'sort_order' => 8],
                    ['type' => 'exclude', 'item' => ['id' => 'Biaya ziarah tambahan di luar jadwal', 'en' => 'Additional ziarah outside schedule', 'ms' => 'Kos ziarah tambahan di luar jadual'], 'sort_order' => 9],
                    ['type' => 'exclude', 'item' => ['id' => 'Biaya kelebihan bagasi', 'en' => 'Excess baggage fees', 'ms' => 'Caj lebihan bagasi'], 'sort_order' => 10],
                ],
            ],
            [
                'name' => [
                    'id' => 'Paket Umrah Plus',
                    'en' => 'Umrah Plus Package',
                    'ms' => 'Pakej Umrah Plus',
                ],
                'description' => [
                    'id' => 'Paket umrah dengan durasi lebih panjang dan fasilitas hotel bintang 4. Tersedia program ziarah ke berbagai tempat bersejarah di Makkah dan Madinah.',
                    'en' => 'An extended umrah package with 4-star hotel facilities and a comprehensive ziarah program to historical sites in Makkah and Madinah.',
                    'ms' => 'Pakej umrah dengan tempoh lebih lama dan kemudahan hotel bintang 4. Program ziarah ke pelbagai tempat bersejarah di Makkah dan Madinah tersedia.',
                ],
                'package_type'       => 'plus',
                'duration_days'      => 12,
                'price_idr'          => 35000000,
                'airline'            => 'Garuda Indonesia',
                'hotel_makkah'       => 'Movenpick Hajar Tower',
                'hotel_makkah_stars' => 4,
                'hotel_madinah'      => 'Crowne Plaza Madinah',
                'hotel_madinah_stars'=> 4,
                'room_type'          => 'triple',
                'visa_included'      => true,
                'handling_included'  => true,
                'is_active'          => true,
                'thumbnail'          => 'https://images.unsplash.com/photo-1564769662533-4f00a87b4056?w=800&q=80&fit=crop',
                'departures'         => [
                    ['departure_date' => '2026-07-05', 'return_date' => '2026-07-16', 'quota_total' => 30, 'quota_booked' => 8, 'status' => 'open'],
                    ['departure_date' => '2026-09-01', 'return_date' => '2026-09-12', 'quota_total' => 30, 'quota_booked' => 0, 'status' => 'open'],
                ],
                'includes' => [
                    ['type' => 'include', 'item' => ['id' => 'Tiket pesawat PP Banjarmasin – Jeddah (Garuda Indonesia)', 'en' => 'Round-trip airfare Banjarmasin – Jeddah (Garuda Indonesia)', 'ms' => 'Tiket kapal terbang PP Banjarmasin – Jeddah (Garuda Indonesia)'], 'sort_order' => 1],
                    ['type' => 'include', 'item' => ['id' => 'Akomodasi hotel bintang 4 di Makkah & Madinah', 'en' => '4-star hotel accommodation in Makkah & Madinah', 'ms' => 'Penginapan hotel bintang 4 di Makkah & Madinah'], 'sort_order' => 2],
                    ['type' => 'include', 'item' => ['id' => 'Visa Umrah', 'en' => 'Umrah Visa', 'ms' => 'Visa Umrah'], 'sort_order' => 3],
                    ['type' => 'include', 'item' => ['id' => 'Transportasi darat + program ziarah lengkap', 'en' => 'Ground transportation + full ziarah program', 'ms' => 'Pengangkutan darat + program ziarah lengkap'], 'sort_order' => 4],
                    ['type' => 'include', 'item' => ['id' => 'Konsumsi (makan 3x sehari)', 'en' => 'Full board meals (3 times daily)', 'ms' => 'Makan 3 kali sehari'], 'sort_order' => 5],
                    ['type' => 'include', 'item' => ['id' => 'Pembimbing ibadah berpengalaman', 'en' => 'Experienced spiritual guide', 'ms' => 'Pembimbing ibadah berpengalaman'], 'sort_order' => 6],
                    ['type' => 'include', 'item' => ['id' => 'Perlengkapan umrah + tas koper branded', 'en' => 'Umrah kit + branded luggage', 'ms' => 'Perlengkapan umrah + beg pakaian berjenama'], 'sort_order' => 7],
                    ['type' => 'exclude', 'item' => ['id' => 'Pengeluaran pribadi', 'en' => 'Personal expenses', 'ms' => 'Perbelanjaan peribadi'], 'sort_order' => 8],
                    ['type' => 'exclude', 'item' => ['id' => 'Biaya kelebihan bagasi', 'en' => 'Excess baggage fees', 'ms' => 'Caj lebihan bagasi'], 'sort_order' => 9],
                ],
            ],
            [
                'name' => [
                    'id' => 'Paket Umrah VIP',
                    'en' => 'VIP Umrah Package',
                    'ms' => 'Pakej Umrah VIP',
                ],
                'description' => [
                    'id' => 'Pengalaman umrah terbaik dengan hotel bintang 5 jarak dekat Masjidil Haram. Layanan premium, kamar double eksklusif, dan pembimbing pribadi.',
                    'en' => 'A premium umrah experience with 5-star hotels near Masjidil Haram. Exclusive double rooms, personal spiritual guide, and premium services throughout.',
                    'ms' => 'Pengalaman umrah terbaik dengan hotel bintang 5 berdekatan Masjidil Haram. Bilik double eksklusif, pembimbing peribadi, dan perkhidmatan premium.',
                ],
                'package_type'       => 'vip',
                'duration_days'      => 15,
                'price_idr'          => 48000000,
                'airline'            => 'Garuda Indonesia',
                'hotel_makkah'       => 'Fairmont Makkah Clock Royal Tower',
                'hotel_makkah_stars' => 5,
                'hotel_madinah'      => 'Oberoi Madinah',
                'hotel_madinah_stars'=> 5,
                'room_type'          => 'double',
                'visa_included'      => true,
                'handling_included'  => true,
                'is_active'          => true,
                'thumbnail'          => 'https://images.unsplash.com/photo-1609770231080-e321deccc34c?w=800&q=80&fit=crop',
                'departures'         => [
                    ['departure_date' => '2026-08-01', 'return_date' => '2026-08-15', 'quota_total' => 20, 'quota_booked' => 6, 'status' => 'open'],
                    ['departure_date' => '2026-10-05', 'return_date' => '2026-10-19', 'quota_total' => 20, 'quota_booked' => 0, 'status' => 'open'],
                ],
                'includes' => [
                    ['type' => 'include', 'item' => ['id' => 'Tiket pesawat PP Business Class (Garuda Indonesia)', 'en' => 'Round-trip Business Class airfare (Garuda Indonesia)', 'ms' => 'Tiket kapal terbang PP Kelas Perniagaan (Garuda Indonesia)'], 'sort_order' => 1],
                    ['type' => 'include', 'item' => ['id' => 'Hotel bintang 5 – posisi sangat dekat Masjidil Haram', 'en' => '5-star hotel – steps away from Masjidil Haram', 'ms' => 'Hotel bintang 5 – sangat dekat Masjidil Haram'], 'sort_order' => 2],
                    ['type' => 'include', 'item' => ['id' => 'Kamar Double / Twin eksklusif', 'en' => 'Exclusive Double / Twin room', 'ms' => 'Bilik Double / Twin eksklusif'], 'sort_order' => 3],
                    ['type' => 'include', 'item' => ['id' => 'Visa Umrah + asuransi perjalanan', 'en' => 'Umrah Visa + travel insurance', 'ms' => 'Visa Umrah + insurans perjalanan'], 'sort_order' => 4],
                    ['type' => 'include', 'item' => ['id' => 'Transportasi VIP door-to-door', 'en' => 'VIP door-to-door transportation', 'ms' => 'Pengangkutan VIP pintu ke pintu'], 'sort_order' => 5],
                    ['type' => 'include', 'item' => ['id' => 'Konsumsi full board + snack eksklusif', 'en' => 'Full board meals + exclusive snacks', 'ms' => 'Makan penuh + snek eksklusif'], 'sort_order' => 6],
                    ['type' => 'include', 'item' => ['id' => 'Pembimbing ibadah pribadi', 'en' => 'Personal spiritual guide', 'ms' => 'Pembimbing ibadah peribadi'], 'sort_order' => 7],
                    ['type' => 'include', 'item' => ['id' => 'Paket ziarah premium Makkah, Madinah & Thaif', 'en' => 'Premium ziarah package: Makkah, Madinah & Thaif', 'ms' => 'Pakej ziarah premium Makkah, Madinah & Thaif'], 'sort_order' => 8],
                    ['type' => 'include', 'item' => ['id' => 'Perlengkapan umrah premium + koper trolley', 'en' => 'Premium umrah kit + trolley luggage', 'ms' => 'Perlengkapan umrah premium + koper trolik'], 'sort_order' => 9],
                    ['type' => 'exclude', 'item' => ['id' => 'Pengeluaran pribadi', 'en' => 'Personal expenses', 'ms' => 'Perbelanjaan peribadi'], 'sort_order' => 10],
                ],
            ],
        ];

        foreach ($packages as $data) {
            $departures = $data['departures'];
            $includes   = $data['includes'];
            unset($data['departures'], $data['includes']);

            $package = UmrahPackage::create($data);

            foreach ($departures as $departure) {
                UmrahDeparture::create([
                    'package_id' => $package->id,
                    ...$departure,
                ]);
            }

            foreach ($includes as $include) {
                UmrahInclude::create([
                    'package_id' => $package->id,
                    ...$include,
                ]);
            }
        }
    }
}