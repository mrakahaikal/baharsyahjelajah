<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    // Translatable — simpan sebagai array ['id' => ..., 'ms' => ..., 'en' => ...]
    public array $site_name;

    public array $meta_description;

    public array $address;

    // Contact
    public string $whatsapp_number;

    public string $email;

    public ?string $map_embed_url;

    public array $office_hours;

    // Default preferences
    public string $default_currency;   // IDR | MYR | SGD

    public int $default_pax;

    public static function group(): string
    {
        return 'general';
    }
}
