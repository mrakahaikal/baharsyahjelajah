<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $panduan = PostCategory::whereJsonContains('slug->id', 'panduan-wisata')->first();
        $destinasi = PostCategory::whereJsonContains('slug->id', 'destinasi')->first();
        $berita = PostCategory::whereJsonContains('slug->id', 'berita-promo')->first();
        $alam = PostCategory::whereJsonContains('slug->id', 'alam-satwa-liar')->first();

        $posts = [
            [
                'post_category_id' => $destinasi->id,
                'user_id' => $admin->id,
                'title' => [
                    'id' => '10 Alasan Mengapa Anda Harus Mengunjungi Taman Nasional Tanjung Puting',
                    'en' => '10 Reasons Why You Must Visit Tanjung Puting National Park',
                    'ms' => '10 Sebab Mengapa Anda Perlu Melawat Taman Negara Tanjung Puting',
                ],
                'slug' => [
                    'id' => '10-alasan-kunjungi-tanjung-puting',
                    'en' => '10-reasons-visit-tanjung-puting',
                    'ms' => '10-sebab-lawat-tanjung-puting',
                ],
                'excerpt' => [
                    'id' => 'Taman Nasional Tanjung Puting adalah salah satu destinasi eco-tourism terbaik di dunia. Temukan 10 alasan mengapa tempat ini wajib masuk bucket list perjalanan Anda.',
                    'en' => 'Tanjung Puting National Park is one of the best eco-tourism destinations in the world. Discover 10 reasons why this place must be on your travel bucket list.',
                    'ms' => 'Taman Negara Tanjung Puting adalah salah satu destinasi eko-pelancongan terbaik di dunia. Temui 10 sebab mengapa tempat ini mesti masuk dalam senarai perjalanan anda.',
                ],
                'content' => [
                    'id' => "<h2>Surga Orangutan di Jantung Kalimantan</h2>\n\n<p>Taman Nasional Tanjung Puting, yang terletak di Kalimantan Tengah, merupakan salah satu kawasan konservasi paling penting di dunia. Dengan luas lebih dari 415.000 hektar, taman nasional ini adalah rumah bagi ribuan orangutan Kalimantan (<em>Pongo pygmaeus</em>) yang hidup bebas di habitat alaminya.</p>\n\n<h3>1. Menyaksikan Orangutan Liar</h3>\n<p>Kesempatan melihat orangutan liar di Camp Leakey adalah pengalaman yang tak terlupakan. Setiap hari, ratusan orangutan datang ke feeding station untuk mendapatkan suplemen makanan.</p>\n\n<h3>2. Petualangan Klotok yang Unik</h3>\n<p>Bermalam di atas klotok — perahu kayu tradisional Kalimantan — sembari mengapung di tengah hutan adalah pengalaman yang hanya bisa Anda dapatkan di sini.</p>\n\n<h3>3. Keanekaragaman Hayati Luar Biasa</h3>\n<p>Selain orangutan, Anda bisa menemukan bekantan (proboscis monkey), buaya muara, biawak, dan ratusan spesies burung langka termasuk rangkong.</p>\n\n<h3>4. Camp Leakey yang Legendaris</h3>\n<p>Didirikan oleh Dr. Biruté Galdikas pada 1971, Camp Leakey adalah pusat penelitian orangutan terlama di dunia yang masih beroperasi.</p>",
                    'en' => "<h2>The Orangutan Paradise in the Heart of Borneo</h2>\n\n<p>Tanjung Puting National Park, located in Central Kalimantan, is one of the most important conservation areas in the world. Covering more than 415,000 hectares, this national park is home to thousands of Bornean orangutans (<em>Pongo pygmaeus</em>) living freely in their natural habitat.</p>\n\n<h3>1. Witness Wild Orangutans</h3>\n<p>The chance to see wild orangutans at Camp Leakey is an unforgettable experience. Every day, hundreds of orangutans come to the feeding station to receive food supplements.</p>\n\n<h3>2. The Unique Klotok Adventure</h3>\n<p>Spending the night on a klotok — a traditional Bornean wooden houseboat — while floating in the middle of the jungle is an experience you can only have here.</p>\n\n<h3>3. Extraordinary Biodiversity</h3>\n<p>Besides orangutans, you can find proboscis monkeys, saltwater crocodiles, monitor lizards, and hundreds of rare bird species including hornbills.</p>\n\n<h3>4. The Legendary Camp Leakey</h3>\n<p>Founded by Dr. Biruté Galdikas in 1971, Camp Leakey is the world's longest-running orangutan research station still in operation.</p>",
                    'ms' => "<h2>Syurga Orang Utan di Jantung Borneo</h2>\n\n<p>Taman Negara Tanjung Puting, yang terletak di Kalimantan Tengah, merupakan salah satu kawasan pemuliharaan paling penting di dunia. Dengan keluasan lebih dari 415,000 hektar, taman negara ini adalah rumah kepada ribuan orang utan Borneo (<em>Pongo pygmaeus</em>) yang hidup bebas di habitat semula jadi mereka.</p>\n\n<h3>1. Menyaksikan Orang Utan Liar</h3>\n<p>Peluang melihat orang utan liar di Camp Leakey adalah pengalaman yang tidak dapat dilupakan. Setiap hari, ratusan orang utan datang ke stesen pemberian makan untuk mendapatkan makanan tambahan.</p>\n\n<h3>2. Pengembaraan Klotok yang Unik</h3>\n<p>Bermalam di atas klotok — perahu kayu tradisional Borneo — sambil terapung di tengah hutan adalah pengalaman yang hanya boleh anda alami di sini.</p>\n\n<h3>3. Kepelbagaian Biologi yang Luar Biasa</h3>\n<p>Selain orang utan, anda boleh menemui monyet belanda, buaya air masin, biawak, dan ratusan spesies burung langka termasuk burung enggang.</p>",
                ],
                'cover_image' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=1200&q=80&fit=crop',
                'status' => 'published',
                'published_at' => now()->subDays(10),
            ],
            [
                'post_category_id' => $panduan->id,
                'user_id' => $admin->id,
                'title' => [
                    'id' => 'Panduan Lengkap Berkunjung ke Kepulauan Derawan: Tips, Waktu Terbaik & Cara Sampai',
                    'en' => 'Complete Guide to Visiting the Derawan Islands: Tips, Best Time & How to Get There',
                    'ms' => 'Panduan Lengkap Melawat Kepulauan Derawan: Tips, Masa Terbaik & Cara Ke Sana',
                ],
                'slug' => [
                    'id' => 'panduan-lengkap-kepulauan-derawan',
                    'en' => 'complete-guide-derawan-islands',
                    'ms' => 'panduan-lengkap-kepulauan-derawan',
                ],
                'excerpt' => [
                    'id' => 'Kepulauan Derawan adalah surga bahari tersembunyi di Kalimantan Timur. Panduan ini akan membantu Anda merencanakan perjalanan sempurna ke salah satu destinasi diving terbaik di Asia Tenggara.',
                    'en' => 'The Derawan Islands are a hidden marine paradise in East Kalimantan. This guide will help you plan the perfect trip to one of the best diving destinations in Southeast Asia.',
                    'ms' => 'Kepulauan Derawan adalah syurga marin tersembunyi di Kalimantan Timur. Panduan ini akan membantu anda merancang perjalanan sempurna ke salah satu destinasi menyelam terbaik di Asia Tenggara.',
                ],
                'content' => [
                    'id' => "<h2>Mengenal Kepulauan Derawan</h2>\n\n<p>Kepulauan Derawan terdiri dari sekitar 31 pulau yang tersebar di Kabupaten Berau, Kalimantan Timur. Gugusan pulau ini terkenal dengan keindahan bawah lautnya yang luar biasa, menjadikannya salah satu destinasi snorkeling dan diving terbaik di Indonesia bahkan dunia.</p>\n\n<h3>Pulau-Pulau Utama</h3>\n<ul>\n<li><strong>Pulau Derawan</strong> — Pusat aktivitas, tempat menginap, dan titik awal island hopping</li>\n<li><strong>Pulau Kakaban</strong> — Rumah Danau Kakaban dengan ubur-ubur tak bersengat yang langka</li>\n<li><strong>Pulau Sangalaki</strong> — Spot terbaik melihat manta ray dan penyu</li>\n<li><strong>Pulau Maratua</strong> — Pantai paling eksotis dengan air biru jernih</li>\n</ul>\n\n<h3>Waktu Terbaik Berkunjung</h3>\n<p>Musim terbaik untuk mengunjungi Kepulauan Derawan adalah antara <strong>Maret hingga November</strong>, saat cuaca cerah dan kondisi laut tenang. Hindari bulan Desember-Februari karena musim hujan dan gelombang besar.</p>\n\n<h3>Cara Menuju Derawan</h3>\n<p>Rute paling umum: Terbang ke Bandara Kalimarau (BEJ) di Berau, lalu naik speedboat dari Pelabuhan Tanjung Batu menuju Pulau Derawan (sekitar 45 menit).</p>",
                    'en' => "<h2>Getting to Know the Derawan Islands</h2>\n\n<p>The Derawan Islands consist of approximately 31 islands scattered across Berau Regency, East Kalimantan. This archipelago is renowned for its extraordinary underwater beauty, making it one of the best snorkeling and diving destinations in Indonesia and even the world.</p>\n\n<h3>Main Islands</h3>\n<ul>\n<li><strong>Derawan Island</strong> — Activity hub, accommodation, and starting point for island hopping</li>\n<li><strong>Kakaban Island</strong> — Home to Lake Kakaban with its rare stingless jellyfish</li>\n<li><strong>Sangalaki Island</strong> — Best spot to see manta rays and sea turtles</li>\n<li><strong>Maratua Island</strong> — Most exotic beach with crystal blue water</li>\n</ul>\n\n<h3>Best Time to Visit</h3>\n<p>The best season to visit the Derawan Islands is between <strong>March and November</strong>, when the weather is clear and sea conditions are calm. Avoid December-February due to rainy season and large waves.</p>\n\n<h3>How to Get to Derawan</h3>\n<p>The most common route: Fly to Kalimarau Airport (BEJ) in Berau, then take a speedboat from Tanjung Batu Port to Derawan Island (about 45 minutes).</p>",
                    'ms' => "<h2>Mengenali Kepulauan Derawan</h2>\n\n<p>Kepulauan Derawan terdiri daripada kira-kira 31 pulau yang tersebar di Kabupaten Berau, Kalimantan Timur. Gugusan pulau ini terkenal dengan keindahan bawah lautnya yang luar biasa, menjadikannya salah satu destinasi snorkeling dan menyelam terbaik di Indonesia malah di dunia.</p>\n\n<h3>Pulau-Pulau Utama</h3>\n<ul>\n<li><strong>Pulau Derawan</strong> — Pusat aktiviti, penginapan, dan titik permulaan lawatan pulau</li>\n<li><strong>Pulau Kakaban</strong> — Rumah Tasik Kakaban dengan obor-obor tanpa sengat yang langka</li>\n<li><strong>Pulau Sangalaki</strong> — Tempat terbaik melihat pari manta dan penyu</li>\n<li><strong>Pulau Maratua</strong> — Pantai paling eksotik dengan air biru jernih</li>\n</ul>\n\n<h3>Masa Terbaik untuk Berkunjung</h3>\n<p>Musim terbaik untuk melawat Kepulauan Derawan adalah antara <strong>Mac hingga November</strong>, apabila cuaca cerah dan keadaan laut tenang. Elakkan bulan Disember-Februari kerana musim hujan dan ombak besar.</p>",
                ],
                'cover_image' => 'https://images.unsplash.com/photo-1559494007-a0ef0c3c85a4?w=1200&q=80&fit=crop',
                'status' => 'published',
                'published_at' => now()->subDays(7),
            ],
            [
                'post_category_id' => $alam->id,
                'user_id' => $admin->id,
                'title' => [
                    'id' => 'Pesut Mahakam: Lumba-Lumba Air Tawar Langka yang Terancam Punah',
                    'en' => 'Pesut Mahakam: The Rare Freshwater Dolphin on the Brink of Extinction',
                    'ms' => 'Pesut Mahakam: Lumba-Lumba Air Tawar Langka yang Terancam Pupus',
                ],
                'slug' => [
                    'id' => 'pesut-mahakam-lumba-lumba-air-tawar',
                    'en' => 'pesut-mahakam-rare-freshwater-dolphin',
                    'ms' => 'pesut-mahakam-lumba-lumba-air-tawar-langka',
                ],
                'excerpt' => [
                    'id' => 'Pesut Mahakam (Orcaella brevirostris) adalah lumba-lumba air tawar yang hanya bisa ditemukan di Sungai Mahakam. Populasinya yang semakin berkurang menjadikannya salah satu mamalia paling terancam di dunia.',
                    'en' => 'The Pesut Mahakam (Orcaella brevirostris) is a freshwater dolphin only found in the Mahakam River. Its dwindling population makes it one of the most threatened mammals in the world.',
                    'ms' => 'Pesut Mahakam (Orcaella brevirostris) adalah lumba-lumba air tawar yang hanya boleh ditemui di Sungai Mahakam. Populasinya yang semakin berkurangan menjadikannya salah satu mamalia paling terancam di dunia.',
                ],
                'content' => [
                    'id' => "<h2>Mengenal Pesut Mahakam</h2>\n\n<p>Pesut Mahakam, atau Irrawaddy Dolphin (<em>Orcaella brevirostris</em>), adalah salah satu spesies paling langka dan terancam di dunia. Hewan yang hidup di perairan tawar ini hanya dapat ditemukan di beberapa sungai besar di Asia, dengan populasi Sungai Mahakam di Kalimantan Timur menjadi salah satu yang paling kritis.</p>\n\n<h3>Mengapa Pesut Mahakam Terancam?</h3>\n<p>Menurut data terbaru, hanya tersisa sekitar 80 individu Pesut Mahakam di Sungai Mahakam. Ancaman utama yang dihadapi meliputi:</p>\n<ul>\n<li>Jaring ikan yang menjebak (bycatch)</li>\n<li>Degradasi habitat akibat deforestasi</li>\n<li>Polusi industri dan pertambangan</li>\n<li>Lalu lintas perahu yang padat</li>\n</ul>\n\n<h3>Upaya Pelestarian</h3>\n<p>Berbagai organisasi konservasi, termasuk WWF Indonesia, bekerja keras untuk melindungi Pesut Mahakam. Program pendidikan masyarakat lokal dan zona perlindungan khusus telah dibentuk di sepanjang Sungai Mahakam.</p>\n\n<h3>Cara Melihat Pesut Mahakam</h3>\n<p>Spot terbaik untuk melihat Pesut Mahakam adalah di sekitar Danau Semayang dan Danau Melintang, di mana kawanan kecil pesut masih sering terlihat di pagi dan sore hari.</p>",
                    'en' => "<h2>Getting to Know the Pesut Mahakam</h2>\n\n<p>The Pesut Mahakam, or Irrawaddy Dolphin (<em>Orcaella brevirostris</em>), is one of the rarest and most threatened species in the world. This freshwater animal can only be found in a few major rivers in Asia, with the Mahakam River population in East Kalimantan being one of the most critical.</p>\n\n<h3>Why is the Pesut Mahakam Threatened?</h3>\n<p>According to the latest data, only around 80 individual Pesut Mahakam remain in the Mahakam River. The main threats they face include:</p>\n<ul>\n<li>Entanglement in fishing nets (bycatch)</li>\n<li>Habitat degradation due to deforestation</li>\n<li>Industrial and mining pollution</li>\n<li>Heavy boat traffic</li>\n</ul>\n\n<h3>Conservation Efforts</h3>\n<p>Various conservation organizations, including WWF Indonesia, are working hard to protect the Pesut Mahakam. Community education programs and special protection zones have been established along the Mahakam River.</p>\n\n<h3>How to See the Pesut Mahakam</h3>\n<p>The best spots to see the Pesut Mahakam are around Lake Semayang and Lake Melintang, where small groups of pesut are still frequently spotted in the morning and late afternoon.</p>",
                    'ms' => "<h2>Mengenali Pesut Mahakam</h2>\n\n<p>Pesut Mahakam, atau Irrawaddy Dolphin (<em>Orcaella brevirostris</em>), adalah salah satu spesies paling langka dan terancam di dunia. Haiwan air tawar ini hanya boleh ditemui di beberapa sungai besar di Asia, dengan populasi Sungai Mahakam di Kalimantan Timur menjadi salah satu yang paling kritikal.</p>\n\n<h3>Mengapa Pesut Mahakam Terancam?</h3>\n<p>Menurut data terkini, hanya tinggal kira-kira 80 individu Pesut Mahakam di Sungai Mahakam. Ancaman utama yang dihadapi termasuk:</p>\n<ul>\n<li>Terjerat dalam jaring ikan (tangkapan sampingan)</li>\n<li>Degradasi habitat akibat penebangan hutan</li>\n<li>Pencemaran industri dan perlombongan</li>\n<li>Trafik perahu yang padat</li>\n</ul>",
                ],
                'cover_image' => 'https://images.unsplash.com/photo-1536768139911-e290a59011e4?w=1200&q=80&fit=crop',
                'status' => 'published',
                'published_at' => now()->subDays(5),
            ],
            [
                'post_category_id' => $berita->id,
                'user_id' => $admin->id,
                'title' => [
                    'id' => 'Promo Spesial Lebaran: Diskon 20% untuk Semua Paket Wisata Kalimantan',
                    'en' => 'Special Eid Promotion: 20% Discount on All Borneo Tour Packages',
                    'ms' => 'Promosi Khas Hari Raya: Diskaun 20% untuk Semua Pakej Pelancongan Borneo',
                ],
                'slug' => [
                    'id' => 'promo-lebaran-diskon-paket-wisata',
                    'en' => 'eid-promotion-discount-tour-packages',
                    'ms' => 'promosi-hari-raya-diskaun-pakej-pelancongan',
                ],
                'excerpt' => [
                    'id' => 'Rayakan Lebaran dengan petualangan seru di Kalimantan! Baharsyah Jelajah menawarkan diskon spesial 20% untuk semua paket wisata yang dipesan selama periode promosi.',
                    'en' => 'Celebrate Eid with exciting adventures in Borneo! Baharsyah Jelajah offers a special 20% discount on all tour packages booked during the promotional period.',
                    'ms' => 'Raikan Hari Raya dengan pengembaraan seru di Borneo! Baharsyah Jelajah menawarkan diskaun khas 20% untuk semua pakej pelancongan yang ditempah semasa tempoh promosi.',
                ],
                'content' => [
                    'id' => "<h2>Promo Spesial Lebaran dari Baharsyah Jelajah!</h2>\n\n<p>Menyambut Hari Raya Idul Fitri, Baharsyah Jelajah dengan bangga mempersembahkan promo spesial yang sayang untuk dilewatkan! Dapatkan <strong>diskon 20%</strong> untuk semua paket wisata pilihan kami.</p>\n\n<h3>Paket yang Termasuk dalam Promo</h3>\n<ul>\n<li>✅ Ekspedisi Orangutan Tanjung Puting</li>\n<li>✅ Snorkeling & Diving Kepulauan Derawan</li>\n<li>✅ Budaya & Tradisi Suku Dayak</li>\n<li>✅ Ekspedisi Sungai Mahakam</li>\n<li>✅ Danau Labuan Cermin</li>\n</ul>\n\n<h3>Syarat dan Ketentuan</h3>\n<ul>\n<li>Promo berlaku untuk pemesanan selama periode Lebaran</li>\n<li>Perjalanan dapat dilaksanakan hingga 3 bulan setelah pemesanan</li>\n<li>Tidak dapat digabungkan dengan promo lain</li>\n<li>Minimum 2 peserta per grup</li>\n</ul>\n\n<p>Jangan lewatkan kesempatan emas ini! Hubungi kami segera untuk pemesanan dan informasi lebih lanjut.</p>",
                    'en' => "<h2>Special Eid Promotion from Baharsyah Jelajah!</h2>\n\n<p>In celebration of Eid al-Fitr, Baharsyah Jelajah proudly presents a special promotion you won't want to miss! Get a <strong>20% discount</strong> on all our selected tour packages.</p>\n\n<h3>Packages Included in the Promotion</h3>\n<ul>\n<li>✅ Tanjung Puting Orangutan Expedition</li>\n<li>✅ Derawan Islands Snorkeling & Diving</li>\n<li>✅ Dayak Tribe Culture & Traditions</li>\n<li>✅ Mahakam River Expedition</li>\n<li>✅ Labuan Cermin Mirror Lake</li>\n</ul>\n\n<h3>Terms and Conditions</h3>\n<ul>\n<li>Promotion valid for bookings during the Eid period</li>\n<li>Travel can be taken up to 3 months after booking</li>\n<li>Cannot be combined with other promotions</li>\n<li>Minimum 2 participants per group</li>\n</ul>\n\n<p>Don't miss this golden opportunity! Contact us immediately for bookings and further information.</p>",
                    'ms' => "<h2>Promosi Khas Hari Raya dari Baharsyah Jelajah!</h2>\n\n<p>Sempena menyambut Hari Raya Aidilfitri, Baharsyah Jelajah dengan bangga mempersembahkan promosi khas yang sayang untuk dilepaskan! Dapatkan <strong>diskaun 20%</strong> untuk semua pakej pelancongan pilihan kami.</p>\n\n<h3>Pakej yang Termasuk dalam Promosi</h3>\n<ul>\n<li>✅ Ekspedisi Orang Utan Tanjung Puting</li>\n<li>✅ Snorkeling & Menyelam Kepulauan Derawan</li>\n<li>✅ Budaya & Tradisi Suku Dayak</li>\n<li>✅ Ekspedisi Sungai Mahakam</li>\n<li>✅ Tasik Cermin Labuan Cermin</li>\n</ul>\n\n<h3>Syarat dan Ketentuan</h3>\n<ul>\n<li>Promosi sah untuk tempahan semasa tempoh Hari Raya</li>\n<li>Perjalanan boleh dilaksanakan sehingga 3 bulan selepas tempahan</li>\n<li>Tidak boleh digabungkan dengan promosi lain</li>\n<li>Minimum 2 peserta setiap kumpulan</li>\n</ul>",
                ],
                'cover_image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1200&q=80&fit=crop',
                'status' => 'published',
                'published_at' => now()->subDays(3),
            ],
            [
                'post_category_id' => $panduan->id,
                'user_id' => $admin->id,
                'title' => [
                    'id' => 'Tips Persiapan Perjalanan ke Pedalaman Kalimantan: Apa Saja yang Harus Dibawa?',
                    'en' => 'Travel Preparation Tips for the Borneo Interior: What Should You Pack?',
                    'ms' => 'Tips Persediaan Perjalanan ke Pedalaman Borneo: Apa yang Perlu Dibawa?',
                ],
                'slug' => [
                    'id' => 'tips-persiapan-perjalanan-pedalaman-kalimantan',
                    'en' => 'travel-preparation-tips-borneo-interior',
                    'ms' => 'tips-persediaan-perjalanan-pedalaman-borneo',
                ],
                'excerpt' => [
                    'id' => 'Berwisata ke pedalaman Kalimantan membutuhkan persiapan yang matang. Berikut panduan lengkap perlengkapan dan persiapan yang perlu Anda lakukan sebelum berangkat.',
                    'en' => 'Traveling to the Borneo interior requires thorough preparation. Here is a complete guide to the equipment and preparations you need to make before you depart.',
                    'ms' => 'Melancong ke pedalaman Borneo memerlukan persediaan yang teliti. Berikut panduan lengkap peralatan dan persediaan yang perlu anda lakukan sebelum bertolak.',
                ],
                'content' => [
                    'id' => "<h2>Persiapan Penting Sebelum ke Pedalaman Kalimantan</h2>\n\n<p>Kalimantan, pulau terbesar ketiga di dunia, menawarkan pengalaman wisata alam yang tak tertandingi. Namun, perjalanan ke pedalaman membutuhkan persiapan yang lebih matang dibandingkan wisata biasa.</p>\n\n<h3>Perlengkapan Wajib</h3>\n\n<h4>🦟 Perlindungan dari Serangga</h4>\n<ul>\n<li>Obat nyamuk dengan kandungan DEET minimal 30%</li>\n<li>Pakaian lengan panjang dan celana panjang berwarna netral</li>\n<li>Kelambu portable untuk tidur</li>\n</ul>\n\n<h4>👟 Alas Kaki</h4>\n<ul>\n<li>Sepatu trekking waterproof dengan grip kuat</li>\n<li>Sandal air untuk aktivitas di sungai</li>\n</ul>\n\n<h4>🏥 Obat-obatan</h4>\n<ul>\n<li>Obat anti-malaria (konsultasi dokter terlebih dahulu)</li>\n<li>Obat diare dan sakit perut</li>\n<li>Plester, perban, dan antiseptik</li>\n<li>Obat alergi</li>\n</ul>\n\n<h4>📱 Elektronik</h4>\n<ul>\n<li>Power bank berkapasitas besar</li>\n<li>Kamera waterproof atau pelindung kamera</li>\n<li>Headlamp dengan baterai cadangan</li>\n</ul>\n\n<h3>Tips Keselamatan</h3>\n<p>Selalu informasikan rencana perjalanan Anda kepada keluarga atau teman. Gunakan pemandu lokal yang berpengalaman dan patuhi semua instruksi pemandu selama di lapangan.</p>",
                    'en' => "<h2>Important Preparations Before Visiting Borneo's Interior</h2>\n\n<p>Borneo, the world's third-largest island, offers an unmatched natural tourism experience. However, travel to the interior requires more thorough preparation than ordinary tourism.</p>\n\n<h3>Essential Equipment</h3>\n\n<h4>🦟 Insect Protection</h4>\n<ul>\n<li>Insect repellent with at least 30% DEET content</li>\n<li>Long-sleeved shirts and long trousers in neutral colors</li>\n<li>Portable mosquito net for sleeping</li>\n</ul>\n\n<h4>👟 Footwear</h4>\n<ul>\n<li>Waterproof trekking shoes with strong grip</li>\n<li>Water sandals for river activities</li>\n</ul>\n\n<h4>🏥 Medication</h4>\n<ul>\n<li>Anti-malaria medication (consult a doctor first)</li>\n<li>Diarrhea and stomach medicine</li>\n<li>Band-aids, bandages, and antiseptic</li>\n<li>Allergy medication</li>\n</ul>\n\n<h4>📱 Electronics</h4>\n<ul>\n<li>High-capacity power bank</li>\n<li>Waterproof camera or camera protector</li>\n<li>Headlamp with spare batteries</li>\n</ul>\n\n<h3>Safety Tips</h3>\n<p>Always inform your family or friends about your travel plans. Use experienced local guides and follow all guide instructions while in the field.</p>",
                    'ms' => "<h2>Persediaan Penting Sebelum ke Pedalaman Borneo</h2>\n\n<p>Borneo, pulau ketiga terbesar di dunia, menawarkan pengalaman pelancongan alam yang tiada tandingan. Namun, perjalanan ke pedalaman memerlukan persediaan yang lebih teliti berbanding pelancongan biasa.</p>\n\n<h3>Peralatan Wajib</h3>\n\n<h4>🦟 Perlindungan daripada Serangga</h4>\n<ul>\n<li>Penghalau serangga dengan kandungan DEET sekurang-kurangnya 30%</li>\n<li>Baju lengan panjang dan seluar panjang berwarna neutral</li>\n<li>Kelambu mudah alih untuk tidur</li>\n</ul>\n\n<h4>👟 Kasut</h4>\n<ul>\n<li>Kasut trekking kalis air dengan cengkaman kuat</li>\n<li>Sandal air untuk aktiviti di sungai</li>\n</ul>\n\n<h4>🏥 Ubat-ubatan</h4>\n<ul>\n<li>Ubat anti-malaria (berunding dengan doktor terlebih dahulu)</li>\n<li>Ubat cirit-birit dan sakit perut</li>\n<li>Plaster, pembalut, dan antiseptik</li>\n</ul>",
                ],
                'cover_image' => 'https://images.unsplash.com/photo-1544551748-8fad4a4d1db9?w=1200&q=80&fit=crop',
                'status' => 'published',
                'published_at' => now()->subDays(1),
            ],
        ];

        foreach ($posts as $post) {
            Post::create($post);
        }
    }
}
