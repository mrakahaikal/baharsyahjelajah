<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

#[Fillable(['tour_id', 'item', 'type', 'sort_order'])]
class TourInclude extends Model
{
    use HasTranslations;

    public array $translatable = ['item'];

    protected $casts = [
        'item' => 'array',
        'sort_order' => 'integer',
    ];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function scopeIncludes($query)
    {
        return $query->where('type', 'include');
    }

    public function scopeExcludes($query)
    {
        return $query->where('type', 'exclude');
    }

    public function scopeNotes($query)
    {
        return $query->where('type', 'note');
    }

    public function getIconAttribute(): string
    {
        return match ($this->type) {
            'include' => '✓',
            'exclude' => '✗',
            'note'    => 'ℹ',
            default   => '•',
        };
    }
}
