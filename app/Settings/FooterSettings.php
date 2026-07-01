<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class FooterSettings extends Settings
{
    public array $subscribe_title;

    public array $subscribe_subtitle;

    public array $copyright_text;

    public static function group(): string
    {
        return 'footer';
    }
}
