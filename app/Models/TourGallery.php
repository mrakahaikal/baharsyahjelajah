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
            'caption'    => 'array',    // ['id' => '...', 'ms' => '...', 'en' => '...']
            'sort_order' => 'integer',
        ];
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function getLocalizedCaptionAttribute(): ?string
    {
        if (empty($this->caption)) {
            return null;
        }

        $locale = app()->getLocale();

        return $this->caption[$locale] ?? $this->caption['id'] ?? null;
    }
}
