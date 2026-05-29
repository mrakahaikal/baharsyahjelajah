<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['product_type', 'locale', 'template', 'variables',])]
class WhatsappTemplate extends Model
{
    protected function casts(): array
    {
        return [
            'variables' => 'array',
        ];
    }

    /**
     * Ambil template dan langsung render dengan variabel.
     * Fallback ke locale 'id' kalau locale yang diminta tidak ada.
     *
     * Contoh:
     *   WhatsappTemplate::render('tour', 'ms', ['{product_name}' => 'Tour Bandung', ...])
     */
    public static function render(string $productType, string $locale, array $variables): string
    {
        $template = static::query()
            ->where('product_type', $productType)
            ->where('locale', $locale)
            ->value('template');

        $template ??= static::query()
            ->where('product_type', $productType)
            ->where('locale', 'id')
            ->value('template');

        if (! $template) {
            return '';
        }

        return strtr($template, $variables);
    }
}
