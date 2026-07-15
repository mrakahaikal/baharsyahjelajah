<?php

namespace App\Models;

use Database\Factories\DestinationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'slug', 'description', 'location', 'map_url', 'is_active', 'is_featured'])]
class Destination extends Model implements HasMedia
{
    /** @use HasFactory<DestinationFactory> */
    use HasFactory;

    use HasTranslations, InteractsWithMedia;

    public const string MEDIA_COLLECTION_GALLERY = 'gallery';

    public array $translatable = ['name', 'description'];

    protected $attributes = [
        'is_active' => true,
        'is_featured' => false,
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function tourPackages(): MorphToMany
    {
        return $this->morphedByMany(TourPackage::class, 'destinationable')
            ->withTimestamps();
    }

    public function itineraries(): MorphToMany
    {
        return $this->morphedByMany(TourPackageItinerary::class, 'destinationable')
            ->withTimestamps();
    }

    public function umrahPackages(): MorphToMany
    {
        return $this->morphedByMany(UmrahPackage::class, 'destinationable')
            ->withTimestamps();
    }

    public function posts(): MorphToMany
    {
        return $this->morphedByMany(Post::class, 'destinationable')
            ->withTimestamps();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_GALLERY)
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->gallery_urls->first();
    }

    /** @return Collection<int, string> */
    public function getGalleryUrlsAttribute(): Collection
    {
        return $this->getMedia(self::MEDIA_COLLECTION_GALLERY)
            ->filter(fn ($media): bool => Storage::disk($media->disk)
                ->exists($media->getPathRelativeToRoot()))
            ->map(fn ($media): string => $media->getUrl())
            ->values();
    }

    public function getSafeMapUrlAttribute(): ?string
    {
        if (! is_string($this->map_url) || filter_var($this->map_url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return in_array(parse_url($this->map_url, PHP_URL_SCHEME), ['http', 'https'], true)
            ? $this->map_url
            : null;
    }
}
