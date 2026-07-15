<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'title' => [
                    'id' => 'Jelajahi Surga Tersembunyi Kalimantan',
                    'en' => 'Discover the Hidden Paradise of Borneo',
                    'ms' => 'Terokai Syurga Tersembunyi Borneo',
                ],
                'subtitle' => [
                    'id' => 'Tour wisata orang utan, hutan tropis, dan budaya Dayak bersama pemandu lokal berpengalaman.',
                    'en' => 'Orangutan tours, tropical forests, and Dayak culture with experienced local guides.',
                    'ms' => 'Lawatan orang utan, hutan tropika, dan budaya Dayak bersama pemandu tempatan yang berpengalaman.',
                ],
                'cta_label' => [
                    'id' => 'Lihat Paket Wisata',
                    'en' => 'View Tour Packages',
                    'ms' => 'Lihat Pakej Pelancongan',
                ],
                'image_path' => 'https://images.unsplash.com/photo-1516690561799-46d8f74f9abf?w=1600&q=80&fit=crop',
                'placement' => 'home_hero',
                'cta_type' => 'route',
                'cta_value' => 'tour.index',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => [
                    'id' => 'Umrah dengan Nyaman & Berkesan',
                    'en' => 'A Comfortable & Meaningful Umrah Journey',
                    'ms' => 'Umrah yang Selesa & Bermakna',
                ],
                'subtitle' => [
                    'id' => 'Paket umrah lengkap dengan bimbingan profesional, hotel bintang 3-5, dan berangkat dari Kalimantan.',
                    'en' => 'Complete umrah packages with professional guidance, 3-5 star hotels, departing from Kalimantan.',
                    'ms' => 'Pakej umrah lengkap dengan bimbingan profesional, hotel bintang 3-5, berlepas dari Kalimantan.',
                ],
                'cta_label' => [
                    'id' => 'Lihat Paket Umrah',
                    'en' => 'View Umrah Packages',
                    'ms' => 'Lihat Pakej Umrah',
                ],
                'image_path' => 'https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?w=1600&q=80&fit=crop',
                'placement' => 'home_promo',
                'cta_type' => 'route',
                'cta_value' => 'umroh.index',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => [
                    'id' => 'Sewa Kendaraan Nyaman untuk Perjalanan Anda',
                    'en' => 'Comfortable Vehicle Rental for Your Journey',
                    'ms' => 'Sewa Kenderaan Selesa untuk Perjalanan Anda',
                ],
                'subtitle' => [
                    'id' => 'Armada lengkap dari Avanza hingga bus pariwisata, lengkap dengan sopir profesional.',
                    'en' => 'Complete fleet from Avanza to tour buses, all with professional drivers.',
                    'ms' => 'Armada lengkap dari Avanza hingga bas pelancongan, dengan pemandu profesional.',
                ],
                'cta_label' => [
                    'id' => 'Sewa Sekarang',
                    'en' => 'Rent Now',
                    'ms' => 'Sewa Sekarang',
                ],
                'image_path' => 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=1600&q=80&fit=crop',
                'placement' => 'home_promo',
                'cta_type' => 'route',
                'cta_value' => 'transport.index',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::updateOrCreate(
                ['sort_order' => $banner['sort_order']],
                $banner,
            );
        }
    }
}
