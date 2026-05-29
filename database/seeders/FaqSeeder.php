<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            // ── Umum ─────────────────────────────────────────────────────
            [
                'category'   => 'general',
                'sort_order' => 1,
                'question'   => ['id' => 'Apa itu Baharsyah Jelajah?', 'en' => 'What is Baharsyah Jelajah?', 'ms' => 'Apakah itu Baharsyah Jelajah?'],
                'answer'     => ['id' => 'Baharsyah Jelajah adalah biro perjalanan wisata berbasis di Kalimantan yang menyediakan layanan tour wisata, paket umrah, dan sewa kendaraan. Kami melayani wisatawan domestik maupun mancanegara dengan panduan profesional dan berpengalaman.', 'en' => 'Baharsyah Jelajah is a travel agency based in Kalimantan that provides tour, umrah package, and vehicle rental services. We serve both domestic and international travelers with professional and experienced guides.', 'ms' => 'Baharsyah Jelajah ialah agensi pelancongan yang berpusat di Kalimantan yang menyediakan perkhidmatan lawatan wisata, pakej umrah, dan penyewaan kenderaan. Kami melayani pelancong domestik dan antarabangsa dengan pemandu profesional dan berpengalaman.'],
                'is_active'  => true,
            ],
            [
                'category'   => 'general',
                'sort_order' => 2,
                'question'   => ['id' => 'Bagaimana cara memesan paket wisata?', 'en' => 'How do I book a tour package?', 'ms' => 'Bagaimana cara menempah pakej pelancongan?'],
                'answer'     => ['id' => 'Anda dapat memesan melalui website kami dengan mengklik tombol "Pesan Sekarang" atau menghubungi kami langsung via WhatsApp. Tim kami akan merespons dalam waktu 1x24 jam.', 'en' => 'You can book through our website by clicking the "Book Now" button or contact us directly via WhatsApp. Our team will respond within 24 hours.', 'ms' => 'Anda boleh menempah melalui laman web kami dengan mengklik butang "Tempah Sekarang" atau menghubungi kami terus melalui WhatsApp. Pasukan kami akan membalas dalam masa 24 jam.'],
                'is_active'  => true,
            ],
            [
                'category'   => 'general',
                'sort_order' => 3,
                'question'   => ['id' => 'Apakah Baharsyah Jelajah sudah terdaftar resmi?', 'en' => 'Is Baharsyah Jelajah officially registered?', 'ms' => 'Adakah Baharsyah Jelajah berdaftar secara rasmi?'],
                'answer'     => ['id' => 'Ya, Baharsyah Jelajah terdaftar sebagai biro perjalanan wisata resmi dan berizin di Kalimantan Selatan. Kami juga terdaftar sebagai penyelenggara perjalanan ibadah umrah yang diakui oleh Kementerian Agama RI.', 'en' => 'Yes, Baharsyah Jelajah is officially registered and licensed as a travel agency in South Kalimantan. We are also registered as an umrah travel organizer recognized by the Indonesian Ministry of Religious Affairs.', 'ms' => 'Ya, Baharsyah Jelajah berdaftar secara rasmi dan berlesen sebagai agensi pelancongan di Kalimantan Selatan. Kami juga berdaftar sebagai penganjur perjalanan umrah yang diiktiraf oleh Kementerian Agama RI.'],
                'is_active'  => true,
            ],

            // ── Tour ─────────────────────────────────────────────────────
            [
                'category'   => 'tour',
                'sort_order' => 1,
                'question'   => ['id' => 'Apa saja yang termasuk dalam paket tour?', 'en' => 'What is included in the tour package?', 'ms' => 'Apakah yang termasuk dalam pakej lawatan?'],
                'answer'     => ['id' => 'Setiap paket tour mencakup transportasi (antar-jemput bandara & selama tur), akomodasi, makan sesuai jadwal, tiket masuk objek wisata, dan pemandu wisata profesional. Detail lengkap dapat dilihat di halaman masing-masing paket.', 'en' => 'Each tour package includes transportation (airport transfers & during tour), accommodation, scheduled meals, attraction entrance tickets, and a professional tour guide. Full details are available on each package page.', 'ms' => 'Setiap pakej lawatan merangkumi pengangkutan (jemput-hantar lapangan terbang & sepanjang lawatan), penginapan, makan mengikut jadual, tiket masuk tempat menarik, dan pemandu pelancongan profesional. Butiran lengkap boleh dilihat di halaman pakej masing-masing.'],
                'is_active'  => true,
            ],
            [
                'category'   => 'tour',
                'sort_order' => 2,
                'question'   => ['id' => 'Berapa minimal peserta untuk private tour?', 'en' => 'What is the minimum number of participants for a private tour?', 'ms' => 'Berapakah bilangan minimum peserta untuk lawatan persendirian?'],
                'answer'     => ['id' => 'Untuk private tour, tidak ada minimal peserta – bahkan 1 orang pun bisa. Namun harga per orang akan lebih efisien jika peserta 4 orang ke atas. Hubungi kami untuk penawaran khusus group.', 'en' => 'For private tours, there is no minimum number of participants — even 1 person is welcome. However, the price per person is more efficient with 4 or more participants. Contact us for special group rates.', 'ms' => 'Untuk lawatan persendirian, tiada bilangan minimum peserta — walaupun 1 orang pun boleh. Walau bagaimanapun, harga per orang lebih menjimatkan jika terdapat 4 orang ke atas. Hubungi kami untuk tawaran kumpulan khas.'],
                'is_active'  => true,
            ],
            [
                'category'   => 'tour',
                'sort_order' => 3,
                'question'   => ['id' => 'Apakah ada paket wisata untuk keluarga dengan anak kecil?', 'en' => 'Are there family tour packages with young children?', 'ms' => 'Adakah pakej lawatan keluarga untuk kanak-kanak kecil?'],
                'answer'     => ['id' => 'Tentu! Kami memiliki paket family-friendly yang cocok untuk membawa anak-anak. Itinerary dirancang agar tidak terlalu melelahkan, dengan waktu istirahat yang cukup. Beritahu kami usia anak Anda agar kami bisa menyesuaikan program.', 'en' => 'Absolutely! We have family-friendly packages suitable for bringing children. Itineraries are designed to not be too tiring, with adequate rest time. Let us know your children\'s ages so we can customize the program.', 'ms' => 'Tentu! Kami mempunyai pakej mesra keluarga yang sesuai untuk membawa kanak-kanak. Itinerary direka supaya tidak terlalu meletihkan, dengan masa rehat yang mencukupi. Beritahu kami usia anak anda agar kami boleh menyesuaikan program.'],
                'is_active'  => true,
            ],

            // ── Umrah ─────────────────────────────────────────────────────
            [
                'category'   => 'umrah',
                'sort_order' => 1,
                'question'   => ['id' => 'Apakah visa sudah termasuk dalam harga paket umrah?', 'en' => 'Is the visa included in the umrah package price?', 'ms' => 'Adakah visa sudah termasuk dalam harga pakej umrah?'],
                'answer'     => ['id' => 'Ya, semua paket umrah kami sudah termasuk pengurusan visa umrah. Kami akan membantu proses aplikasi visa dari awal hingga selesai.', 'en' => 'Yes, all our umrah packages include umrah visa processing. We will assist with the entire visa application process from start to finish.', 'ms' => 'Ya, semua pakej umrah kami sudah termasuk pengurusan visa umrah. Kami akan membantu proses permohonan visa dari awal hingga selesai.'],
                'is_active'  => true,
            ],
            [
                'category'   => 'umrah',
                'sort_order' => 2,
                'question'   => ['id' => 'Berapa lama proses pendaftaran umrah?', 'en' => 'How long does the umrah registration process take?', 'ms' => 'Berapa lama proses pendaftaran umrah?'],
                'answer'     => ['id' => 'Pendaftaran dapat dilakukan kapan saja. Namun untuk kelancaran proses visa dan dokumen, disarankan mendaftar minimal 2-3 bulan sebelum keberangkatan. Pendaftaran mendadak (kurang dari 1 bulan) mungkin dikenakan biaya tambahan.', 'en' => 'Registration can be done at any time. However, for smooth visa and document processing, it is recommended to register at least 2-3 months before departure. Last-minute registration (less than 1 month) may incur additional charges.', 'ms' => 'Pendaftaran boleh dilakukan bila-bila masa. Walau bagaimanapun, untuk kelancaran proses visa dan dokumen, adalah disyorkan mendaftar sekurang-kurangnya 2-3 bulan sebelum berlepas. Pendaftaran lewat (kurang dari 1 bulan) mungkin dikenakan caj tambahan.'],
                'is_active'  => true,
            ],
            [
                'category'   => 'umrah',
                'sort_order' => 3,
                'question'   => ['id' => 'Apa perbedaan paket Regular, Plus, dan VIP?', 'en' => 'What is the difference between Regular, Plus, and VIP packages?', 'ms' => 'Apakah perbezaan antara pakej Regular, Plus, dan VIP?'],
                'answer'     => ['id' => 'Paket Regular cocok bagi yang ingin hemat dengan fasilitas standar (hotel bintang 3, kamar quad). Paket Plus menawarkan durasi lebih panjang dengan hotel bintang 4 dan program ziarah lengkap. Paket VIP memberikan layanan premium: hotel bintang 5 dekat Masjidil Haram, kamar double, dan pembimbing pribadi.', 'en' => 'The Regular package is ideal for budget travelers with standard facilities (3-star hotel, quad room). The Plus package offers a longer duration with a 4-star hotel and a full ziarah program. The VIP package provides premium service: 5-star hotel near Masjidil Haram, double room, and a personal guide.', 'ms' => 'Pakej Regular sesuai bagi yang ingin menjimatkan dengan kemudahan standard (hotel bintang 3, bilik kuad). Pakej Plus menawarkan tempoh lebih lama dengan hotel bintang 4 dan program ziarah lengkap. Pakej VIP memberikan perkhidmatan premium: hotel bintang 5 berdekatan Masjidil Haram, bilik double, dan pembimbing peribadi.'],
                'is_active'  => true,
            ],
            [
                'category'   => 'umrah',
                'sort_order' => 4,
                'question'   => ['id' => 'Apakah ada bimbingan manasik umrah sebelum berangkat?', 'en' => 'Is there a pre-departure umrah manasik guidance session?', 'ms' => 'Adakah sesi bimbingan manasik umrah sebelum berlepas?'],
                'answer'     => ['id' => 'Ya, kami mengadakan bimbingan manasik umrah sebanyak 3-5 kali pertemuan sebelum keberangkatan. Bimbingan ini gratis dan wajib diikuti oleh semua jamaah untuk memastikan kelancaran ibadah.', 'en' => 'Yes, we hold 3-5 umrah manasik guidance sessions before departure. These sessions are free and mandatory for all pilgrims to ensure smooth worship.', 'ms' => 'Ya, kami mengadakan sesi bimbingan manasik umrah sebanyak 3-5 kali sebelum berlepas. Bimbingan ini percuma dan wajib dihadiri oleh semua jemaah untuk memastikan kelancaran ibadat.'],
                'is_active'  => true,
            ],

            // ── Kendaraan ─────────────────────────────────────────────────
            [
                'category'   => 'vehicle',
                'sort_order' => 1,
                'question'   => ['id' => 'Apakah sewa kendaraan sudah termasuk sopir?', 'en' => 'Does the vehicle rental include a driver?', 'ms' => 'Adakah penyewaan kenderaan termasuk pemandu?'],
                'answer'     => ['id' => 'Ya, semua sewa kendaraan di Baharsyah Jelajah sudah termasuk sopir profesional yang berpengalaman dan mengenal baik wilayah Kalimantan. Kami tidak menyediakan sewa lepas kunci (self-drive).', 'en' => 'Yes, all vehicle rentals at Baharsyah Jelajah include a professional driver who is experienced and well-acquainted with the Kalimantan region. We do not offer self-drive rentals.', 'ms' => 'Ya, semua penyewaan kenderaan di Baharsyah Jelajah sudah termasuk pemandu profesional yang berpengalaman dan mengenali kawasan Kalimantan dengan baik. Kami tidak menyediakan penyewaan tanpa pemandu (self-drive).'],
                'is_active'  => true,
            ],
            [
                'category'   => 'vehicle',
                'sort_order' => 2,
                'question'   => ['id' => 'Berapa lama minimal sewa kendaraan?', 'en' => 'What is the minimum vehicle rental duration?', 'ms' => 'Berapakah tempoh minimum penyewaan kenderaan?'],
                'answer'     => ['id' => 'Untuk sewa harian, minimal adalah 8 jam/hari. Untuk perjalanan antar kota atau tour multi-hari, tidak ada batasan minimum – kami akan menyesuaikan harga sesuai kebutuhan perjalanan Anda.', 'en' => 'For daily rental, the minimum is 8 hours/day. For intercity travel or multi-day tours, there is no minimum – we will tailor the price to your travel needs.', 'ms' => 'Untuk penyewaan harian, minimumnya ialah 8 jam/hari. Untuk perjalanan antara bandar atau lawatan berbilang hari, tiada minimum – kami akan menyesuaikan harga mengikut keperluan perjalanan anda.'],
                'is_active'  => true,
            ],
            [
                'category'   => 'vehicle',
                'sort_order' => 3,
                'question'   => ['id' => 'Bagaimana jika kendaraan mengalami kerusakan saat perjalanan?', 'en' => 'What happens if the vehicle breaks down during the trip?', 'ms' => 'Apa yang berlaku jika kenderaan rosak semasa perjalanan?'],
                'answer'     => ['id' => 'Semua armada kami dilengkapi asuransi perjalanan. Jika terjadi kerusakan akibat faktor di luar kendali penyewa, kami akan mengganti kendaraan secepatnya tanpa biaya tambahan. Kerusakan akibat kelalaian penyewa akan dikenakan biaya sesuai kesepakatan.', 'en' => 'All our fleet is covered by travel insurance. If a breakdown occurs due to factors beyond the renter\'s control, we will replace the vehicle as quickly as possible at no extra cost. Damage due to renter negligence will be charged according to agreement.', 'ms' => 'Semua armada kami dilindungi insurans perjalanan. Jika kerosakan berlaku akibat faktor di luar kawalan penyewa, kami akan menggantikan kenderaan secepat mungkin tanpa kos tambahan. Kerosakan akibat kecuaian penyewa akan dikenakan caj mengikut perjanjian.'],
                'is_active'  => true,
            ],

            // ── Pembayaran ────────────────────────────────────────────────
            [
                'category'   => 'payment',
                'sort_order' => 1,
                'question'   => ['id' => 'Metode pembayaran apa saja yang diterima?', 'en' => 'What payment methods are accepted?', 'ms' => 'Apakah kaedah pembayaran yang diterima?'],
                'answer'     => ['id' => 'Kami menerima transfer bank (BCA, Mandiri, BNI, BRI), QRIS, dan kartu kredit/debit. Untuk pembayaran tunai, silakan hubungi kami terlebih dahulu untuk membuat janji.', 'en' => 'We accept bank transfers (BCA, Mandiri, BNI, BRI), QRIS, and credit/debit cards. For cash payments, please contact us first to make an appointment.', 'ms' => 'Kami menerima pemindahan bank (BCA, Mandiri, BNI, BRI), QRIS, dan kad kredit/debit. Untuk pembayaran tunai, sila hubungi kami terlebih dahulu untuk membuat temujanji.'],
                'is_active'  => true,
            ],
            [
                'category'   => 'payment',
                'sort_order' => 2,
                'question'   => ['id' => 'Apakah ada cicilan atau DP untuk paket umrah?', 'en' => 'Is there an installment or down payment option for umrah packages?', 'ms' => 'Adakah pilihan ansuran atau bayaran pendahuluan untuk pakej umrah?'],
                'answer'     => ['id' => 'Ya, kami menerima DP (Down Payment) minimal 30% dari total biaya paket. Pelunasan dapat dilakukan paling lambat 30 hari sebelum keberangkatan. Cicilan tersedia untuk anggota tertentu, hubungi kami untuk info lebih lanjut.', 'en' => 'Yes, we accept a minimum down payment of 30% of the total package cost. The remaining balance must be paid at least 30 days before departure. Installments are available for certain members — contact us for more information.', 'ms' => 'Ya, kami menerima bayaran pendahuluan minimum 30% daripada jumlah kos pakej. Baki pembayaran mesti diselesaikan sekurang-kurangnya 30 hari sebelum berlepas. Ansuran tersedia untuk ahli tertentu — hubungi kami untuk maklumat lanjut.'],
                'is_active'  => true,
            ],
            [
                'category'   => 'payment',
                'sort_order' => 3,
                'question'   => ['id' => 'Apakah ada kebijakan refund jika pembatalan terjadi?', 'en' => 'What is the refund policy for cancellations?', 'ms' => 'Apakah polisi bayaran balik untuk pembatalan?'],
                'answer'     => ['id' => 'Kebijakan refund kami: Pembatalan >60 hari sebelum keberangkatan: refund 80%. Pembatalan 30-60 hari: refund 50%. Pembatalan <30 hari: tidak ada refund. Untuk force majeure (bencana alam, dsb), kami akan meninjau kasus per kasus.', 'en' => 'Our refund policy: Cancellation >60 days before departure: 80% refund. Cancellation 30-60 days: 50% refund. Cancellation <30 days: no refund. For force majeure events (natural disasters, etc.), we will review on a case-by-case basis.', 'ms' => 'Polisi bayaran balik kami: Pembatalan >60 hari sebelum berlepas: bayaran balik 80%. Pembatalan 30-60 hari: bayaran balik 50%. Pembatalan <30 hari: tiada bayaran balik. Untuk kejadian force majeure (bencana alam, dll), kami akan meninjau setiap kes secara individu.'],
                'is_active'  => true,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::create($faq);
        }
    }
}
