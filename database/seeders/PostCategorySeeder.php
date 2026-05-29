<?php

namespace Database\Seeders;

use App\Models\PostCategory;
use Illuminate\Database\Seeder;

class PostCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => ['id' => 'Panduan Wisata', 'en' => 'Travel Guide', 'ms' => 'Panduan Pelancongan'],
                'slug' => ['id' => 'panduan-wisata', 'en' => 'travel-guide', 'ms' => 'panduan-pelancongan'],
                'description' => [
                    'id' => 'Tips dan panduan lengkap untuk wisatawan yang ingin menjelajahi Kalimantan.',
                    'en' => 'Complete tips and guides for travelers who want to explore Borneo.',
                    'ms' => 'Tip dan panduan lengkap untuk pelancong yang ingin meneroka Borneo.',
                ],
            ],
            [
                'name' => ['id' => 'Destinasi', 'en' => 'Destinations', 'ms' => 'Destinasi'],
                'slug' => ['id' => 'destinasi', 'en' => 'destinations', 'ms' => 'destinasi'],
                'description' => [
                    'id' => 'Eksplorasi destinasi-destinasi menakjubkan di Kalimantan dan sekitarnya.',
                    'en' => 'Explore the amazing destinations in Borneo and the surrounding areas.',
                    'ms' => 'Terokai destinasi-destinasi menakjubkan di Borneo dan kawasan sekitarnya.',
                ],
            ],
            [
                'name' => ['id' => 'Berita & Promo', 'en' => 'News & Promotions', 'ms' => 'Berita & Promosi'],
                'slug' => ['id' => 'berita-promo', 'en' => 'news-promotions', 'ms' => 'berita-promosi'],
                'description' => [
                    'id' => 'Informasi terkini dan penawaran promosi menarik dari Baharsyah Jelajah.',
                    'en' => 'Latest news and attractive promotional offers from Baharsyah Jelajah.',
                    'ms' => 'Maklumat terkini dan tawaran promosi menarik dari Baharsyah Jelajah.',
                ],
            ],
            [
                'name' => ['id' => 'Alam & Satwa Liar', 'en' => 'Nature & Wildlife', 'ms' => 'Alam & Hidupan Liar'],
                'slug' => ['id' => 'alam-satwa-liar', 'en' => 'nature-wildlife', 'ms' => 'alam-hidupan-liar'],
                'description' => [
                    'id' => 'Artikel tentang kekayaan alam dan satwa liar yang unik di Kalimantan.',
                    'en' => 'Articles about the natural richness and unique wildlife of Borneo.',
                    'ms' => 'Artikel tentang kekayaan alam dan hidupan liar yang unik di Borneo.',
                ],
            ],
        ];

        foreach ($categories as $category) {
            PostCategory::create($category);
        }
    }
}
