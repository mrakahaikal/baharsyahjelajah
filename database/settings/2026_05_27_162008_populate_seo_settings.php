<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('seo.og_title', [
            'id' => 'Travel Company — Tour, Sewa Mobil & Umrah',
            'ms' => 'Travel Company — Pelancongan, Sewa Kereta & Umrah',
            'en' => 'Travel Company — Tours, Car Rental & Umrah',
        ]);
        $this->migrator->add('seo.og_description', [
            'id' => 'Temukan paket perjalanan terbaik bersama kami.',
            'ms' => 'Temui pakej perjalanan terbaik bersama kami.',
            'en' => 'Discover the best travel packages with us.',
        ]);
        $this->migrator->add('seo.og_image', null);
        $this->migrator->add('seo.google_analytics_id', null);
        $this->migrator->add('seo.meta_pixel_id', null);
    }
};
