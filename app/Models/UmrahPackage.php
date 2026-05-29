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
    'name', 'description', 'package_type', 'duration_days', 'price_idr',
    'airline', 'hotel_makkah', 'hotel_makkah_stars', 'hotel_madinah',
    'hotel_madinah_stars', 'room_type', 'visa_included', 'handling_included',
    'thumbnail', 'is_active',
])]
class UmrahPackage extends Model
{
    use HasTranslations, SoftDeletes;

    protected function casts(): array
    {
        return [
            'duration_days'       => 'integer',
            'price_idr'           => 'integer',
            'hotel_makkah_stars'  => 'integer',
            'hotel_madinah_stars' => 'integer',
            'visa_included'       => 'boolean',
            'handling_included'   => 'boolean',
            'is_active'           => 'boolean',
        ];
    }

    public array $translatable = ['name', 'description'];

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

    public function testimonials(): MorphMany
    {
        return $this->morphMany(Testimonial::class, 'product')
            ->where('is_active', true)
            ->orderByDesc('created_at');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('package_type', $type);
    }

    public function getThumbnailUrlAttribute(): string
    {
        if (str_starts_with($this->thumbnail ?? '', 'http')) {
            return $this->thumbnail;
        }

        return $this->thumbnail 
            ? \Illuminate\Support\Facades\Storage::url($this->thumbnail) 
            : 'https://images.unsplash.com/photo-1564767609342-620cb19b2357?auto=format&fit=crop&q=80&w=800';
    }

    public function getFormattedPriceAttribute(): string
    {
        $currency = session('currency', 'IDR');

        return app(CurrencyService::class)->convert($this->price_idr, $currency);
    }

    /**
     * Label tipe paket yang sudah diformat.
     */
    public function getPackageTypeLabelAttribute(): string
    {
        return match ($this->package_type) {
            'regular' => 'Regular',
            'plus'    => 'Plus',
            'vip'     => 'VIP',
            'ramadan' => 'Ramadan',
            default   => ucfirst($this->package_type),
        };
    }

    /**
     * Ambil harga keberangkatan terdekat.
     * Kalau departure punya override, pakai itu. Kalau tidak, pakai harga package.
     */
    public function getPriceForDeparture(?UmrahDeparture $departure = null): int
    {
        if ($departure && $departure->price_override_idr) {
            return $departure->price_override_idr;
        }

        return $this->price_idr;
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
        $locale   = app()->getLocale();
        $currency = session('currency', 'IDR');
        $phone    = app(GeneralSettings::class)->whatsapp_number;

        $template = WhatsappTemplate::query()
            ->where('product_type', 'umrah')
            ->where('locale', $locale)
            ->value('template')
            ?? WhatsappTemplate::query()
                ->where('product_type', 'umrah')
                ->where('locale', 'id')
                ->value('template');

        $priceIdr      = $this->getPriceForDeparture($departure);
        $departureDate = $departure
            ? $departure->departure_date->translatedFormat('d F Y')
            : '-';

        $message = strtr($template ?? '', [
            '{product_name}'   => $this->getTranslation('name', $locale),
            '{duration}'       => $this->duration_days,
            '{departure_date}' => $departureDate,
            '{price}'          => app(CurrencyService::class)->convert($priceIdr, $currency),
            '{pax}'            => $pax,
        ]);

        return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
    }
}
