<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

#[Fillable(['tour_id', 'name', 'slug', 'duration_days', 'duration_nights'])]
class TourPackage extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia;

    public const string MEDIA_COLLECTION_COVER = 'cover';

    public const string MEDIA_COLLECTION_GALLERY = 'gallery';

    public const string DEFAULT_COVER_URL = 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80';

    public array $translatable = ['name', 'slug'];

    protected static function booted(): void
    {
        static::deleting(function (TourPackage $tourPackage): void {
            $tourPackage->destinations()->detach();

            $tourPackage->itineraries()
                ->eachById(fn (TourPackageItinerary $itinerary) => $itinerary->destinations()->detach());
        });
    }

    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
            'duration_nights' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_COVER)
            ->singleFile();

        $this->addMediaCollection(self::MEDIA_COLLECTION_GALLERY);
    }

    public function localizedMediaCaption(Media $media): ?string
    {
        $captions = $media->getCustomProperty('caption');

        if (blank($captions)) {
            return null;
        }

        if (is_string($captions)) {
            return $captions;
        }

        if (! is_array($captions)) {
            return null;
        }

        $locale = app()->getLocale();

        return $captions[$locale] ?? $captions['id'] ?? collect($captions)->filter()->first();
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function itineraries(): HasMany
    {
        return $this->hasMany(TourPackageItinerary::class)->orderBy('day_number');
    }

    public function destinations(): MorphToMany
    {
        return $this->morphToMany(Destination::class, 'destinationable')
            ->withTimestamps();
    }

    public function includes(): HasMany
    {
        return $this->hasMany(TourPackageInclude::class)->orderBy('sort_order');
    }

    public function tiers(): HasMany
    {
        return $this->hasMany(PackageTier::class);
    }

    public function startingPriceTier(): ?TourPriceTier
    {
        $this->loadMissing('tiers.priceTiers');

        return $this->tiers
            ->flatMap(fn (PackageTier $tier) => $tier->priceTiers)
            ->sortBy(fn (TourPriceTier $priceTier): float => (float) $priceTier->price)
            ->first();
    }

    public function getCoverUrlAttribute(): string
    {
        $url = $this->getFirstMediaUrl(self::MEDIA_COLLECTION_COVER);

        return filled($url) ? $url : self::DEFAULT_COVER_URL;
    }

    /**
     * Label durasi yang sudah diformat.
     * Contoh output: "3 Hari 2 Malam"
     */
    public function getDurationLabelAttribute(): string
    {
        $locale = app()->getLocale();

        $days = match ($locale) {
            'en' => $this->duration_days.' Day'.($this->duration_days > 1 ? 's' : ''),
            default => $this->duration_days.' Hari',
        };

        if ($this->duration_nights > 0) {
            $nights = match ($locale) {
                'en' => $this->duration_nights.' Night'.($this->duration_nights > 1 ? 's' : ''),
                default => $this->duration_nights.' Malam',
            };

            return "$days $nights";
        }

        return $days;
    }
}
