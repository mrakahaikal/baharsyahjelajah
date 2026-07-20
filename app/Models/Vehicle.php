<?php

namespace App\Models;

use App\Enums\VehicleCategory;
use App\Helpers\LocaleHelper;
use App\Models\Concerns\HasLocalizedSlug;
use App\Services\CurrencyService;
use Database\Factories\VehicleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'catalog_code', 'name', 'slug', 'description', 'brand', 'model', 'year', 'capacity_pax',
    'capacity_luggage', 'transmission', 'has_ac', 'has_wifi', 'is_active',
    'is_featured', 'price_per_day_idr', 'price_per_trip_idr', 'features', 'thumbnail',
    'category', 'capacity_label', 'overtime_rate_idr', 'sort_order',
])]
class Vehicle extends Model implements HasMedia
{
    /** @use HasFactory<VehicleFactory> */
    use HasFactory;

    use HasLocalizedSlug, HasTranslations, InteractsWithMedia, SoftDeletes;

    public const string MEDIA_COLLECTION_COVER = 'cover';

    public const string MEDIA_COLLECTION_GALLERY = 'gallery';

    protected $attributes = [
        'capacity_luggage' => 0,
        'transmission' => 'automatic',
        'has_ac' => true,
        'has_wifi' => false,
        'is_active' => true,
        'is_featured' => false,
        'sort_order' => 0,
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'capacity_pax' => 'integer',
            'capacity_luggage' => 'integer',
            'has_ac' => 'boolean',
            'has_wifi' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'category' => VehicleCategory::class,
            'price_per_day_idr' => 'integer',
            'price_per_trip_idr' => 'integer',
            'overtime_rate_idr' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public array $translatable = ['name', 'slug', 'description', 'features', 'capacity_label'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_COVER)->singleFile();
        $this->addMediaCollection(self::MEDIA_COLLECTION_GALLERY);
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(VehicleGallery::class)->orderBy('sort_order');
    }

    public function testimonials(): MorphMany
    {
        return $this->morphMany(Testimonial::class, 'product')
            ->where('is_active', true)
            ->orderByDesc('created_at');
    }

    public function rentalRates(): HasMany
    {
        return $this->hasMany(VehicleRentalRate::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByCapacity(Builder $query, int $minimumPassengers): Builder
    {
        return $query->whereNotNull('capacity_pax')->where('capacity_pax', '>=', $minimumPassengers);
    }

    /**
     * Nama lengkap kendaraan.
     * Contoh: "Toyota Alphard 2023"
     */
    public function getThumbnailUrlAttribute(): string
    {
        $coverUrl = $this->getFirstMediaUrl(self::MEDIA_COLLECTION_COVER);

        if (filled($coverUrl)) {
            return $coverUrl;
        }

        if (str_starts_with($this->thumbnail ?? '', 'http')) {
            return $this->thumbnail;
        }

        return $this->thumbnail
            ? Storage::url($this->thumbnail)
            : 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&q=80&w=600';
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->brand} {$this->model} {$this->year}");
    }

    /** @return Collection<int, string> */
    public function galleryUrls(): Collection
    {
        $mediaUrls = $this->getMedia(self::MEDIA_COLLECTION_GALLERY)
            ->map(fn ($media): string => $media->getUrl());

        if ($mediaUrls->isNotEmpty()) {
            return $mediaUrls->values();
        }

        return $this->galleries
            ->map(fn (VehicleGallery $gallery): string => str_starts_with($gallery->image_path, 'http')
                ? $gallery->image_path
                : Storage::url($gallery->image_path))
            ->values();
    }

    public function getFormattedPricePerDayAttribute(): ?string
    {
        if (! $this->price_per_day_idr) {
            return null;
        }

        $currency = LocaleHelper::currency();

        return app(CurrencyService::class)->convert($this->price_per_day_idr, $currency);
    }

    public function getFormattedPricePerTripAttribute(): ?string
    {
        if (! $this->price_per_trip_idr) {
            return null;
        }

        $currency = LocaleHelper::currency();

        return app(CurrencyService::class)->convert($this->price_per_trip_idr, $currency);
    }

    public function getFormattedOvertimeRateAttribute(): ?string
    {
        if (! $this->overtime_rate_idr) {
            return null;
        }

        return app(CurrencyService::class)->convert($this->overtime_rate_idr, LocaleHelper::currency());
    }

    public function getCapacityDisplayAttribute(): string
    {
        return $this->capacity_label ?: ($this->capacity_pax
            ? __('transport.capacity.pax', ['count' => $this->capacity_pax])
            : __('transport.capacity.on_request'));
    }

    /**
     * Badge fitur utama kendaraan untuk ditampilkan di card.
     * Contoh: ['AC', 'WiFi', 'Automatic', '7 Penumpang']
     */
    public function getFeatureBadgesAttribute(): array
    {
        $badges = [];

        if ($this->has_ac) {
            $badges[] = 'AC';
        }

        if ($this->has_wifi) {
            $badges[] = 'WiFi';
        }

        if ($this->transmission) {
            $badges[] = __('transport.transmission.'.$this->transmission);
        }

        $badges[] = $this->capacity_display;

        return $badges;
    }
}
