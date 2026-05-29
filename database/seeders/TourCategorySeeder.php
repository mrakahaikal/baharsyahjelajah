<?php

namespace Database\Seeders;

use App\Models\TourCategory;
use Illuminate\Database\Seeder;

class TourCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => ['id' => 'Wisata Alam', 'en' => 'Nature Tour', 'ms' => 'Pelancongan Alam'],
                'slug' => ['id' => 'wisata-alam', 'en' => 'nature-tour', 'ms' => 'pelancongan-alam'],
                'icon' => 'heroicon-o-sparkles',
                'sort_order' => 1,
            ],
            [
                'name' => ['id' => 'Wisata Bahari', 'en' => 'Marine Tour', 'ms' => 'Pelancongan Bahari'],
                'slug' => ['id' => 'wisata-bahari', 'en' => 'marine-tour', 'ms' => 'pelancongan-bahari'],
                'icon' => 'heroicon-o-sun',
                'sort_order' => 2,
            ],
            [
                'name' => ['id' => 'Wisata Budaya', 'en' => 'Cultural Tour', 'ms' => 'Pelancongan Budaya'],
                'slug' => ['id' => 'wisata-budaya', 'en' => 'cultural-tour', 'ms' => 'pelancongan-budaya'],
                'icon' => 'heroicon-o-building-library',
                'sort_order' => 3,
            ],
            [
                'name' => ['id' => 'Wisata Petualangan', 'en' => 'Adventure Tour', 'ms' => 'Pelancongan Pengembaraan'],
                'slug' => ['id' => 'wisata-petualangan', 'en' => 'adventure-tour', 'ms' => 'pelancongan-pengembaraan'],
                'icon' => 'heroicon-o-map',
                'sort_order' => 4,
            ],
        ];

        foreach ($categories as $category) {
            TourCategory::create($category);
        }
    }
}
