<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tour_id', 'image_path', 'caption', 'sort_order'])]
class TourGallery extends Model
{
    protected function casts(): array
    {
        return [
            'caption' => 'array',    // ['id' => '...', 'ms' => '...', 'en' => '...']
            'sort_order' => 'integer',
        ];
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function getLocalizedCaptionAttribute(): ?string
    {
        $caption = $this->caption;

        if (blank($caption)) {
            return null;
        }

        if (is_string($caption)) {
            return $caption;
        }

        if (! is_array($caption)) {
            return null;
        }

        $locale = app()->getLocale();

        return $caption[$locale] ?? $caption['id'] ?? collect($caption)->filter()->first();
    }
}
