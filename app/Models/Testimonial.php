<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'reviewer_name', 'reviewer_country', 'reviewer_flag', 'product_type', 'product_id',
    'rating', 'content', 'photo', 'is_featured', 'is_active',
])]
class Testimonial extends Model
{
    use HasTranslations;

    protected function casts(): array
    {
        return [
            'rating'      => 'integer',
            'is_featured' => 'boolean',
            'is_active'   => 'boolean',
        ];
    }

    public array $translatable = ['reviewer_name', 'content'];

    /**
     * Polymorphic: bisa ke Tour, Vehicle, atau UmrahPackage.
     */
    public function product(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeForProduct($query, string $type, int $id)
    {
        return $query->where('product_type', $type)->where('product_id', $id);
    }

    /**
     * Bintang dalam format string untuk display.
     * Contoh: "★★★★☆" untuk rating 4
     */
    public function getStarsAttribute(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    /**
     * Label tipe produk untuk display di CMS.
     */
    public function getProductTypeLabelAttribute(): string
    {
        return match ($this->product_type) {
            'App\Models\Tour'         => 'Tour',
            'App\Models\Vehicle'      => 'Sewa Mobil',
            'App\Models\UmrahPackage' => 'Umrah',
            default                   => $this->product_type,
        };
    }
}
