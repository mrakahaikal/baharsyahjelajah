<?php

namespace App\Models;

use App\Enums\VisaEntryType;
use App\Helpers\LocaleHelper;
use App\Services\CurrencyService;
use Database\Factories\VisaServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'country_id', 'name', 'slug', 'visa_type', 'summary', 'description', 'entry_type',
    'processing_days_min', 'processing_days_max', 'validity_days', 'maximum_stay_days',
    'currency', 'price', 'price_idr', 'is_active', 'is_featured', 'sort_order',
])]
class VisaService extends Model implements HasMedia
{
    /** @use HasFactory<VisaServiceFactory> */
    use HasFactory;

    use HasTranslations, InteractsWithMedia, SoftDeletes;

    public const string MEDIA_COLLECTION_COVER = 'cover';

    public const string MEDIA_COLLECTION_GALLERY = 'gallery';

    public const string DEFAULT_IMAGE = 'images/visa/visa-service-fallback.jpg';

    public array $translatable = ['name', 'visa_type', 'summary', 'description'];

    protected $attributes = [
        'currency' => 'IDR',
        'is_active' => true,
        'is_featured' => false,
        'sort_order' => 0,
    ];

    protected static function booted(): void
    {
        static::saving(function (VisaService $service): void {
            if (blank($service->currency)) {
                $service->currency = (string) config('currencies.base', 'IDR');
            }

            $priceDirty = $service->isDirty('price');
            $priceIdrDirty = $service->isDirty('price_idr');

            if ($priceDirty && ! $priceIdrDirty) {
                if ($service->price === null) {
                    $service->price_idr = null;
                } elseif ($service->currency === 'IDR') {
                    $service->price_idr = (int) round((float) $service->price);
                } else {
                    try {
                        $service->price_idr = (int) round(app(CurrencyService::class)->convertRaw((float) $service->price, 'IDR', $service->currency));
                    } catch (\Throwable) {
                        $service->price_idr = null;
                    }
                }
            } elseif ($priceIdrDirty && ! $priceDirty) {
                $service->price = $service->price_idr;
                if ($service->price_idr !== null && blank($service->currency)) {
                    $service->currency = 'IDR';
                }
            } elseif ($priceDirty && $priceIdrDirty) {
                if ($service->currency === 'IDR' && $service->price !== null) {
                    $service->price_idr = (int) round((float) $service->price);
                }
            }
        });
    }

    protected function casts(): array
    {
        return [
            'entry_type' => VisaEntryType::class,
            'processing_days_min' => 'integer',
            'processing_days_max' => 'integer',
            'validity_days' => 'integer',
            'maximum_stay_days' => 'integer',
            'price' => 'decimal:2',
            'currency' => 'string',
            'price_idr' => 'integer',
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
        $this->addMediaCollection(self::MEDIA_COLLECTION_COVER)
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection(self::MEDIA_COLLECTION_GALLERY)
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class)->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(VisaServiceItem::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopePubliclyAvailable(Builder $query): Builder
    {
        return $query
            ->active()
            ->whereHas('country', fn (Builder $query): Builder => $query
                ->active()
                ->whereNull((new Country)->getQualifiedDeletedAtColumn()));
    }

    public function scopeForCountry(Builder $query, Country|int $country): Builder
    {
        return $query->where('country_id', $country instanceof Country ? $country->getKey() : $country);
    }

    public function getFormattedPriceAttribute(): ?string
    {
        $amount = $this->price ?? $this->price_idr;

        if ($amount === null) {
            return null;
        }

        $currency = $this->currency ?: (string) config('currencies.base', 'IDR');

        return app(CurrencyService::class)->convert(
            (float) $amount,
            LocaleHelper::currency(),
            $currency,
        );
    }

    public function getCoverUrlAttribute(): string
    {
        return $this->getFirstMediaUrl(self::MEDIA_COLLECTION_COVER)
            ?: asset(self::DEFAULT_IMAGE);
    }

    /** @return Collection<int, string> */
    public function getGalleryUrlsAttribute(): Collection
    {
        return collect([$this->cover_url])
            ->merge($this->getMedia(self::MEDIA_COLLECTION_GALLERY)->map->getUrl())
            ->filter()
            ->unique()
            ->values();
    }
}
