<?php

namespace App\Models;

use App\Services\CurrencyService;
use App\Settings\GeneralSettings;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'name', 'brand', 'model', 'year', 'capacity_pax', 'capacity_luggage',
    'transmission', 'has_ac', 'has_wifi', 'is_available', 'price_per_day_idr',
    'price_per_trip_idr', 'features', 'thumbnail',
])]
class Vehicle extends Model
{
    use HasTranslations, SoftDeletes;

    protected function casts(): array
    {
        return [
            'year'              => 'integer',
            'capacity_pax'      => 'integer',
            'capacity_luggage'  => 'integer',
            'has_ac'            => 'boolean',
            'has_wifi'          => 'boolean',
            'is_available'      => 'boolean',
            'price_per_day_idr' => 'integer',
            'price_per_trip_idr'=> 'integer',
            'features'          => 'array',
        ];
    }

    public array $translatable = ['name'];

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

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeByCapacity($query, int $minPax)
    {
        return $query->where('capacity_pax', '>=', $minPax);
    }

    /**
     * Nama lengkap kendaraan.
     * Contoh: "Toyota Alphard 2023"
     */
    public function getThumbnailUrlAttribute(): string
    {
        if (str_starts_with($this->thumbnail ?? '', 'http')) {
            return $this->thumbnail;
        }

        return $this->thumbnail 
            ? \Illuminate\Support\Facades\Storage::url($this->thumbnail) 
            : 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&q=80&w=600';
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->brand} {$this->model} {$this->year}");
    }

    public function getFormattedPricePerDayAttribute(): ?string
    {
        if (! $this->price_per_day_idr) {
            return null;
        }

        $currency = session('currency', 'IDR');

        return app(CurrencyService::class)->convert($this->price_per_day_idr, $currency);
    }

    public function getFormattedPricePerTripAttribute(): ?string
    {
        if (! $this->price_per_trip_idr) {
            return null;
        }

        $currency = session('currency', 'IDR');

        return app(CurrencyService::class)->convert($this->price_per_trip_idr, $currency);
    }

    /**
     * Badge fitur utama kendaraan untuk ditampilkan di card.
     * Contoh: ['AC', 'WiFi', 'Automatic', '7 Penumpang']
     */
    public function getFeatureBadgesAttribute(): array
    {
        $badges = [];

        if ($this->has_ac)   $badges[] = 'AC';
        if ($this->has_wifi) $badges[] = 'WiFi';

        $badges[] = ucfirst($this->transmission);
        $badges[] = $this->capacity_pax . ' ' . match (app()->getLocale()) {
                'en' => 'Pax',
                'ms' => 'Penumpang',
                default => 'Penumpang',
            };

        return $badges;
    }

    public function whatsappUrl(?string $date = null): string
    {
        $locale   = app()->getLocale();
        $currency = session('currency', 'IDR');
        $phone    = app(GeneralSettings::class)->whatsapp_number;

        $template = WhatsappTemplate::query()
            ->where('product_type', 'vehicle')
            ->where('locale', $locale)
            ->value('template')
            ?? WhatsappTemplate::query()
                ->where('product_type', 'vehicle')
                ->where('locale', 'id')
                ->value('template');

        // Tentukan harga yang akan ditampilkan (per trip lebih diprioritaskan)
        $price = $this->price_per_trip_idr
            ? app(CurrencyService::class)->convert($this->price_per_trip_idr, $currency)
            : app(CurrencyService::class)->convert($this->price_per_day_idr ?? 0, $currency);

        $message = strtr($template ?? '', [
            '{product_name}' => $this->getTranslation('name', $locale),
            '{capacity}'     => $this->capacity_pax,
            '{date}'         => $date ?? '-',
            '{price}'        => $price,
        ]);

        return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
    }
}
