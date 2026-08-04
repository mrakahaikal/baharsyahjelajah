<?php

namespace App\Models;

use Database\Factories\CountryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'slug', 'description', 'iso_alpha_2', 'iso_alpha_3', 'is_active', 'is_featured', 'sort_order'])]
class Country extends Model implements HasMedia
{
    /** @use HasFactory<CountryFactory> */
    use HasFactory;

    use HasTranslations, InteractsWithMedia, SoftDeletes;

    public const string MEDIA_COLLECTION_FLAG = 'flag';

    public const string MEDIA_COLLECTION_COVER = 'cover';

    public array $translatable = ['name', 'description'];

    protected $attributes = [
        'is_active' => true,
        'is_featured' => false,
        'sort_order' => 0,
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_FLAG)
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection(self::MEDIA_COLLECTION_COVER)
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function visaServices(): HasMany
    {
        return $this->hasMany(VisaService::class);
    }

    public function tours(): MorphToMany
    {
        return $this->morphedByMany(Tour::class, 'countryable')
            ->withTimestamps();
    }

    public function tourPackages(): MorphToMany
    {
        return $this->morphedByMany(TourPackage::class, 'countryable')
            ->withTimestamps();
    }

    public function umrahPackages(): MorphToMany
    {
        return $this->morphedByMany(UmrahPackage::class, 'countryable')
            ->withTimestamps();
    }

    public function destinations(): MorphToMany
    {
        return $this->morphedByMany(Destination::class, 'countryable')
            ->withTimestamps();
    }

    public function vehicles(): MorphToMany
    {
        return $this->morphedByMany(Vehicle::class, 'countryable')
            ->withTimestamps();
    }

    public function posts(): MorphToMany
    {
        return $this->morphedByMany(Post::class, 'countryable')
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function getFlagUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl(self::MEDIA_COLLECTION_FLAG) ?: null;
    }

    public function getCoverUrlAttribute(): string
    {
        $url = $this->getFirstMediaUrl(self::MEDIA_COLLECTION_COVER);
        if (filled($url)) {
            return $url;
        }

        $fallbacks = [
            'ID' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1200&q=80',
            'SA' => 'https://images.unsplash.com/photo-1591604466107-ec97de577aff?auto=format&fit=crop&w=1200&q=80',
            'JP' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=1200&q=80',
            'TR' => 'https://images.unsplash.com/photo-1524231757912-21f4fe3a7200?auto=format&fit=crop&w=1200&q=80',
            'MY' => 'https://images.unsplash.com/photo-1596422846543-75c6fc197f07?auto=format&fit=crop&w=1200&q=80',
            'EG' => 'https://images.unsplash.com/photo-1503177119275-0aa32b3a9368?auto=format&fit=crop&w=1200&q=80',
            'AE' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1200&q=80',
            'SG' => 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=1200&q=80',
        ];

        $iso = strtoupper((string) $this->iso_alpha_2);
        if (isset($fallbacks[$iso])) {
            return $fallbacks[$iso];
        }

        $list = array_values($fallbacks);
        $index = ($this->id ?? 0) % count($list);

        return $list[$index];
    }
}
