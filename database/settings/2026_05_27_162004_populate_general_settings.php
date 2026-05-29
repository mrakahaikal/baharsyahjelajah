<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_name', [
            'id' => 'Travel Company',
            'ms' => 'Travel Company',
            'en' => 'Travel Company',
        ]);

        $this->migrator->add('general.meta_description', [
            'id' => 'Paket tour, sewa mobil, dan umrah terpercaya.',
            'ms' => 'Pakej pelancongan, sewa kereta, dan umrah yang dipercayai.',
            'en' => 'Trusted tour packages, car rental, and Umrah services.',
        ]);

        $this->migrator->add('general.address', [
            'id' => 'Jakarta, Indonesia',
            'ms' => 'Jakarta, Indonesia',
            'en' => 'Jakarta, Indonesia',
        ]);

        $this->migrator->add('general.whatsapp_number', '6281234567890');
        $this->migrator->add('general.email', 'info@travelcompany.com');
        $this->migrator->add('general.default_currency', 'IDR');
        $this->migrator->add('general.default_pax', 2);
    }
};
