<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'title', 'subtitle', 'image_path', 'cta_type', 'cta_value',
    'cta_label', 'sort_order', 'is_active',
])]
class Banner extends Model
{
    use HasTranslations;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active'  => 'boolean',
        ];
    }

    public array $translatable = ['title', 'subtitle', 'cta_label'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Resolusi URL CTA berdasarkan tipe.
     * - 'route'     → route() Laravel
     * - 'url'       → URL langsung
     * - 'whatsapp'  → wa.me link
     */
    public function getCtaUrlAttribute(): ?string
    {
        return match ($this->cta_type) {
            'route'     => route($this->cta_value),
            'url'       => $this->cta_value,
            'whatsapp'  => 'https://wa.me/' . app(\App\Settings\GeneralSettings::class)->whatsapp_number,
            default     => null,
        };
    }
}
