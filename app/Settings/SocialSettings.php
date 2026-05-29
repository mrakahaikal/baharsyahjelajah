<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SocialSettings extends Settings
{
    public ?string $instagram;
    public ?string $facebook;
    public ?string $tiktok;
    public ?string $youtube;

    public static function group(): string
    {
        return 'social';
    }
}
