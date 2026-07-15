<?php

namespace App\Models;

use App\Helpers\LocaleHelper;
use App\Services\CurrencyService;
use App\Settings\GeneralSettings;
use Database\Factories\UmrahPackageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'name', 'slug', 'description', 'package_type', 'duration_days', 'price_idr',
    'airline', 'hotel_makkah', 'hotel_makkah_stars', 'hotel_madinah',
    'hotel_madinah_stars', 'room_type', 'visa_included', 'handling_included',
    'thumbnail', 'is_active', 'is_featured',
])]
class UmrahPackage extends Model implements HasMedia
{
    /** @use HasFactory<UmrahPackageFactory> */
    use HasFactory;

    use HasTranslations, InteractsWithMedia, SoftDeletes;

    public const string MEDIA_COLLECTION_COVER = 'cover';

    public const string MEDIA_COLLECTION_GALLERY = 'gallery';

    protected static function booted(): void
    {
        static::forceDeleted(fn (UmrahPackage $package) => $package->destinations()->detach());
    }

    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
            'price_idr' => 'integer',
            'hotel_makkah_stars' => 'integer',
            'hotel_madinah_stars' => 'integer',
            'visa_included' => 'boolean',
            'handling_included' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public array $translatable = ['name', 'slug', 'description'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_COVER)->singleFile();
        $this->addMediaCollection(self::MEDIA_COLLECTION_GALLERY);
    }

    public function departures(): HasMany
    {
        return $this->hasMany(UmrahDeparture::class, 'package_id')
            ->orderBy('departure_date');
    }

    public function upcomingDepartures(): HasMany
    {
        return $this->hasMany(UmrahDeparture::class, 'package_id')
            ->where('departure_date', '>=', now())
            ->where('status', '!=', 'closed')
            ->orderBy('departure_date');
    }

    public function includes(): HasMany
    {
        return $this->hasMany(UmrahInclude::class, 'package_id')
            ->orderBy('sort_order');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(UmrahPackagePrice::class)->orderBy('price_idr');
    }

    public function itineraries(): HasMany
    {
        return $this->hasMany(UmrahPackageItinerary::class)->orderBy('day_number');
    }

    public function destinations(): MorphToMany
    {
        return $this->morphToMany(Destination::class, 'destinationable')
            ->withTimestamps();
    }

    public function testimonials(): MorphMany
    {
        return $this->morphMany(Testimonial::class, 'product')
            ->where('is_active', true)
            ->orderByDesc('created_at');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('package_type', $type);
    }

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
            : 'https://images.unsplash.com/photo-1564767609342-620cb19b2357?auto=format&fit=crop&q=80&w=800';
    }

    public function getFormattedPriceAttribute(): string
    {
        $currency = LocaleHelper::currency();

        return app(CurrencyService::class)->convert($this->starting_price_idr, $currency);
    }

    public function getStartingPriceIdrAttribute(): int
    {
        if ($this->relationLoaded('prices')) {
            return (int) ($this->prices->min('price_idr') ?? $this->price_idr ?? 0);
        }

        return (int) ($this->prices()->min('price_idr') ?? $this->price_idr ?? 0);
    }

    /**
     * Label tipe paket yang sudah diformat.
     */
    public function getPackageTypeLabelAttribute(): string
    {
        return match ($this->package_type) {
            'regular' => 'Regular',
            'plus' => 'Plus',
            'vip' => 'VIP',
            'ramadan' => 'Ramadan',
            default => ucfirst($this->package_type),
        };
    }

    /**
     * Ambil harga keberangkatan terdekat.
     * Kalau departure punya override, pakai itu. Kalau tidak, pakai harga package.
     */
    public function getPriceForDeparture(?UmrahDeparture $departure = null, ?UmrahPackagePrice $packagePrice = null): int
    {
        if ($departure && $packagePrice) {
            $override = $departure->relationLoaded('prices')
                ? $departure->prices->firstWhere('umrah_package_price_id', $packagePrice->id)
                : $departure->prices()->whereBelongsTo($packagePrice, 'packagePrice')->first();

            if ($override) {
                return $override->price_idr;
            }
        }

        if ($departure && $departure->price_override_idr && ! $packagePrice) {
            return $departure->price_override_idr;
        }

        return $packagePrice?->price_idr ?? $this->starting_price_idr;
    }

    /**
     * Apakah masih ada sisa kuota di departures yang upcoming.
     */
    public function getHasAvailabilityAttribute(): bool
    {
        return $this->upcomingDepartures()
            ->where('status', '!=', 'full')
            ->exists();
    }

    public function whatsappUrl(?UmrahDeparture $departure = null, int $pax = 2): string
    {
        $locale = app()->getLocale();
        $currency = LocaleHelper::currency();
        $phone = app(GeneralSettings::class)->whatsapp_number;

        $template = WhatsappTemplate::query()
            ->where('product_type', 'umrah')
            ->where('locale', $locale)
            ->value('template')
            ?? WhatsappTemplate::query()
                ->where('product_type', 'umrah')
                ->where('locale', 'id')
                ->value('template');

        $priceIdr = $this->getPriceForDeparture($departure);
        $departureDate = $departure
            ? $departure->departure_date->translatedFormat('d F Y')
            : '-';

        $message = strtr($template ?? '', [
            '{product_name}' => $this->getTranslation('name', $locale),
            '{duration}' => $this->duration_days,
            '{departure_date}' => $departureDate,
            '{price}' => app(CurrencyService::class)->convert($priceIdr, $currency),
            '{pax}' => $pax,
        ]);

        return 'https://wa.me/'.$phone.'?text='.urlencode($message);
    }
}
