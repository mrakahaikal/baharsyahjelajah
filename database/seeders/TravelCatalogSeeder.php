<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TravelCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCurrencyRates();
        $this->seedWhatsappTemplates();
//        $this->seedTourCategories();
    }

    private function seedCurrencyRates(): void
    {
        DB::table('currency_rates')->upsert([
            ['from_currency' => 'IDR', 'to_currency' => 'MYR', 'rate' => 0.00029200, 'created_at' => now(), 'updated_at' => now()],
            ['from_currency' => 'IDR', 'to_currency' => 'SGD', 'rate' => 0.00008500, 'created_at' => now(), 'updated_at' => now()],
        ], ['from_currency', 'to_currency'], ['rate', 'updated_at']);
    }

    private function seedWhatsappTemplates(): void
    {
        $templates = [
            // ── Tour ──────────────────────────────────────────────
            [
                'product_type' => 'tour',
                'locale'       => 'id',
                'template'     => "Assalamu'alaikum,\n\nSaya tertarik dengan paket *{product_name}* ({duration}).\n\nDetail:\n- Jumlah peserta: {pax} orang\n- Estimasi harga: {price}\n\nMohon info ketersediaan dan detail lengkapnya. Terima kasih 🙏",
                'variables'    => json_encode(['product_name', 'duration', 'pax', 'price']),
            ],
            [
                'product_type' => 'tour',
                'locale'       => 'ms',
                'template'     => "Assalamualaikum,\n\nSaya berminat dengan pakej *{product_name}* ({duration}).\n\nButiran:\n- Bilangan peserta: {pax} orang\n- Anggaran harga: {price}\n\nSila maklumkan ketersediaan dan maklumat lanjut. Terima kasih 🙏",
                'variables'    => json_encode(['product_name', 'duration', 'pax', 'price']),
            ],
            [
                'product_type' => 'tour',
                'locale'       => 'en',
                'template'     => "Hello,\n\nI'm interested in the *{product_name}* package ({duration}).\n\nDetails:\n- Number of participants: {pax} person(s)\n- Estimated price: {price}\n\nKindly share availability and full details. Thank you 🙏",
                'variables'    => json_encode(['product_name', 'duration', 'pax', 'price']),
            ],

            // ── Sewa Mobil ────────────────────────────────────────
            [
                'product_type' => 'vehicle',
                'locale'       => 'id',
                'template'     => "Halo,\n\nSaya ingin menyewa *{product_name}* ({capacity} penumpang).\n\nDetail kebutuhan:\n- Tanggal: {date}\n- Estimasi harga: {price}\n\nMohon konfirmasi ketersediaan. Terima kasih 🙏",
                'variables'    => json_encode(['product_name', 'capacity', 'date', 'price']),
            ],
            [
                'product_type' => 'vehicle',
                'locale'       => 'ms',
                'template'     => "Helo,\n\nSaya ingin menyewa *{product_name}* ({capacity} penumpang).\n\nButiran:\n- Tarikh: {date}\n- Anggaran harga: {price}\n\nSila sahkan ketersediaan. Terima kasih 🙏",
                'variables'    => json_encode(['product_name', 'capacity', 'date', 'price']),
            ],
            [
                'product_type' => 'vehicle',
                'locale'       => 'en',
                'template'     => "Hello,\n\nI'd like to rent a *{product_name}* ({capacity} passengers).\n\nDetails:\n- Date: {date}\n- Estimated price: {price}\n\nPlease confirm availability. Thank you 🙏",
                'variables'    => json_encode(['product_name', 'capacity', 'date', 'price']),
            ],

            // ── Umrah ─────────────────────────────────────────────
            [
                'product_type' => 'umrah',
                'locale'       => 'id',
                'template'     => "Assalamu'alaikum,\n\nSaya tertarik dengan *Paket Umrah {product_name}* ({duration} hari).\n\nDetail:\n- Keberangkatan: {departure_date}\n- Jumlah jamaah: {pax} orang\n- Estimasi harga: {price}/orang\n\nMohon info lengkap dan prosedur pendaftaran. Jazakallahu khairan 🙏",
                'variables'    => json_encode(['product_name', 'duration', 'departure_date', 'pax', 'price']),
            ],
            [
                'product_type' => 'umrah',
                'locale'       => 'ms',
                'template'     => "Assalamualaikum,\n\nSaya berminat dengan *Pakej Umrah {product_name}* ({duration} hari).\n\nButiran:\n- Tarikh berangkat: {departure_date}\n- Bilangan jemaah: {pax} orang\n- Anggaran harga: {price}/orang\n\nSila berikan maklumat lengkap dan prosedur pendaftaran. Jazakallahu khairan 🙏",
                'variables'    => json_encode(['product_name', 'duration', 'departure_date', 'pax', 'price']),
            ],
            [
                'product_type' => 'umrah',
                'locale'       => 'en',
                'template'     => "Assalamu'alaikum,\n\nI'm interested in the *Umrah Package {product_name}* ({duration} days).\n\nDetails:\n- Departure date: {departure_date}\n- Number of pilgrims: {pax} person(s)\n- Estimated price: {price}/person\n\nKindly provide full info and registration procedure. Jazakallahu khairan 🙏",
                'variables'    => json_encode(['product_name', 'duration', 'departure_date', 'pax', 'price']),
            ],
        ];

        foreach ($templates as $template) {
            DB::table('whatsapp_templates')->upsert(
                array_merge($template, ['created_at' => now(), 'updated_at' => now()]),
                ['product_type', 'locale'],
                ['template', 'variables', 'updated_at']
            );
        }
    }

//    private function seedTourCategories(): void
//    {
//        $categories = [
//            ['name' => json_encode(['id' => 'City Tour',     'ms' => 'Lawatan Bandar',  'en' => 'City Tour']),    'slug' => json_encode(['id' => 'city-tour',     'ms' => 'lawatan-bandar',  'en' => 'city-tour']),    'icon' => 'heroicon-o-building-office',  'sort_order' => 1],
//            ['name' => json_encode(['id' => 'Wisata Alam',   'ms' => 'Pelancongan Alam','en' => 'Nature Tour']),  'slug' => json_encode(['id' => 'wisata-alam',   'ms' => 'pelancongan-alam','en' => 'nature-tour']),  'icon' => 'heroicon-o-sun',              'sort_order' => 2],
//            ['name' => json_encode(['id' => 'Wisata Religi', 'ms' => 'Pelancongan Agama','en' => 'Religious Tour']),'slug' => json_encode(['id' => 'wisata-religi','ms' => 'pelancongan-agama','en' => 'religious-tour']),'icon' => 'heroicon-o-star',           'sort_order' => 3],
//            ['name' => json_encode(['id' => 'Wisata Kuliner','ms' => 'Pelancongan Kuliner','en' => 'Culinary Tour']),'slug' => json_encode(['id' => 'wisata-kuliner','ms' => 'pelancongan-kuliner','en' => 'culinary-tour']),'icon' => 'heroicon-o-cake',         'sort_order' => 4],
//        ];
//
//        foreach ($categories as $category) {
//            DB::table('tour_categories')->upsert(
//                array_merge($category, ['created_at' => now(), 'updated_at' => now()]),
//                ['sort_order'],
//                ['name', 'slug', 'updated_at']
//            );
//        }
//    }
}
