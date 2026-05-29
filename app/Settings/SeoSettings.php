<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SeoSettings extends Settings
{
    public array   $og_title;
    public array   $og_description;
    public ?string $og_image;
    public ?string $google_analytics_id;
    public ?string $meta_pixel_id;

    public static function group(): string
    {
        return 'seo';
    }
}
