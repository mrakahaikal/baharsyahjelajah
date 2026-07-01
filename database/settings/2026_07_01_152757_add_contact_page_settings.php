<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.map_embed_url', 'https://www.google.com/maps?q=Jakarta%2C%20Indonesia&output=embed');
        $this->migrator->add('general.office_hours', [
            'id' => 'Senin-Sabtu, 09.00-18.00 WIB. Pesan di luar jam operasional akan dibalas pada hari kerja berikutnya.',
            'ms' => 'Isnin-Sabtu, 09.00-18.00 WIB. Mesej di luar waktu operasi akan dibalas pada hari kerja berikutnya.',
            'en' => 'Monday-Saturday, 09:00-18:00 WIB. Messages outside office hours will be replied to on the next business day.',
        ]);
    }
};
