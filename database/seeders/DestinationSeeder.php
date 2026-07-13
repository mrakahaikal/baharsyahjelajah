<?php

namespace Database\Seeders;

use App\Models\Destination;
use Illuminate\Database\Seeder;

class DestinationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $destinations = [
            [
                'name' => [
                    'id' => 'Taman Nasional Tanjung Puting',
                    'en' => 'Tanjung Puting National Park',
                    'ms' => 'Taman Negara Tanjung Puting',
                ],
                'slug' => 'taman-nasional-tanjung-puting',
                'description' => [
                    'id' => 'Kawasan konservasi hutan tropis yang dikenal sebagai habitat orangutan Kalimantan.',
                    'en' => 'A tropical forest conservation area known as a habitat for Bornean orangutans.',
                    'ms' => 'Kawasan pemuliharaan hutan tropika yang dikenali sebagai habitat orang utan Borneo.',
                ],
                'location' => 'Kotawaringin Barat, Kalimantan Tengah',
                'map_url' => 'https://maps.google.com/?q=Tanjung+Puting+National+Park',
            ],
            [
                'name' => ['id' => 'Camp Leakey', 'en' => 'Camp Leakey', 'ms' => 'Camp Leakey'],
                'slug' => 'camp-leakey',
                'description' => [
                    'id' => 'Pusat penelitian orangutan dengan jalur trekking dan area pemberian makan.',
                    'en' => 'An orangutan research centre with trekking trails and a feeding area.',
                    'ms' => 'Pusat penyelidikan orang utan dengan laluan trekking dan kawasan pemberian makanan.',
                ],
                'location' => 'Tanjung Puting, Kalimantan Tengah',
                'map_url' => 'https://maps.google.com/?q=Camp+Leakey',
            ],
            [
                'name' => ['id' => 'Sungai Sekonyer', 'en' => 'Sekonyer River', 'ms' => 'Sungai Sekonyer'],
                'slug' => 'sungai-sekonyer',
                'description' => [
                    'id' => 'Jalur sungai utama untuk perjalanan klotok menuju kawasan konservasi Tanjung Puting.',
                    'en' => 'The main river route for klotok journeys into the Tanjung Puting conservation area.',
                    'ms' => 'Laluan sungai utama untuk perjalanan klotok ke kawasan pemuliharaan Tanjung Puting.',
                ],
                'location' => 'Kumai, Kalimantan Tengah',
                'map_url' => 'https://maps.google.com/?q=Sekonyer+River',
            ],
            [
                'name' => ['id' => 'Kota Bangkok', 'en' => 'Bangkok City', 'ms' => 'Bandar Bangkok'],
                'slug' => 'kota-bangkok',
                'description' => [
                    'id' => 'Kota metropolitan dengan perpaduan wisata budaya, kuliner, dan pusat perbelanjaan.',
                    'en' => 'A metropolitan city combining cultural attractions, cuisine, and shopping districts.',
                    'ms' => 'Bandar metropolitan yang menggabungkan tarikan budaya, kulinari, dan kawasan membeli-belah.',
                ],
                'location' => 'Bangkok, Thailand',
                'map_url' => 'https://maps.google.com/?q=Bangkok+Thailand',
            ],
            [
                'name' => ['id' => 'Pantai Pattaya', 'en' => 'Pattaya Beach', 'ms' => 'Pantai Pattaya'],
                'slug' => 'pantai-pattaya',
                'description' => [
                    'id' => 'Kawasan pesisir populer dengan aktivitas pantai dan akses menuju pulau-pulau sekitar.',
                    'en' => 'A popular coastal area with beach activities and access to nearby islands.',
                    'ms' => 'Kawasan pesisir popular dengan aktiviti pantai dan akses ke pulau-pulau berdekatan.',
                ],
                'location' => 'Chonburi, Thailand',
                'map_url' => 'https://maps.google.com/?q=Pattaya+Beach',
            ],
        ];

        foreach ($destinations as $destination) {
            Destination::updateOrCreate(
                ['slug' => $destination['slug']],
                $destination,
            );
        }
    }
}
