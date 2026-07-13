<?php

namespace Database\Seeders;

use App\Enums\TourType;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\TourPackage;
use Illuminate\Database\Seeder;

class TourSeeder extends Seeder
{
    public function run(): void
    {
        $natureCategory = TourCategory::query()
            ->where('icon', 'heroicon-o-sparkles')
            ->firstOrFail();

        $adventureCategory = TourCategory::query()
            ->where('icon', 'heroicon-o-map')
            ->firstOrFail();

        $this->createTour([
            'tour' => [
                'tour_category_id' => $natureCategory->id,
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
                'short_description' => [
                    'id' => 'Susuri hutan Kalimantan dengan klotok dan temui orangutan di habitat aslinya.',
                    'en' => 'Cruise through the Bornean rainforest and encounter orangutans in their natural habitat.',
                    'ms' => 'Susuri hutan Kalimantan dengan klotok dan temui orang utan di habitat asalnya.',
                ],
                'description' => [
                    'id' => 'Perjalanan menyusuri Sungai Sekonyer menuju pusat konservasi orangutan, lengkap dengan pengalaman bermalam di atas klotok.',
                    'en' => 'A journey along the Sekonyer River to orangutan conservation sites, including an overnight stay aboard a klotok.',
                    'ms' => 'Perjalanan menyusuri Sungai Sekonyer ke pusat pemuliharaan orang utan, termasuk bermalam di atas klotok.',
                ],
                'tour_type' => TourType::Domestic,
                'currency' => 'IDR',
                'is_active' => true,
                'is_featured' => true,
            ],
            'package' => [
                'name' => [
                    'id' => 'Paket Klotok 4 Hari',
                    'en' => '4-Day Klotok Package',
                    'ms' => 'Pakej Klotok 4 Hari',
                ],
                'slug' => [
                    'id' => 'paket-klotok-4-hari',
                    'en' => '4-day-klotok-package',
                    'ms' => 'pakej-klotok-4-hari',
                ],
                'duration_days' => 4,
                'duration_nights' => 3,
            ],
            'tier' => [
                'name' => ['id' => 'Standar', 'en' => 'Standard', 'ms' => 'Standard'],
                'hotel_stars' => null,
            ],
            'prices' => [
                ['min_pax' => 1, 'max_pax' => 2, 'price' => 4250000, 'currency' => 'IDR'],
                ['min_pax' => 3, 'max_pax' => null, 'price' => 3500000, 'currency' => 'IDR'],
            ],
            'itineraries' => [
                [
                    'day_number' => 1,
                    'title' => ['id' => 'Pangkalan Bun dan Sungai Sekonyer', 'en' => 'Pangkalan Bun and Sekonyer River', 'ms' => 'Pangkalan Bun dan Sungai Sekonyer'],
                    'description' => ['id' => 'Penjemputan bandara, transfer ke pelabuhan, lalu mulai perjalanan dengan klotok.', 'en' => 'Airport pickup, port transfer, and the start of the klotok journey.', 'ms' => 'Jemputan lapangan terbang, pindah ke pelabuhan, kemudian memulakan perjalanan klotok.'],
                ],
                [
                    'day_number' => 2,
                    'title' => ['id' => 'Pondok Tanggui dan Camp Leakey', 'en' => 'Pondok Tanggui and Camp Leakey', 'ms' => 'Pondok Tanggui dan Camp Leakey'],
                    'description' => ['id' => 'Mengunjungi lokasi pemberian makan dan pusat penelitian orangutan.', 'en' => 'Visit orangutan feeding sites and the renowned research centre.', 'ms' => 'Melawat lokasi pemberian makanan dan pusat penyelidikan orang utan.'],
                ],
                [
                    'day_number' => 3,
                    'title' => ['id' => 'Tanjung Harapan', 'en' => 'Tanjung Harapan', 'ms' => 'Tanjung Harapan'],
                    'description' => ['id' => 'Trekking hutan dan pengamatan satwa liar di sekitar Sungai Sekonyer.', 'en' => 'Forest trekking and wildlife observation around the Sekonyer River.', 'ms' => 'Trekking hutan dan pemerhatian hidupan liar di sekitar Sungai Sekonyer.'],
                ],
                [
                    'day_number' => 4,
                    'title' => ['id' => 'Kepulangan', 'en' => 'Departure', 'ms' => 'Kepulangan'],
                    'description' => ['id' => 'Kembali ke pelabuhan dan transfer menuju bandara.', 'en' => 'Return to the port and transfer to the airport.', 'ms' => 'Kembali ke pelabuhan dan pindah ke lapangan terbang.'],
                ],
            ],
            'includes' => [
                ['item' => ['id' => 'Klotok dan awak kapal', 'en' => 'Klotok and boat crew', 'ms' => 'Klotok dan kru bot'], 'type' => 'include', 'sort_order' => 1],
                ['item' => ['id' => 'Makan selama perjalanan', 'en' => 'Meals during the trip', 'ms' => 'Makanan sepanjang perjalanan'], 'type' => 'include', 'sort_order' => 2],
                ['item' => ['id' => 'Tiket pesawat', 'en' => 'Flights', 'ms' => 'Tiket penerbangan'], 'type' => 'exclude', 'sort_order' => 3],
            ],
            'cover' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=1200&q=80&fit=crop',
            'gallery' => [
                ['url' => 'https://images.unsplash.com/photo-1497294815431-9365093b7331?w=1200&q=80&fit=crop', 'caption' => ['id' => 'Perjalanan dengan klotok', 'en' => 'Klotok river journey', 'ms' => 'Perjalanan dengan klotok']],
                ['url' => 'https://images.unsplash.com/photo-1615982513414-d287e6b70ad6?w=1200&q=80&fit=crop', 'caption' => ['id' => 'Trekking di hutan Kalimantan', 'en' => 'Trekking in the Bornean rainforest', 'ms' => 'Trekking di hutan Kalimantan']],
            ],
        ]);

        $this->createTour([
            'tour' => [
                'tour_category_id' => $adventureCategory->id,
                'name' => [
                    'id' => 'Bangkok dan Pattaya Muslim Friendly',
                    'en' => 'Muslim-Friendly Bangkok and Pattaya',
                    'ms' => 'Bangkok dan Pattaya Mesra Muslim',
                ],
                'slug' => [
                    'id' => 'bangkok-pattaya-muslim-friendly',
                    'en' => 'muslim-friendly-bangkok-pattaya',
                    'ms' => 'bangkok-pattaya-mesra-muslim',
                ],
                'short_description' => [
                    'id' => 'Jelajahi Bangkok dan Pattaya dengan pilihan makanan halal serta jadwal ibadah yang nyaman.',
                    'en' => 'Explore Bangkok and Pattaya with halal dining and prayer-friendly arrangements.',
                    'ms' => 'Terokai Bangkok dan Pattaya dengan hidangan halal serta jadual ibadah yang selesa.',
                ],
                'description' => [
                    'id' => 'Paket internasional yang memadukan wisata kota Bangkok, budaya Thailand, dan pesisir Pattaya dengan layanan ramah Muslim.',
                    'en' => 'An international package combining Bangkok city sights, Thai culture, and Pattaya coastline with Muslim-friendly services.',
                    'ms' => 'Pakej antarabangsa yang menggabungkan bandar Bangkok, budaya Thai, dan pesisir Pattaya dengan perkhidmatan mesra Muslim.',
                ],
                'tour_type' => TourType::International,
                'currency' => 'USD',
                'is_active' => true,
                'is_featured' => true,
            ],
            'package' => [
                'name' => [
                    'id' => 'Paket Thailand 5 Hari',
                    'en' => '5-Day Thailand Package',
                    'ms' => 'Pakej Thailand 5 Hari',
                ],
                'slug' => [
                    'id' => 'paket-thailand-5-hari',
                    'en' => '5-day-thailand-package',
                    'ms' => 'pakej-thailand-5-hari',
                ],
                'duration_days' => 5,
                'duration_nights' => 4,
            ],
            'tier' => [
                'name' => ['id' => 'Superior', 'en' => 'Superior', 'ms' => 'Superior'],
                'hotel_stars' => 4,
            ],
            'prices' => [
                ['min_pax' => 1, 'max_pax' => 2, 'price' => 975, 'currency' => 'USD'],
                ['min_pax' => 3, 'max_pax' => null, 'price' => 825, 'currency' => 'USD'],
            ],
            'itineraries' => [
                [
                    'day_number' => 1,
                    'title' => ['id' => 'Tiba di Bangkok', 'en' => 'Arrival in Bangkok', 'ms' => 'Tiba di Bangkok'],
                    'description' => ['id' => 'Penjemputan bandara, makan malam halal, dan check-in hotel.', 'en' => 'Airport pickup, halal dinner, and hotel check-in.', 'ms' => 'Jemputan lapangan terbang, makan malam halal, dan daftar masuk hotel.'],
                ],
                [
                    'day_number' => 2,
                    'title' => ['id' => 'Wisata Kota Bangkok', 'en' => 'Bangkok City Tour', 'ms' => 'Lawatan Bandar Bangkok'],
                    'description' => ['id' => 'Mengunjungi ikon kota, pusat budaya, dan masjid bersejarah.', 'en' => 'Visit city landmarks, cultural sites, and a historic mosque.', 'ms' => 'Melawat mercu tanda bandar, pusat budaya, dan masjid bersejarah.'],
                ],
                [
                    'day_number' => 3,
                    'title' => ['id' => 'Bangkok ke Pattaya', 'en' => 'Bangkok to Pattaya', 'ms' => 'Bangkok ke Pattaya'],
                    'description' => ['id' => 'Perjalanan menuju Pattaya dan menikmati kawasan pesisir.', 'en' => 'Travel to Pattaya and explore its coastal attractions.', 'ms' => 'Perjalanan ke Pattaya dan menikmati kawasan pesisir.'],
                ],
                [
                    'day_number' => 4,
                    'title' => ['id' => 'Eksplorasi Pattaya', 'en' => 'Pattaya Exploration', 'ms' => 'Penerokaan Pattaya'],
                    'description' => ['id' => 'Wisata pulau, aktivitas pantai, dan makan malam halal.', 'en' => 'Island excursion, beach activities, and halal dinner.', 'ms' => 'Lawatan pulau, aktiviti pantai, dan makan malam halal.'],
                ],
                [
                    'day_number' => 5,
                    'title' => ['id' => 'Kepulangan', 'en' => 'Departure', 'ms' => 'Kepulangan'],
                    'description' => ['id' => 'Kembali ke Bangkok untuk penerbangan pulang.', 'en' => 'Return to Bangkok for the flight home.', 'ms' => 'Kembali ke Bangkok untuk penerbangan pulang.'],
                ],
            ],
            'includes' => [
                ['item' => ['id' => 'Hotel bintang empat', 'en' => 'Four-star hotel', 'ms' => 'Hotel empat bintang'], 'type' => 'include', 'sort_order' => 1],
                ['item' => ['id' => 'Transportasi dan pemandu', 'en' => 'Transportation and guide', 'ms' => 'Pengangkutan dan pemandu'], 'type' => 'include', 'sort_order' => 2],
                ['item' => ['id' => 'Tiket pesawat internasional', 'en' => 'International flights', 'ms' => 'Tiket penerbangan antarabangsa'], 'type' => 'exclude', 'sort_order' => 3],
            ],
            'cover' => 'https://images.unsplash.com/photo-1508009603885-50cf7c579365?w=1200&q=80&fit=crop',
            'gallery' => [
                ['url' => 'https://images.unsplash.com/photo-1528181304800-259b08848526?w=1200&q=80&fit=crop', 'caption' => ['id' => 'Pemandangan Bangkok', 'en' => 'Bangkok cityscape', 'ms' => 'Pemandangan Bangkok']],
                ['url' => 'https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?w=1200&q=80&fit=crop', 'caption' => ['id' => 'Budaya dan arsitektur Thailand', 'en' => 'Thai culture and architecture', 'ms' => 'Budaya dan seni bina Thailand']],
            ],
        ]);
    }

