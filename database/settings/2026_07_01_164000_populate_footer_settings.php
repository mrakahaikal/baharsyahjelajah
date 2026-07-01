<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('footer.subscribe_title', [
            'id' => 'Langganan Baharsyah Jelajah',
            'ms' => 'Langgan Baharsyah Jelajah',
            'en' => 'Subscribe to Baharsyah Jelajah',
        ]);

        $this->migrator->add('footer.subscribe_subtitle', [
            'id' => 'Dapatkan info perjalanan dan inspirasi terbaru langsung di email Anda.',
            'ms' => 'Dapatkan maklumat perjalanan dan inspirasi terkini terus ke e-mel anda.',
            'en' => 'Get the latest travel news and inspiration directly to your inbox.',
        ]);

        $this->migrator->add('footer.copyright_text', [
            'id' => 'PT Baharsyah Jelajah Untuk Semua. Hak Cipta Dilindungi.',
            'ms' => 'PT Baharsyah Jelajah Untuk Semua. Hak Cipta Terpelihara.',
            'en' => 'PT Baharsyah Jelajah Untuk Semua. All rights reserved.',
        ]);
    }
};
