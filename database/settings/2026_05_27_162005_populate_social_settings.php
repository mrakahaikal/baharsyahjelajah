<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('social.instagram', '@travelcompany');
        $this->migrator->add('social.facebook', 'travelcompany');
        $this->migrator->add('social.tiktok', '@travelcompany');
        $this->migrator->add('social.youtube', null);
    }
};
