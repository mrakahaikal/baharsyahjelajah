<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Translatable\HasTranslations;

#[Fillable(['tour_package_id', 'day_number', 'title', 'description'])]
class TourPackageItinerary extends Model
{
    use HasTranslations;

    public array $translatable = ['title', 'description'];

    protected static function booted(): void
    {
        static::deleting(
            fn (TourPackageItinerary $itinerary) => $itinerary->destinations()->detach(),
        );
    }

    protected function casts(): array
    {
        return [
            'day_number' => 'integer',
        ];
    }

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function destinations(): MorphToMany
    {
        return $this->morphToMany(Destination::class, 'destinationable')
            ->withTimestamps();
    }
}
