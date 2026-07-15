<?php

namespace App\Models;

use Database\Factories\UmrahPackageItineraryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

#[Fillable(['umrah_package_id', 'day_number', 'title', 'location', 'description'])]
class UmrahPackageItinerary extends Model
{
    /** @use HasFactory<UmrahPackageItineraryFactory> */
    use HasFactory;

    use HasTranslations;

    public array $translatable = ['title', 'location', 'description'];

    protected function casts(): array
    {
        return ['day_number' => 'integer'];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(UmrahPackage::class, 'umrah_package_id');
    }
}
