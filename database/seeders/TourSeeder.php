<?php

namespace Database\Seeders;

use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\TourGallery;
use App\Models\TourInclude;
use App\Models\TourItinerary;
use Illuminate\Database\Seeder;

class TourSeeder extends Seeder
{
    public function run(): void
    {
        $alam = TourCategory::where('icon', 'heroicon-o-sparkles')->first();
        $bahari = TourCategory::where('icon', 'heroicon-o-sun')->first();
        $budaya = TourCategory::where('icon', 'heroicon-o-building-library')->first();
        $petualangan = TourCategory::where('icon', 'heroicon-o-map')->first();

        $tours = [
            [
                'tour' => [
                    'category_id' => $alam->id,
                    'name' => [
                        'id' => 'Ekspedisi Orangutan Tanjung Puting',
                        'en' => 'Tanjung Puting Orangutan Expedition',
                        'ms' => 'Ekspedisi Orang Utan Tanjung Puting',
                    ],
                    'slug' => [
                        'id' => 'ekspedisi-orangutan-tanjung-puting',
                        'en' => 'tanjung-puting-orangutan-expedition',
                        'ms' => 'ekspedisi-orang-utan-tanjung-puting',
                    ],
                    'description' => [
                        'id' => 'Jelajahi Taman Nasional Tanjung Puting, rumah bagi orangutan Kalimantan yang ikonik. Nikmati petualangan susur sungai dengan klotok sambil menyaksikan orangutan liar di habitat aslinya di hutan hujan tropis Kalimantan Tengah.',
                        'en' => 'Explore Tanjung Puting National Park, home to the iconic Bornean orangutans. Enjoy a riverboat adventure while witnessing wild orangutans in their natural habitat in the tropical rainforests of Central Kalimantan.',
                        'ms' => 'Jelajahi Taman Negara Tanjung Puting, rumah kepada orang utan Borneo yang ikonik. Nikmati pengembaraan perahu sungai sambil menyaksikan orang utan liar di habitat semula jadi mereka di hutan hujan tropika Kalimantan Tengah.',
                    ],
                    'highlights' => [
                        'id' => "Menyaksikan orangutan liar di Camp Leakey\nBermalam di atas klotok di tengah hutan\nBirdwatching dan pengamatan satwa liar\nKunjungan ke Camp Pondok Tanggui",
                        'en' => "Witness wild orangutans at Camp Leakey\nOvernight on a klotok houseboat in the jungle\nBirdwatching and wildlife observation\nVisit to Camp Pondok Tanggui",
                        'ms' => "Saksikan orang utan liar di Camp Leakey\nBermalam di atas klotok di tengah hutan\nPemantauan burung dan pemerhatian hidupan liar\nLawatan ke Camp Pondok Tanggui",
                    ],
                    'tour_type' => 'group',
                    'duration_days' => 4,
                    'duration_nights' => 3,
                    'price' => 3500000,
                    'currency' => 'IDR',
                    'difficulty' => 'easy',
                    'max_pax' => 12,
                    'is_active' => true,
                    'is_featured' => true,
                    'thumbnail' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=800&q=80&fit=crop',
                ],
                'itineraries' => [
                    [
                        'day_number' => 1,
                        'title' => ['id' => 'Tiba di Pangkalan Bun', 'en' => 'Arrival in Pangkalan Bun', 'ms' => 'Tiba di Pangkalan Bun'],
                        'description' => [
                            'id' => 'Tiba di Bandara Iskandar Pangkalan Bun. Dijemput oleh pemandu wisata dan transfer ke pelabuhan untuk naik klotok. Briefing perjalanan dan makan malam di atas klotok.',
                            'en' => 'Arrive at Iskandar Airport in Pangkalan Bun. Meet your guide and transfer to the port to board the klotok houseboat. Trip briefing and dinner on the klotok.',
                            'ms' => 'Tiba di Lapangan Terbang Iskandar di Pangkalan Bun. Disambut oleh pemandu pelancongan dan pindah ke pelabuhan untuk menaiki klotok. Taklimat perjalanan dan makan malam di atas klotok.',
                        ],
                        'meals_included' => ['id' => 'Makan Malam', 'en' => 'Dinner', 'ms' => 'Makan Malam'],
                        'accommodation' => 'Klotok Houseboat',
                    ],
                    [
                        'day_number' => 2,
                        'title' => ['id' => 'Camp Leakey & Pondok Tanggui', 'en' => 'Camp Leakey & Pondok Tanggui', 'ms' => 'Camp Leakey & Pondok Tanggui'],
                        'description' => [
                            'id' => 'Kunjungi Camp Pondok Tanggui di pagi hari untuk menyaksikan pemberian makan orangutan. Lanjutkan ke Camp Leakey, pusat penelitian orangutan tertua di dunia. Sore hari birdwatching di sepanjang sungai.',
                            'en' => 'Visit Camp Pondok Tanggui in the morning to witness orangutan feeding. Continue to Camp Leakey, the world\'s oldest orangutan research station. Afternoon birdwatching along the river.',
                            'ms' => 'Lawati Camp Pondok Tanggui pada waktu pagi untuk menyaksikan pemberian makan orang utan. Teruskan ke Camp Leakey, stesen penyelidikan orang utan tertua di dunia. Petang pemantauan burung di sepanjang sungai.',
                        ],
                        'meals_included' => ['id' => 'Sarapan, Makan Siang, Makan Malam', 'en' => 'Breakfast, Lunch, Dinner', 'ms' => 'Sarapan, Makan Tengah Hari, Makan Malam'],
                        'accommodation' => 'Klotok Houseboat',
                    ],
                    [
                        'day_number' => 3,
                        'title' => ['id' => 'Hutan Tanjung Harapan', 'en' => 'Tanjung Harapan Forest', 'ms' => 'Hutan Tanjung Harapan'],
                        'description' => [
                            'id' => 'Pagi hari trekking di hutan Tanjung Harapan. Kesempatan menyaksikan proboscis monkey, bekantan, dan berbagai jenis burung. Siang hari bersantai di klotok sambil menikmati pemandangan hutan.',
                            'en' => 'Morning trekking in Tanjung Harapan forest. Opportunity to see proboscis monkeys, bearded pigs, and various bird species. Afternoon relaxing on the klotok while enjoying forest views.',
                            'ms' => 'Trekking pagi di hutan Tanjung Harapan. Peluang melihat monyet belanda, babi berjanggut, dan pelbagai spesies burung. Petang berehat di klotok sambil menikmati pemandangan hutan.',
                        ],
                        'meals_included' => ['id' => 'Sarapan, Makan Siang, Makan Malam', 'en' => 'Breakfast, Lunch, Dinner', 'ms' => 'Sarapan, Makan Tengah Hari, Makan Malam'],
                        'accommodation' => 'Klotok Houseboat',
                    ],
                    [
                        'day_number' => 4,
                        'title' => ['id' => 'Kepulangan', 'en' => 'Departure', 'ms' => 'Pemulangan'],
                        'description' => [
                            'id' => 'Sarapan pagi di klotok sambil menikmati suasana hutan untuk terakhir kalinya. Transfer ke bandara untuk penerbangan pulang.',
                            'en' => 'Breakfast on the klotok while enjoying the forest ambiance for the last time. Transfer to the airport for your return flight.',
                            'ms' => 'Sarapan di klotok sambil menikmati suasana hutan buat kali terakhir. Pindah ke lapangan terbang untuk penerbangan pulang.',
                        ],
                        'meals_included' => ['id' => 'Sarapan', 'en' => 'Breakfast', 'ms' => 'Sarapan'],
                        'accommodation' => '-',
                    ],
                ],
                'includes' => [
                    ['item' => ['id' => 'Sewa klotok selama 3 malam', 'en' => '3-night klotok houseboat rental', 'ms' => 'Sewa klotok 3 malam'], 'type' => 'include', 'sort_order' => 1],
                    ['item' => ['id' => 'Pemandu wisata berpengalaman', 'en' => 'Experienced tour guide', 'ms' => 'Pemandu pelancongan berpengalaman'], 'type' => 'include', 'sort_order' => 2],
                    ['item' => ['id' => 'Seluruh makan (dari makan malam hari 1)', 'en' => 'All meals (from dinner on day 1)', 'ms' => 'Semua hidangan (dari makan malam hari 1)'], 'type' => 'include', 'sort_order' => 3],
                    ['item' => ['id' => 'Tiket masuk taman nasional', 'en' => 'National park entrance fee', 'ms' => 'Yuran masuk taman negara'], 'type' => 'include', 'sort_order' => 4],
                    ['item' => ['id' => 'Tiket pesawat PP', 'en' => 'Return flight tickets', 'ms' => 'Tiket penerbangan pergi balik'], 'type' => 'exclude', 'sort_order' => 1],
                    ['item' => ['id' => 'Pengeluaran pribadi', 'en' => 'Personal expenses', 'ms' => 'Perbelanjaan peribadi'], 'type' => 'exclude', 'sort_order' => 2],
                ],
                'galleries' => [
                    ['image_path' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=1200&q=80&fit=crop', 'caption' => 'Hutan hujan tropis Tanjung Puting', 'sort_order' => 1],
                    ['image_path' => 'https://images.unsplash.com/photo-1497294815431-9365093b7331?w=1200&q=80&fit=crop', 'caption' => 'Perjalanan susur sungai dengan klotok', 'sort_order' => 2],
                    ['image_path' => 'https://images.unsplash.com/photo-1544551748-8fad4a4d1db9?w=1200&q=80&fit=crop', 'caption' => 'Trekking di jalur hutan', 'sort_order' => 3],
                    ['image_path' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&q=80&fit=crop', 'caption' => 'Pemandangan alam Kalimantan', 'sort_order' => 4],
                ],
            ],
            [
                'tour' => [
                    'category_id' => $bahari->id,
                    'name' => [
                        'id' => 'Snorkeling & Diving Kepulauan Derawan',
                        'en' => 'Derawan Islands Snorkeling & Diving',
                        'ms' => 'Snorkeling & Menyelam Kepulauan Derawan',
                    ],
                    'slug' => [
                        'id' => 'snorkeling-diving-kepulauan-derawan',
                        'en' => 'derawan-islands-snorkeling-diving',
                        'ms' => 'snorkeling-menyelam-kepulauan-derawan',
                    ],
                    'description' => [
                        'id' => 'Temukan keindahan bawah laut Kepulauan Derawan, surga bahari di Kalimantan Timur. Nikmati snorkeling bersama penyu hijau, berenang dengan ubur-ubur tak bersengat di Danau Kakaban, dan menyelam di titik-titik terbaik Berau.',
                        'en' => 'Discover the underwater beauty of the Derawan Islands, a marine paradise in East Kalimantan. Enjoy snorkeling with green turtles, swim with stingless jellyfish at Lake Kakaban, and dive at the best spots in Berau.',
                        'ms' => 'Temui keindahan bawah laut Kepulauan Derawan, syurga marin di Kalimantan Timur. Nikmati snorkeling bersama penyu hijau, berenang dengan obor-obor tanpa sengat di Tasik Kakaban, dan menyelam di kawasan terbaik Berau.',
                    ],
                    'highlights' => [
                        'id' => "Snorkeling bersama penyu hijau di Pulau Derawan\nBerenang dengan ubur-ubur di Danau Kakaban\nMenyelam di Pulau Sangalaki\nMenyaksikan manta ray di perairan Berau",
                        'en' => "Snorkeling with green turtles at Derawan Island\nSwimming with jellyfish at Lake Kakaban\nDiving at Sangalaki Island\nWitnessing manta rays in Berau waters",
                        'ms' => "Snorkeling bersama penyu hijau di Pulau Derawan\nBerenang dengan obor-obor di Tasik Kakaban\nMenyelam di Pulau Sangalaki\nMenyaksikan pari manta di perairan Berau",
                    ],
                    'tour_type' => 'group',
                    'duration_days' => 3,
                    'duration_nights' => 2,
                    'price' => 2800000,
                    'currency' => 'IDR',
                    'difficulty' => 'easy',
                    'max_pax' => 10,
                    'is_active' => true,
                    'is_featured' => true,
                    'thumbnail' => 'https://images.unsplash.com/photo-1559494007-a0ef0c3c85a4?w=800&q=80&fit=crop',
                ],
                'itineraries' => [
                    [
                        'day_number' => 1,
                        'title' => ['id' => 'Tiba di Berau & Menuju Derawan', 'en' => 'Arrival in Berau & Transfer to Derawan', 'ms' => 'Tiba di Berau & Perjalanan ke Derawan'],
                        'description' => [
                            'id' => 'Tiba di Bandara Kalimarau Berau. Transfer ke pelabuhan Tanjung Batu dan naik speedboat menuju Pulau Derawan. Check-in cottage tepi pantai. Sore hari snorkeling pertama di sekitar pulau.',
                            'en' => 'Arrive at Kalimarau Airport in Berau. Transfer to Tanjung Batu port and speedboat to Derawan Island. Check-in beachfront cottage. Afternoon first snorkeling session around the island.',
                            'ms' => 'Tiba di Lapangan Terbang Kalimarau di Berau. Pindah ke pelabuhan Tanjung Batu dan menaiki bot laju ke Pulau Derawan. Daftar masuk kotej tepi pantai. Petang sesi snorkeling pertama di sekitar pulau.',
                        ],
                        'meals_included' => ['id' => 'Makan Malam', 'en' => 'Dinner', 'ms' => 'Makan Malam'],
                        'accommodation' => 'Cottage Tepi Pantai Derawan',
                    ],
                    [
                        'day_number' => 2,
                        'title' => ['id' => 'Kakaban, Sangalaki & Maratua', 'en' => 'Kakaban, Sangalaki & Maratua', 'ms' => 'Kakaban, Sangalaki & Maratua'],
                        'description' => [
                            'id' => 'Full day island hopping. Pagi di Danau Kakaban berenang bersama ubur-ubur. Siang snorkeling di Pulau Sangalaki bersama manta ray. Sore mengunjungi Pulau Maratua untuk menikmati pantai berpasir putih.',
                            'en' => 'Full day island hopping. Morning at Lake Kakaban swimming with jellyfish. Midday snorkeling at Sangalaki Island with manta rays. Afternoon visiting Maratua Island to enjoy white sandy beaches.',
                            'ms' => 'Lawatan pulau penuh hari. Pagi di Tasik Kakaban berenang bersama obor-obor. Tengah hari snorkeling di Pulau Sangalaki bersama pari manta. Petang melawat Pulau Maratua untuk menikmati pantai berpasir putih.',
                        ],
                        'meals_included' => ['id' => 'Sarapan, Makan Siang, Makan Malam', 'en' => 'Breakfast, Lunch, Dinner', 'ms' => 'Sarapan, Makan Tengah Hari, Makan Malam'],
                        'accommodation' => 'Cottage Tepi Pantai Derawan',
                    ],
                    [
                        'day_number' => 3,
                        'title' => ['id' => 'Snorkeling Pagi & Kepulangan', 'en' => 'Morning Snorkeling & Departure', 'ms' => 'Snorkeling Pagi & Pemulangan'],
                        'description' => [
                            'id' => 'Snorkeling pagi terakhir di sekitar Pulau Derawan bersama penyu hijau. Check-out cottage. Speedboat kembali ke pelabuhan Tanjung Batu dan transfer ke bandara.',
                            'en' => 'Last morning snorkeling around Derawan Island with green turtles. Check-out from cottage. Speedboat back to Tanjung Batu port and transfer to the airport.',
                            'ms' => 'Snorkeling pagi terakhir di sekitar Pulau Derawan bersama penyu hijau. Daftar keluar dari kotej. Bot laju kembali ke pelabuhan Tanjung Batu dan pindah ke lapangan terbang.',
                        ],
                        'meals_included' => ['id' => 'Sarapan, Makan Siang', 'en' => 'Breakfast, Lunch', 'ms' => 'Sarapan, Makan Tengah Hari'],
                        'accommodation' => '-',
                    ],
                ],
                'includes' => [
                    ['item' => ['id' => 'Akomodasi cottage 2 malam', 'en' => '2-night cottage accommodation', 'ms' => 'Penginapan kotej 2 malam'], 'type' => 'include', 'sort_order' => 1],
                    ['item' => ['id' => 'Transportasi speedboat selama tur', 'en' => 'Speedboat transportation during tour', 'ms' => 'Pengangkutan bot laju semasa lawatan'], 'type' => 'include', 'sort_order' => 2],
                    ['item' => ['id' => 'Peralatan snorkeling', 'en' => 'Snorkeling equipment', 'ms' => 'Peralatan snorkeling'], 'type' => 'include', 'sort_order' => 3],
                    ['item' => ['id' => 'Pemandu wisata bahari', 'en' => 'Marine tour guide', 'ms' => 'Pemandu pelancongan marin'], 'type' => 'include', 'sort_order' => 4],
                    ['item' => ['id' => 'Semua makan (dari makan malam hari 1)', 'en' => 'All meals (from dinner on day 1)', 'ms' => 'Semua hidangan (dari makan malam hari 1)'], 'type' => 'include', 'sort_order' => 5],
                    ['item' => ['id' => 'Peralatan diving (sewa terpisah)', 'en' => 'Diving equipment (separate rental)', 'ms' => 'Peralatan menyelam (sewa berasingan)'], 'type' => 'exclude', 'sort_order' => 1],
                    ['item' => ['id' => 'Tiket pesawat ke Berau', 'en' => 'Flight tickets to Berau', 'ms' => 'Tiket penerbangan ke Berau'], 'type' => 'exclude', 'sort_order' => 2],
                ],
                'galleries' => [
                    ['image_path' => 'https://images.unsplash.com/photo-1559494007-a0ef0c3c85a4?w=1200&q=80&fit=crop', 'caption' => 'Perairan biru Kepulauan Derawan', 'sort_order' => 1],
                    ['image_path' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1200&q=80&fit=crop', 'caption' => 'Pantai berpasir putih Pulau Maratua', 'sort_order' => 2],
                    ['image_path' => 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=1200&q=80&fit=crop', 'caption' => 'Bawah laut Kepulauan Derawan', 'sort_order' => 3],
                ],
            ],
            [
                'tour' => [
                    'category_id' => $budaya->id,
                    'name' => [
                        'id' => 'Budaya & Tradisi Suku Dayak',
                        'en' => 'Dayak Tribe Culture & Traditions',
                        'ms' => 'Budaya & Tradisi Suku Dayak',
                    ],
                    'slug' => [
                        'id' => 'budaya-tradisi-suku-dayak',
                        'en' => 'dayak-tribe-culture-traditions',
                        'ms' => 'budaya-tradisi-suku-dayak',
                    ],
                    'description' => [
                        'id' => 'Selami kehidupan dan budaya Suku Dayak yang kaya di pedalaman Kalimantan. Kunjungi rumah betang, saksikan tarian tradisional, belajar kerajinan anyaman rotan, dan rasakan kehidupan autentik masyarakat adat Dayak.',
                        'en' => 'Immerse yourself in the rich life and culture of the Dayak tribe in the interior of Borneo. Visit longhouses, witness traditional dances, learn rattan weaving crafts, and experience the authentic life of the Dayak indigenous people.',
                        'ms' => 'Selami kehidupan dan budaya Suku Dayak yang kaya di pedalaman Kalimantan. Lawati rumah betang, saksikan tarian tradisional, pelajari kraf anyaman rotan, dan rasai kehidupan autentik masyarakat adat Dayak.',
                    ],
                    'highlights' => [
                        'id' => "Kunjungan ke Rumah Betang (Rumah Panjang)\nPertunjukan tarian tradisional Dayak\nWorkshop pembuatan kerajinan rotan\nMakan malam tradisional bersama keluarga Dayak",
                        'en' => "Visit to Betang House (Longhouse)\nTraditional Dayak dance performance\nRattan craft-making workshop\nTraditional dinner with a Dayak family",
                        'ms' => "Lawatan ke Rumah Betang (Rumah Panjang)\nPersembahan tarian tradisional Dayak\nBengkel pembuatan kraf rotan\nMakan malam tradisional bersama keluarga Dayak",
                    ],
                    'tour_type' => 'group',
                    'duration_days' => 3,
                    'duration_nights' => 2,
                    'price' => 1800000,
                    'currency' => 'IDR',
                    'difficulty' => 'easy',
                    'max_pax' => 15,
                    'is_active' => true,
                    'is_featured' => false,
                    'thumbnail' => 'https://images.unsplash.com/photo-1605106702734-205df224ecce?w=800&q=80&fit=crop',
                ],
                'itineraries' => [
                    [
                        'day_number' => 1,
                        'title' => ['id' => 'Kedatangan & Desa Dayak', 'en' => 'Arrival & Dayak Village', 'ms' => 'Ketibaan & Kampung Dayak'],
                        'description' => [
                            'id' => 'Tiba dan dijemput menuju desa Dayak. Disambut upacara penyambutan tradisional. Pengenalan kepada kepala suku dan tur keliling desa. Malam menonton pertunjukan tarian dan musik tradisional.',
                            'en' => 'Arrive and be transferred to the Dayak village. Welcomed by a traditional reception ceremony. Introduction to the tribal chief and village tour. Evening watching traditional dance and music performances.',
                            'ms' => 'Tiba dan dipindahkan ke kampung Dayak. Disambut dengan upacara penyambutan tradisional. Perkenalan dengan ketua suku dan lawatan keliling kampung. Malam menonton persembahan tarian dan muzik tradisional.',
                        ],
                        'meals_included' => ['id' => 'Makan Malam', 'en' => 'Dinner', 'ms' => 'Makan Malam'],
                        'accommodation' => 'Rumah Betang (Longhouse)',
                    ],
                    [
                        'day_number' => 2,
                        'title' => ['id' => 'Workshop Kerajinan & Hutan Adat', 'en' => 'Craft Workshop & Traditional Forest', 'ms' => 'Bengkel Kraf & Hutan Adat'],
                        'description' => [
                            'id' => 'Pagi hari workshop pembuatan kerajinan rotan dan tenun tradisional. Siang hari trekking ke hutan adat dipandu tetua suku untuk mengenal tanaman obat. Sore belajar memasak masakan tradisional Dayak.',
                            'en' => 'Morning workshop on rattan craft-making and traditional weaving. Midday trekking to the traditional forest guided by tribal elders to learn about medicinal plants. Afternoon cooking traditional Dayak cuisine.',
                            'ms' => 'Bengkel pagi tentang pembuatan kraf rotan dan tenunan tradisional. Trekking tengah hari ke hutan adat dipandu oleh tetua suku untuk mengenali tumbuhan ubatan. Petang memasak masakan tradisional Dayak.',
                        ],
                        'meals_included' => ['id' => 'Sarapan, Makan Siang, Makan Malam', 'en' => 'Breakfast, Lunch, Dinner', 'ms' => 'Sarapan, Makan Tengah Hari, Makan Malam'],
                        'accommodation' => 'Rumah Betang (Longhouse)',
                    ],
                    [
                        'day_number' => 3,
                        'title' => ['id' => 'Upacara Penutupan & Kepulangan', 'en' => 'Closing Ceremony & Departure', 'ms' => 'Upacara Penutup & Pemulangan'],
                        'description' => [
                            'id' => 'Sarapan pagi bersama keluarga Dayak. Upacara penutupan dan pertukaran kenang-kenangan. Transfer kembali ke kota.',
                            'en' => 'Breakfast with the Dayak family. Closing ceremony and exchange of souvenirs. Transfer back to the city.',
                            'ms' => 'Sarapan bersama keluarga Dayak. Upacara penutup dan pertukaran cenderamata. Pindah kembali ke bandar.',
                        ],
                        'meals_included' => ['id' => 'Sarapan', 'en' => 'Breakfast', 'ms' => 'Sarapan'],
                        'accommodation' => '-',
                    ],
                ],
                'includes' => [
                    ['item' => ['id' => 'Menginap di Rumah Betang 2 malam', 'en' => '2-night stay in Betang Longhouse', 'ms' => 'Menginap di Rumah Betang 2 malam'], 'type' => 'include', 'sort_order' => 1],
                    ['item' => ['id' => 'Pemandu budaya lokal', 'en' => 'Local cultural guide', 'ms' => 'Pemandu budaya tempatan'], 'type' => 'include', 'sort_order' => 2],
                    ['item' => ['id' => 'Workshop kerajinan tangan', 'en' => 'Handicraft workshop', 'ms' => 'Bengkel kraf tangan'], 'type' => 'include', 'sort_order' => 3],
                    ['item' => ['id' => 'Semua makan selama tur', 'en' => 'All meals during the tour', 'ms' => 'Semua hidangan semasa lawatan'], 'type' => 'include', 'sort_order' => 4],
                    ['item' => ['id' => 'Transportasi PP ke desa', 'en' => 'Round-trip transportation to village', 'ms' => 'Pengangkutan pergi balik ke kampung'], 'type' => 'include', 'sort_order' => 5],
                    ['item' => ['id' => 'Biaya perjalanan dari kota asal', 'en' => 'Travel costs from home city', 'ms' => 'Kos perjalanan dari bandar asal'], 'type' => 'exclude', 'sort_order' => 1],
                ],
                'galleries' => [
                    ['image_path' => 'https://images.unsplash.com/photo-1605106702734-205df224ecce?w=1200&q=80&fit=crop', 'caption' => 'Pertunjukan tarian tradisional Dayak', 'sort_order' => 1],
                    ['image_path' => 'https://images.unsplash.com/photo-1472214103451-9374bd1c798e?w=1200&q=80&fit=crop', 'caption' => 'Rumah Betang tradisional Kalimantan', 'sort_order' => 2],
                    ['image_path' => 'https://images.unsplash.com/photo-1544551748-8fad4a4d1db9?w=1200&q=80&fit=crop', 'caption' => 'Hutan adat Kalimantan', 'sort_order' => 3],
                ],
            ],
            [
                'tour' => [
                    'category_id' => $petualangan->id,
                    'name' => [
                        'id' => 'Ekspedisi Sungai Mahakam',
                        'en' => 'Mahakam River Expedition',
                        'ms' => 'Ekspedisi Sungai Mahakam',
                    ],
                    'slug' => [
                        'id' => 'ekspedisi-sungai-mahakam',
                        'en' => 'mahakam-river-expedition',
                        'ms' => 'ekspedisi-sungai-mahakam',
                    ],
                    'description' => [
                        'id' => 'Jelajahi Sungai Mahakam, arteri kehidupan Kalimantan Timur. Berlayar melewati desa-desa terpencil, menyaksikan lumba-lumba air tawar Pesut Mahakam yang langka, dan menyelami kehidupan masyarakat tepi sungai yang unik.',
                        'en' => 'Explore the Mahakam River, the lifeline of East Kalimantan. Sail through remote villages, witness the rare freshwater Irrawaddy dolphin (Pesut Mahakam), and dive into the unique lives of riverside communities.',
                        'ms' => 'Jelajahi Sungai Mahakam, nadi kehidupan Kalimantan Timur. Belayar melalui kampung-kampung terpencil, saksikan lumba-lumba air tawar Pesut Mahakam yang langka, dan selami kehidupan unik masyarakat tepi sungai.',
                    ],
                    'highlights' => [
                        'id' => "Berlayar di Sungai Mahakam dengan perahu kayu\nMenyaksikan Pesut Mahakam (lumba-lumba air tawar)\nKunjungan ke Danau Jempang dan desa Tanjung Isuy\nMenyaksikan matahari terbenam di Sungai Mahakam",
                        'en' => "Sailing on the Mahakam River by wooden boat\nWitnessing the Pesut Mahakam (Irrawaddy dolphin)\nVisit to Lake Jempang and Tanjung Isuy village\nWatching the sunset over the Mahakam River",
                        'ms' => "Belayar di Sungai Mahakam dengan perahu kayu\nMenyaksikan Pesut Mahakam (lumba-lumba air tawar)\nLawatan ke Tasik Jempang dan kampung Tanjung Isuy\nMenyaksikan matahari terbenam di Sungai Mahakam",
                    ],
                    'tour_type' => 'private',
                    'duration_days' => 5,
                    'duration_nights' => 4,
                    'price' => 4200000,
                    'currency' => 'IDR',
                    'difficulty' => 'moderate',
                    'max_pax' => 8,
                    'is_active' => true,
                    'is_featured' => true,
                    'thumbnail' => 'https://images.unsplash.com/photo-1536768139911-e290a59011e4?w=800&q=80&fit=crop',
                ],
                'itineraries' => [
                    [
                        'day_number' => 1,
                        'title' => ['id' => 'Tiba di Samarinda', 'en' => 'Arrival in Samarinda', 'ms' => 'Tiba di Samarinda'],
                        'description' => [
                            'id' => 'Tiba di Samarinda, ibu kota Kalimantan Timur. Check-in hotel dan briefing perjalanan. Malam hari menikmati kuliner khas Samarinda di tepian Sungai Mahakam.',
                            'en' => 'Arrive in Samarinda, the capital of East Kalimantan. Hotel check-in and trip briefing. Evening enjoying local Samarinda cuisine by the Mahakam riverfront.',
                            'ms' => 'Tiba di Samarinda, ibu kota Kalimantan Timur. Daftar masuk hotel dan taklimat perjalanan. Malam menikmati masakan tempatan Samarinda di tepi Sungai Mahakam.',
                        ],
                        'meals_included' => ['id' => 'Makan Malam', 'en' => 'Dinner', 'ms' => 'Makan Malam'],
                        'accommodation' => 'Hotel Samarinda',
                    ],
                    [
                        'day_number' => 2,
                        'title' => ['id' => 'Berlayar ke Tenggarong & Danau Semayang', 'en' => 'Sailing to Tenggarong & Lake Semayang', 'ms' => 'Belayar ke Tenggarong & Tasik Semayang'],
                        'description' => [
                            'id' => 'Naik perahu dan berlayar ke Tenggarong, bekas ibu kota Kesultanan Kutai. Kunjungi Museum Mulawarman. Lanjutkan perjalanan menuju Danau Semayang mencari Pesut Mahakam.',
                            'en' => 'Board the boat and sail to Tenggarong, the former capital of the Kutai Sultanate. Visit the Mulawarman Museum. Continue journey to Lake Semayang in search of the Pesut Mahakam.',
                            'ms' => 'Naiki perahu dan belayar ke Tenggarong, bekas ibu kota Kesultanan Kutai. Lawati Muzium Mulawarman. Teruskan perjalanan ke Tasik Semayang mencari Pesut Mahakam.',
                        ],
                        'meals_included' => ['id' => 'Sarapan, Makan Siang, Makan Malam', 'en' => 'Breakfast, Lunch, Dinner', 'ms' => 'Sarapan, Makan Tengah Hari, Makan Malam'],
                        'accommodation' => 'Penginapan Tepi Sungai',
                    ],
                    [
                        'day_number' => 3,
                        'title' => ['id' => 'Danau Jempang & Desa Tanjung Isuy', 'en' => 'Lake Jempang & Tanjung Isuy Village', 'ms' => 'Tasik Jempang & Kampung Tanjung Isuy'],
                        'description' => [
                            'id' => 'Berlayar ke Danau Jempang, danau terbesar di Kalimantan Timur. Kunjungi desa Tanjung Isuy, desa Dayak Benuaq tradisional. Saksikan tarian Kancet Ledo dan berbagi pengalaman dengan masyarakat lokal.',
                            'en' => 'Sail to Lake Jempang, the largest lake in East Kalimantan. Visit Tanjung Isuy village, a traditional Dayak Benuaq village. Watch the Kancet Ledo dance and share experiences with the local community.',
                            'ms' => 'Belayar ke Tasik Jempang, tasik terbesar di Kalimantan Timur. Lawati kampung Tanjung Isuy, kampung tradisional Dayak Benuaq. Saksikan tarian Kancet Ledo dan berkongsi pengalaman dengan komuniti tempatan.',
                        ],
                        'meals_included' => ['id' => 'Sarapan, Makan Siang, Makan Malam', 'en' => 'Breakfast, Lunch, Dinner', 'ms' => 'Sarapan, Makan Tengah Hari, Makan Malam'],
                        'accommodation' => 'Homestay Desa',
                    ],
                    [
                        'day_number' => 4,
                        'title' => ['id' => 'Sungai Belayan & Kehidupan Pedalaman', 'en' => 'Belayan River & Interior Life', 'ms' => 'Sungai Belayan & Kehidupan Pedalaman'],
                        'description' => [
                            'id' => 'Menyusuri Sungai Belayan, anak sungai Mahakam. Mengunjungi desa-desa terpencil dan berinteraksi langsung dengan masyarakat Dayak pedalaman. Malam barbeque ikan segar tangkapan nelayan lokal.',
                            'en' => 'Navigate along the Belayan River, a tributary of the Mahakam. Visit remote villages and interact directly with interior Dayak communities. Evening barbecue of fresh fish caught by local fishermen.',
                            'ms' => 'Menyusuri Sungai Belayan, anak sungai Mahakam. Melawat kampung-kampung terpencil dan berinteraksi terus dengan komuniti Dayak pedalaman. Malam barbeku ikan segar tangkapan nelayan tempatan.',
                        ],
                        'meals_included' => ['id' => 'Sarapan, Makan Siang, Makan Malam', 'en' => 'Breakfast, Lunch, Dinner', 'ms' => 'Sarapan, Makan Tengah Hari, Makan Malam'],
                        'accommodation' => 'Perahu/Homestay',
                    ],
                    [
                        'day_number' => 5,
                        'title' => ['id' => 'Kembali ke Samarinda & Kepulangan', 'en' => 'Return to Samarinda & Departure', 'ms' => 'Kembali ke Samarinda & Pemulangan'],
                        'description' => [
                            'id' => 'Berlayar kembali ke Samarinda. Waktu bebas untuk berbelanja oleh-oleh khas Kalimantan. Transfer ke bandara untuk penerbangan pulang.',
                            'en' => 'Sail back to Samarinda. Free time to shop for Kalimantan souvenirs. Transfer to the airport for the return flight.',
                            'ms' => 'Belayar kembali ke Samarinda. Masa lapang untuk membeli cenderamata khas Kalimantan. Pindah ke lapangan terbang untuk penerbangan pulang.',
                        ],
                        'meals_included' => ['id' => 'Sarapan, Makan Siang', 'en' => 'Breakfast, Lunch', 'ms' => 'Sarapan, Makan Tengah Hari'],
                        'accommodation' => '-',
                    ],
                ],
                'includes' => [
                    ['item' => ['id' => 'Sewa perahu selama 4 hari', 'en' => '4-day boat rental', 'ms' => 'Sewa perahu 4 hari'], 'type' => 'include', 'sort_order' => 1],
                    ['item' => ['id' => 'Pemandu wisata berpengalaman', 'en' => 'Experienced tour guide', 'ms' => 'Pemandu pelancongan berpengalaman'], 'type' => 'include', 'sort_order' => 2],
                    ['item' => ['id' => 'Akomodasi (4 malam)', 'en' => 'Accommodation (4 nights)', 'ms' => 'Penginapan (4 malam)'], 'type' => 'include', 'sort_order' => 3],
                    ['item' => ['id' => 'Seluruh makan selama tur', 'en' => 'All meals during the tour', 'ms' => 'Semua hidangan semasa lawatan'], 'type' => 'include', 'sort_order' => 4],
                    ['item' => ['id' => 'Tiket masuk museum dan objek wisata', 'en' => 'Museum and attraction entrance fees', 'ms' => 'Yuran masuk muzium dan tempat tarikan'], 'type' => 'include', 'sort_order' => 5],
                    ['item' => ['id' => 'Tiket pesawat ke Samarinda', 'en' => 'Flight tickets to Samarinda', 'ms' => 'Tiket penerbangan ke Samarinda'], 'type' => 'exclude', 'sort_order' => 1],
                    ['item' => ['id' => 'Asuransi perjalanan', 'en' => 'Travel insurance', 'ms' => 'Insurans perjalanan'], 'type' => 'exclude', 'sort_order' => 2],
                ],
                'galleries' => [
                    ['image_path' => 'https://images.unsplash.com/photo-1536768139911-e290a59011e4?w=1200&q=80&fit=crop', 'caption' => 'Sungai Mahakam di pagi hari', 'sort_order' => 1],
                    ['image_path' => 'https://images.unsplash.com/photo-1472214103451-9374bd1c798e?w=1200&q=80&fit=crop', 'caption' => 'Danau Jempang, Kalimantan Timur', 'sort_order' => 2],
                    ['image_path' => 'https://images.unsplash.com/photo-1497294815431-9365093b7331?w=1200&q=80&fit=crop', 'caption' => 'Hutan di tepi Sungai Mahakam', 'sort_order' => 3],
                    ['image_path' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&q=80&fit=crop', 'caption' => 'Pemandangan dari atas sungai', 'sort_order' => 4],
                ],
            ],
            [
                'tour' => [
                    'category_id' => $alam->id,
                    'name' => [
                        'id' => 'Danau Labuan Cermin',
                        'en' => 'Labuan Cermin Mirror Lake',
                        'ms' => 'Tasik Cermin Labuan Cermin',
                    ],
                    'slug' => [
                        'id' => 'danau-labuan-cermin',
                        'en' => 'labuan-cermin-mirror-lake',
                        'ms' => 'tasik-cermin-labuan-cermin',
                    ],
                    'description' => [
                        'id' => 'Kunjungi Danau Labuan Cermin yang menakjubkan di Berau, Kalimantan Timur. Danau unik ini memiliki dua lapisan air — air tawar di atas dan air asin di bawah — menciptakan efek cermin yang memukau. Snorkeling di dua ekosistem sekaligus!',
                        'en' => 'Visit the amazing Labuan Cermin lake in Berau, East Kalimantan. This unique lake has two layers of water — freshwater on top and saltwater below — creating a breathtaking mirror effect. Snorkel in two ecosystems at once!',
                        'ms' => 'Lawati Tasik Labuan Cermin yang menakjubkan di Berau, Kalimantan Timur. Tasik unik ini mempunyai dua lapisan air — air tawar di atas dan air masin di bawah — mewujudkan kesan cermin yang mempesonakan. Snorkeling dalam dua ekosistem serentak!',
                    ],
                    'highlights' => [
                        'id' => "Snorkeling di danau dua lapisan yang unik\nMenikmati jernihnya air danau seperti kristal\nTrekking ringan melalui hutan bakau\nFoto terbaik dengan efek cermin air",
                        'en' => "Snorkeling in the unique two-layered lake\nEnjoy crystal clear lake water\nLight trekking through mangrove forest\nBest photos with the water mirror effect",
                        'ms' => "Snorkeling di tasik dua lapisan yang unik\nMenikmati jernihnya air tasik seperti kristal\nTrekking ringan melalui hutan bakau\nFoto terbaik dengan kesan cermin air",
                    ],
                    'tour_type' => 'group',
                    'duration_days' => 2,
                    'duration_nights' => 1,
                    'price' => 75,
                    'currency' => 'USD',
                    'difficulty' => 'easy',
                    'max_pax' => 20,
                    'is_active' => true,
                    'is_featured' => false,
                    'thumbnail' => 'https://images.unsplash.com/photo-1552083375-1447ce886485?w=800&q=80&fit=crop',
                ],
                'itineraries' => [
                    [
                        'day_number' => 1,
                        'title' => ['id' => 'Perjalanan & Snorkeling Danau', 'en' => 'Journey & Lake Snorkeling', 'ms' => 'Perjalanan & Snorkeling Tasik'],
                        'description' => [
                            'id' => 'Berangkat dari Tanjung Redeb menuju Labuan Cermin. Tiba dan langsung snorkeling di danau. Nikmati fenomena dua lapisan air yang unik. Malam menginap di penginapan lokal.',
                            'en' => 'Depart from Tanjung Redeb to Labuan Cermin. Arrive and snorkel directly in the lake. Enjoy the unique two-layer water phenomenon. Overnight at a local guesthouse.',
                            'ms' => 'Bertolak dari Tanjung Redeb ke Labuan Cermin. Tiba dan terus snorkeling di tasik. Nikmati fenomena dua lapisan air yang unik. Bermalam di penginapan tempatan.',
                        ],
                        'meals_included' => ['id' => 'Makan Siang, Makan Malam', 'en' => 'Lunch, Dinner', 'ms' => 'Makan Tengah Hari, Makan Malam'],
                        'accommodation' => 'Penginapan Lokal Labuan Cermin',
                    ],
                    [
                        'day_number' => 2,
                        'title' => ['id' => 'Eksplorasi Pagi & Kepulangan', 'en' => 'Morning Exploration & Departure', 'ms' => 'Penerokaan Pagi & Pemulangan'],
                        'description' => [
                            'id' => 'Pagi hari eksplorasi danau saat paling jernih. Trekking ringan di hutan bakau sekitar danau. Makan siang dan kembali ke Tanjung Redeb.',
                            'en' => 'Morning lake exploration when the water is clearest. Light trekking through the mangrove forest around the lake. Lunch and return to Tanjung Redeb.',
                            'ms' => 'Penerokaan tasik pagi semasa air paling jernih. Trekking ringan di hutan bakau sekitar tasik. Makan tengah hari dan kembali ke Tanjung Redeb.',
                        ],
                        'meals_included' => ['id' => 'Sarapan, Makan Siang', 'en' => 'Breakfast, Lunch', 'ms' => 'Sarapan, Makan Tengah Hari'],
                        'accommodation' => '-',
                    ],
                ],
                'includes' => [
                    ['item' => ['id' => 'Transportasi PP dari Tanjung Redeb', 'en' => 'Round-trip transport from Tanjung Redeb', 'ms' => 'Pengangkutan pergi balik dari Tanjung Redeb'], 'type' => 'include', 'sort_order' => 1],
                    ['item' => ['id' => 'Peralatan snorkeling', 'en' => 'Snorkeling equipment', 'ms' => 'Peralatan snorkeling'], 'type' => 'include', 'sort_order' => 2],
                    ['item' => ['id' => 'Penginapan 1 malam', 'en' => '1-night accommodation', 'ms' => 'Penginapan 1 malam'], 'type' => 'include', 'sort_order' => 3],
                    ['item' => ['id' => 'Makan (dari makan siang hari 1)', 'en' => 'Meals (from lunch on day 1)', 'ms' => 'Hidangan (dari makan tengah hari 1)'], 'type' => 'include', 'sort_order' => 4],
                    ['item' => ['id' => 'Pemandu lokal', 'en' => 'Local guide', 'ms' => 'Pemandu tempatan'], 'type' => 'include', 'sort_order' => 5],
                    ['item' => ['id' => 'Transportasi ke Tanjung Redeb', 'en' => 'Transportation to Tanjung Redeb', 'ms' => 'Pengangkutan ke Tanjung Redeb'], 'type' => 'exclude', 'sort_order' => 1],
                ],
                'galleries' => [
                    ['image_path' => 'https://images.unsplash.com/photo-1552083375-1447ce886485?w=1200&q=80&fit=crop', 'caption' => 'Jernihnya air Danau Labuan Cermin', 'sort_order' => 1],
                    ['image_path' => 'https://images.unsplash.com/photo-1559494007-a0ef0c3c85a4?w=1200&q=80&fit=crop', 'caption' => 'Snorkeling di danau dua lapisan', 'sort_order' => 2],
                    ['image_path' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1200&q=80&fit=crop', 'caption' => 'Hutan bakau di sekitar danau', 'sort_order' => 3],
                ],
            ],
        ];

        foreach ($tours as $data) {
            $tour = Tour::create($data['tour']);

            foreach ($data['itineraries'] as $itinerary) {
                TourItinerary::create(['tour_id' => $tour->id] + $itinerary);
            }

            foreach ($data['includes'] as $include) {
                TourInclude::create(['tour_id' => $tour->id] + $include);
            }

            foreach ($data['galleries'] as $gallery) {
                TourGallery::create(['tour_id' => $tour->id] + $gallery);
            }
        }
    }
}
