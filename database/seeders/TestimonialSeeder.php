<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use App\Models\Tour;
use App\Models\UmrahPackage;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $tours   = Tour::pluck('id')->values();
        $vehicles = Vehicle::pluck('id')->values();
        $packages = UmrahPackage::pluck('id')->values();

        $testimonials = [
            // ── Tour ─────────────────────────────────────────────────────
            [
                'reviewer_name'    => ['id' => 'Hendra Kusuma', 'en' => 'Hendra Kusuma', 'ms' => 'Hendra Kusuma'],
                'reviewer_country' => 'ID',
                'reviewer_flag'    => '🇮🇩',
                'product_type'     => 'App\Models\Tour',
                'product_id'       => $tours->get(0, 1),
                'rating'           => 5,
                'content'          => [
                    'id' => 'Pengalaman luar biasa! Perjalanan ke Tanjung Puting sangat berkesan. Panduan lokal sangat informatif dan ramah. Kami bisa melihat orang utan secara langsung dari dekat. Sangat direkomendasikan!',
                    'en' => 'An incredible experience! The trip to Tanjung Puting was unforgettable. The local guide was very informative and friendly. We got to see orangutans up close. Highly recommended!',
                    'ms' => 'Pengalaman luar biasa! Perjalanan ke Tanjung Puting sangat berkesan. Pemandu tempatan sangat bermaklumat dan mesra. Kami dapat melihat orang utan dari dekat. Sangat disyorkan!',
                ],
                'is_featured' => true,
                'is_active'   => true,
            ],
            [
                'reviewer_name'    => ['id' => 'Siti Rahmawati', 'en' => 'Siti Rahmawati', 'ms' => 'Siti Rahmawati'],
                'reviewer_country' => 'ID',
                'reviewer_flag'    => '🇮🇩',
                'product_type'     => 'App\Models\Tour',
                'product_id'       => $tours->get(1, 1),
                'rating'           => 5,
                'content'          => [
                    'id' => 'Tour ke Kepulauan Derawan benar-benar memukau! Air lautnya jernih banget, penyu bisa kita lihat langsung. Akomodasi bagus dan makanannya enak. Dijamin ketagihan kalau sudah ke sini.',
                    'en' => 'The Derawan Islands tour was absolutely stunning! The water was crystal clear and we could see sea turtles right there. Great accommodation and delicious food. You will definitely want to come back!',
                    'ms' => 'Lawatan ke Kepulauan Derawan benar-benar menakjubkan! Air lautnya sangat jernih dan kami boleh melihat penyu secara terus. Penginapan bagus dan makanan sedap. Pasti ingin kembali ke sini!',
                ],
                'is_featured' => true,
                'is_active'   => true,
            ],
            [
                'reviewer_name'    => ['id' => 'Ahmad Fauzi', 'en' => 'Ahmad Fauzi', 'ms' => 'Ahmad Fauzi'],
                'reviewer_country' => 'MY',
                'reviewer_flag'    => '🇲🇾',
                'product_type'     => 'App\Models\Tour',
                'product_id'       => $tours->get(2, 1),
                'rating'           => 4,
                'content'          => [
                    'id' => 'Wisata budaya Dayak yang tidak akan terlupakan. Kami diajak masuk ke desa adat dan belajar tari tradisional. Pemandu sangat sabar menjelaskan sejarah dan budaya Dayak. Luar biasa!',
                    'en' => 'An unforgettable Dayak cultural experience. We were taken into a traditional village and learned traditional dances. The guide patiently explained Dayak history and culture. Extraordinary!',
                    'ms' => 'Pengalaman budaya Dayak yang tidak akan dilupakan. Kami dibawa ke kampung tradisional dan belajar tarian tradisional. Pemandu sangat sabar menerangkan sejarah dan budaya Dayak. Luar biasa!',
                ],
                'is_featured' => false,
                'is_active'   => true,
            ],
            [
                'reviewer_name'    => ['id' => 'Nurul Hidayah', 'en' => 'Nurul Hidayah', 'ms' => 'Nurul Hidayah'],
                'reviewer_country' => 'ID',
                'reviewer_flag'    => '🇮🇩',
                'product_type'     => 'App\Models\Tour',
                'product_id'       => $tours->get(3, 1),
                'rating'           => 5,
                'content'          => [
                    'id' => 'Jelajah Sungai Mahakam yang menakjubkan! Pemandangan matahari terbenam di atas sungai sungguh romantis. Kelotok-nya nyaman dan bersih. Pengalaman yang wajib dicoba!',
                    'en' => 'An amazing Mahakam River journey! The sunset views over the river were truly romantic. The klotok boat was comfortable and clean. A must-try experience!',
                    'ms' => 'Perjalanan Sungai Mahakam yang menakjubkan! Pemandangan matahari terbenam di atas sungai sungguh romantik. Perahu klotok selesa dan bersih. Pengalaman yang mesti dicuba!',
                ],
                'is_featured' => true,
                'is_active'   => true,
            ],

            // ── Sewa Kendaraan ────────────────────────────────────────────
            [
                'reviewer_name'    => ['id' => 'Budi Santoso', 'en' => 'Budi Santoso', 'ms' => 'Budi Santoso'],
                'reviewer_country' => 'ID',
                'reviewer_flag'    => '🇮🇩',
                'product_type'     => 'App\Models\Vehicle',
                'product_id'       => $vehicles->get(0, 1),
                'rating'           => 5,
                'content'          => [
                    'id' => 'Sewa Alphard untuk perjalanan keluarga ke Banjarmasin. Kendaraan sangat bersih dan nyaman. Pak sopirnya ramah, sabar, dan tahu banyak tempat wisata di sana. Akan sewa lagi!',
                    'en' => 'Rented the Alphard for a family trip to Banjarmasin. The vehicle was very clean and comfortable. The driver was friendly, patient, and knowledgeable about local attractions. Will rent again!',
                    'ms' => 'Menyewa Alphard untuk perjalanan keluarga ke Banjarmasin. Kenderaan sangat bersih dan selesa. Pak pemandunya mesra, sabar, dan tahu banyak tempat pelancongan di sana. Akan sewa lagi!',
                ],
                'is_featured' => true,
                'is_active'   => true,
            ],
            [
                'reviewer_name'    => ['id' => 'Rizal Fadillah', 'en' => 'Rizal Fadillah', 'ms' => 'Rizal Fadillah'],
                'reviewer_country' => 'ID',
                'reviewer_flag'    => '🇮🇩',
                'product_type'     => 'App\Models\Vehicle',
                'product_id'       => $vehicles->get(1, 1),
                'rating'           => 4,
                'content'          => [
                    'id' => 'Sewa HiAce untuk grup 12 orang. Kapasitas bagasi mencukupi untuk semua koper. AC-nya mantap walaupun siang hari panas. Harga juga sangat terjangkau. Recommended!',
                    'en' => 'Rented a HiAce for a group of 12. Luggage capacity was enough for all our bags. The AC worked great even in the midday heat. The price was also very affordable. Recommended!',
                    'ms' => 'Menyewa HiAce untuk kumpulan 12 orang. Kapasiti bagasi mencukupi untuk semua beg. AC mantap walaupun cuaca panas tengah hari. Harga juga sangat berpatutan. Disyorkan!',
                ],
                'is_featured' => false,
                'is_active'   => true,
            ],

            // ── Umrah ─────────────────────────────────────────────────────
            [
                'reviewer_name'    => ['id' => 'Hajjah Fatimah', 'en' => 'Hajjah Fatimah', 'ms' => 'Hajjah Fatimah'],
                'reviewer_country' => 'ID',
                'reviewer_flag'    => '🇮🇩',
                'product_type'     => 'App\Models\UmrahPackage',
                'product_id'       => $packages->get(0, 1),
                'rating'           => 5,
                'content'          => [
                    'id' => 'Alhamdulillah, perjalanan umrah bersama Baharsyah Jelajah sangat luar biasa. Mulai dari bimbingan manasik, keberangkatan, hingga kembali ke tanah air semua terorganisir dengan baik. Pembimbingnya sabar dan berilmu. Insya Allah, kami akan berangkat lagi!',
                    'en' => "Alhamdulillah, the umrah journey with Baharsyah Jelajah was extraordinary. From manasik guidance, departure, to returning home — everything was well-organized. The guide was patient and knowledgeable. God willing, we will go again!",
                    'ms' => 'Alhamdulillah, perjalanan umrah bersama Baharsyah Jelajah sangat luar biasa. Dari bimbingan manasik, berlepas, hingga pulang ke tanah air — semuanya teratur dengan baik. Pembimbing sabar dan berpengetahuan. Insya-Allah, kami akan pergi lagi!',
                ],
                'is_featured' => true,
                'is_active'   => true,
            ],
            [
                'reviewer_name'    => ['id' => 'Muhammad Iqbal', 'en' => 'Muhammad Iqbal', 'ms' => 'Muhammad Iqbal'],
                'reviewer_country' => 'ID',
                'reviewer_flag'    => '🇮🇩',
                'product_type'     => 'App\Models\UmrahPackage',
                'product_id'       => $packages->get(1, 1),
                'rating'           => 5,
                'content'          => [
                    'id' => 'Paket Plus sangat worth it! Hotel bintang 4 dengan jarak yang tidak terlalu jauh dari Masjidil Haram. Program ziarahnya lengkap ke Gua Hira, Gua Tsur, Thaif, dan tempat-tempat bersejarah lainnya. Tim Baharsyah Jelajah selalu sigap membantu.',
                    'en' => 'The Plus package was totally worth it! 4-star hotel with a reasonable distance from Masjidil Haram. The ziarah program was comprehensive — Gua Hira, Gua Tsur, Thaif, and other historical sites. The Baharsyah Jelajah team was always ready to help.',
                    'ms' => 'Pakej Plus sangat berbaloi! Hotel bintang 4 dengan jarak yang tidak terlalu jauh dari Masjidil Haram. Program ziarah lengkap ke Gua Hira, Gua Tsur, Thaif, dan tempat-tempat bersejarah lain. Pasukan Baharsyah Jelajah sentiasa bersedia membantu.',
                ],
                'is_featured' => true,
                'is_active'   => true,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::create($testimonial);
        }
    }
}
