<?php

namespace App\Models;

use Database\Factories\CountryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'slug', 'iso_alpha_2', 'iso_alpha_3', 'is_active', 'sort_order'])]
class Country extends Model implements HasMedia
{
    /** @use HasFactory<CountryFactory> */
    use HasFactory;

    use HasTranslations, InteractsWithMedia, SoftDeletes;

    public const string MEDIA_COLLECTION_FLAG = 'flag';

    public array $translatable = ['name'];

    protected $attributes = [
        'is_active' => true,
        'sort_order' => 0,
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
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
    }

    public function visaServices(): HasMany
    {
        return $this->hasMany(VisaService::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getFlagUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl(self::MEDIA_COLLECTION_FLAG) ?: null;
    }
}
