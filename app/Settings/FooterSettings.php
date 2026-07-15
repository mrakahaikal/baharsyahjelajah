<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class FooterSettings extends Settings
{
    public array $brand_description;

    public array $cta_title;

    public array $cta_subtitle;

    public array $cta_button_label;

    public string $cta_button_route;

    public array $social_title;

    public array $social_description;

    public array $contact_title;

    public array $destinations_all_label;

    public int $destination_limit;

    public array $link_groups;

    public array $legal_links;

    public array $copyright_text;

    public static function group(): string
    {
        return 'footer';
    }
}
