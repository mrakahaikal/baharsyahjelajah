<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'slug', 'icon', 'sort_order'])]
class TourCategory extends Model
{
    use HasTranslations;

    public array $translatable = ['name', 'slug'];

    protected $casts = [
        'name' => 'array',
        'slug' => 'array',
    ];

    public function tours(): HasMany
    {
        return $this->hasMany(Tour::class, 'tour_category_id');
    }

    public function activeTours(): HasMany
    {
        return $this->hasMany(Tour::class, 'tour_category_id')
            ->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
