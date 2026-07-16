<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $emptyPage = [
            'title' => ['id' => null, 'en' => null, 'ms' => null],
            'description' => ['id' => null, 'en' => null, 'ms' => null],
            'og_title' => ['id' => null, 'en' => null, 'ms' => null],
            'og_description' => ['id' => null, 'en' => null, 'ms' => null],
            'og_image' => null,
        ];

        $this->migrator->add('seo.pages', [
            'home' => $emptyPage,
            'contact_index' => $emptyPage,
            'tour_index' => $emptyPage,
            'umroh_index' => $emptyPage,
            'visa_index' => $emptyPage,
            'transport_index' => $emptyPage,
            'destination_index' => $emptyPage,
            'blog_index' => $emptyPage,
        ]);
    }
};