    /**
     * @param  array{
     *     tour: array<string, mixed>,
     *     package: array<string, mixed>,
     *     tier: array<string, mixed>,
     *     prices: array<int, array<string, mixed>>,
     *     itineraries: array<int, array<string, mixed>>,
     *     includes: array<int, array<string, mixed>>,
     *     cover: string,
     *     gallery: array<int, array{url: string, caption: array<string, string>}>
     * }  $data
     */
    private function createTour(array $data): void
    {
        $tour = Tour::create($data['tour']);
        $package = $tour->packages()->create($data['package']);
        $tier = $package->tiers()->create($data['tier']);

        $tier->priceTiers()->createMany($data['prices']);
        $package->itineraries()->createMany($data['itineraries']);
        $package->includes()->createMany($data['includes']);

        $package->addMediaFromUrl($data['cover'])
            ->usingName($tour->getTranslation('name', 'id'))
            ->toMediaCollection(TourPackage::MEDIA_COLLECTION_COVER);

        foreach ($data['gallery'] as $gallery) {
            $package->addMediaFromUrl($gallery['url'])
                ->usingName($gallery['caption']['id'])
                ->withCustomProperties(['caption' => $gallery['caption']])
                ->toMediaCollection(TourPackage::MEDIA_COLLECTION_GALLERY);
        }
    }
}
