<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin Baharsyah',
            'email' => 'admin@baharsyahjelajah.test',
            'password' => Hash::make('password'),
        ]);

        $this->call([
            // Katalog produk
            DestinationSeeder::class,
            TourCategorySeeder::class,
            TourSeeder::class,
            VehicleSeeder::class,
            UmrahPackageSeeder::class,
            CountrySeeder::class,
            VisaServiceSeeder::class,

            // Konten website
            //            BannerSeeder::class,
            //            FaqSeeder::class,
            //            TestimonialSeeder::class,

            // Blog
            PostCategorySeeder::class,
            PostSeeder::class,

            // Pengaturan (currency rates & WhatsApp templates)
            TravelCatalogSeeder::class,
        ]);
    }
}
