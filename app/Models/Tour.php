<?php

namespace App\Models;

use App\Enums\TourType;
use App\Models\Concerns\HasLocalizedSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'tour_category_id', 'name', 'slug', 'short_description', 'description',
    'tour_type', 'currency', 'is_active', 'is_featured',
])]
class Tour extends Model
{
    use HasLocalizedSlug, HasTranslations;

    protected function casts(): array
    {
        return [
            'tour_type' => TourType::class,
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public array $translatable = ['name', 'slug', 'short_description', 'description'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(TourCategory::class, 'tour_category_id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(TourPackage::class);
    }

    public function testimonials(): MorphMany
    {
        return $this->morphMany(Testimonial::class, 'product')
            ->where('is_active', true)
            ->orderByDesc('created_at');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeByType(Builder $query, TourType|string $type): Builder
    {
        return $query->where('tour_type', $type instanceof TourType ? $type->value : $type);
    }

    /**
     * Rating rata-rata dari testimonials.
     */
    public function getAverageRatingAttribute(): float
    {
        return round($this->testimonials()->avg('rating') ?? 0, 1);
    }
}
