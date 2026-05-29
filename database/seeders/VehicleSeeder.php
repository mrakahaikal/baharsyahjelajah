<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use App\Models\VehicleGallery;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = [
            [
                'name'             => ['id' => 'Toyota Alphard', 'en' => 'Toyota Alphard', 'ms' => 'Toyota Alphard'],
                'brand'            => 'Toyota',
                'model'            => 'Alphard',
                'year'             => 2023,
                'capacity_pax'     => 7,
                'capacity_luggage' => 4,
                'transmission'     => 'automatic',
                'has_ac'           => true,
                'has_wifi'         => true,
                'is_available'     => true,
                'price_per_day_idr'  => 1500000,
                'price_per_trip_idr' => 2500000,
                'features'         => ['Kursi Kulit', 'Kamera Belakang', 'Charger USB', 'Audio Premium'],
                'thumbnail'        => 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=800&q=80&fit=crop',
                'galleries'        => [
                    'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=1200&q=80&fit=crop',
                    'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=1200&q=80&fit=crop',
                ],
            ],
            [
                'name'             => ['id' => 'Toyota HiAce Commuter', 'en' => 'Toyota HiAce Commuter', 'ms' => 'Toyota HiAce Commuter'],
                'brand'            => 'Toyota',
                'model'            => 'HiAce Commuter',
                'year'             => 2022,
                'capacity_pax'     => 14,
                'capacity_luggage' => 6,
                'transmission'     => 'automatic',
                'has_ac'           => true,
                'has_wifi'         => false,
                'is_available'     => true,
                'price_per_day_idr'  => 900000,
                'price_per_trip_idr' => 1500000,
                'features'         => ['Karpet Tebal', 'Tirai Jendela', 'Speaker', 'Koper 20" Masuk'],
                'thumbnail'        => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=800&q=80&fit=crop',
                'galleries'        => [
                    'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=1200&q=80&fit=crop',
                    'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1200&q=80&fit=crop',
                ],
            ],
            [
                'name'             => ['id' => 'Toyota Innova Reborn', 'en' => 'Toyota Innova Reborn', 'ms' => 'Toyota Innova Reborn'],
                'brand'            => 'Toyota',
                'model'            => 'Innova Reborn',
                'year'             => 2023,
                'capacity_pax'     => 7,
                'capacity_luggage' => 3,
                'transmission'     => 'automatic',
                'has_ac'           => true,
                'has_wifi'         => false,
                'is_available'     => true,
                'price_per_day_idr'  => 700000,
                'price_per_trip_idr' => 1200000,
                'features'         => ['Charger USB', 'Audio Stereo', 'Ban Serep'],
                'thumbnail'        => 'https://images.unsplash.com/photo-1619767886558-efdc259cde1a?w=800&q=80&fit=crop',
                'galleries'        => [
                    'https://images.unsplash.com/photo-1619767886558-efdc259cde1a?w=1200&q=80&fit=crop',
                ],
            ],
            [
                'name'             => ['id' => 'Toyota Avanza', 'en' => 'Toyota Avanza', 'ms' => 'Toyota Avanza'],
                'brand'            => 'Toyota',
                'model'            => 'Avanza',
                'year'             => 2022,
                'capacity_pax'     => 7,
                'capacity_luggage' => 2,
                'transmission'     => 'manual',
                'has_ac'           => true,
                'has_wifi'         => false,
                'is_available'     => true,
                'price_per_day_idr'  => 450000,
                'price_per_trip_idr' => 750000,
                'features'         => ['Audio Stereo', 'Ban Serep'],
                'thumbnail'        => 'https://images.unsplash.com/photo-1489824904134-891ab64532f1?w=800&q=80&fit=crop',
                'galleries'        => [
                    'https://images.unsplash.com/photo-1489824904134-891ab64532f1?w=1200&q=80&fit=crop',
                ],
            ],
            [
                'name'             => ['id' => 'Bus Pariwisata 40 Seat', 'en' => '40-Seat Tour Bus', 'ms' => 'Bas Pelancongan 40 Tempat Duduk'],
                'brand'            => 'Hino',
                'model'            => 'RN285',
                'year'             => 2021,
                'capacity_pax'     => 40,
                'capacity_luggage' => 20,
                'transmission'     => 'automatic',
                'has_ac'           => true,
                'has_wifi'         => true,
                'is_available'     => true,
                'price_per_day_idr'  => null,
                'price_per_trip_idr' => 6000000,
                'features'         => ['Reclining Seat', 'Toilet', 'Kompartemen Bagasi', 'TV/Monitor', 'Mikrofon'],
                'thumbnail'        => 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=800&q=80&fit=crop',
                'galleries'        => [
                    'https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=1200&q=80&fit=crop',
                ],
            ],
        ];

        foreach ($vehicles as $data) {
            $galleries = $data['galleries'];
            unset($data['galleries']);

            $vehicle = Vehicle::create($data);

            foreach ($galleries as $i => $imageUrl) {
                VehicleGallery::create([
                    'vehicle_id' => $vehicle->id,
                    'image_path' => $imageUrl,
                    'sort_order' => $i + 1,
                ]);
            }
        }
    }
}