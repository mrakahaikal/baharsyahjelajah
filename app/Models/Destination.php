<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'slug', 'description', 'location', 'map_url'])]
class Destination extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia;

    public const string MEDIA_COLLECTION_GALLERY = 'gallery';

    public array $translatable = ['name', 'description'];

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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_GALLERY)
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }
}
