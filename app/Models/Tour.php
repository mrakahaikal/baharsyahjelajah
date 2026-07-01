<?php

namespace App\Models;

use App\Helpers\LocaleHelper;
use App\Services\CurrencyService;
use App\Settings\GeneralSettings;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'category_id', 'name', 'slug', 'description', 'highlights',
    'tour_type', 'duration_days', 'duration_nights', 'price', 'currency',
    'difficulty', 'max_pax', 'is_active', 'is_featured', 'thumbnail',
])]
class Tour extends Model
{
    use HasTranslations;

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'duration_days' => 'integer',
            'duration_nights' => 'integer',
            'max_pax' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public array $translatable = ['name', 'slug', 'description', 'highlights'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(TourCategory::class);
    }

    public function itineraries(): HasMany
    {
        return $this->hasMany(TourItinerary::class)->orderBy('day_number');
    }

    public function includes(): HasMany
    {
        return $this->hasMany(TourInclude::class)->orderBy('sort_order');
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(TourGallery::class)->orderBy('sort_order');
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

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('tour_type', $type);
    }

    public function scopePriceBetween($query, int $min, int $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    public function getThumbnailUrlAttribute(): string
    {
        if (str_starts_with($this->thumbnail ?? '', 'http')) {
            return $this->thumbnail;
        }

        return $this->thumbnail
            ? Storage::url($this->thumbnail)
            : 'https://images.unsplash.com/photo-1524231757912-21f4fe3a7200?auto=format&fit=crop&q=80&w=800';
    }

    /**
     * Label durasi yang sudah diformat.
     * Contoh output: "3 Hari 2 Malam"
     */
    public function getDurationLabelAttribute(): string
    {
        $locale = app()->getLocale();

        $days = match ($locale) {
            'ms' => $this->duration_days.' Hari',
            'en' => $this->duration_days.' Day'.($this->duration_days > 1 ? 's' : ''),
            default => $this->duration_days.' Hari',
        };

        if ($this->duration_nights > 0) {
            $nights = match ($locale) {
                'ms' => $this->duration_nights.' Malam',
                'en' => $this->duration_nights.' Night'.($this->duration_nights > 1 ? 's' : ''),
                default => $this->duration_nights.' Malam',
            };

            return "$days $nights";
        }

        return $days;
    }

    /**
     * Harga sudah diformat sesuai currency aktif di session.
     * Contoh output: "Rp 1.500.000" atau "RM 438.00"
     */
    public function getFormattedPriceAttribute(): string
    {
        $currency = LocaleHelper::currency();

        return app(CurrencyService::class)->convert($this->price, $currency, $this->currency);
    }

    /**
     * Rating rata-rata dari testimonials.
     */
    public function getAverageRatingAttribute(): float
    {
        return round($this->testimonials()->avg('rating') ?? 0, 1);
    }

    /**
     * Generate WhatsApp URL dengan template sesuai locale aktif.
     */
    public function whatsappUrl(int $pax = 2): string
    {
        $locale = app()->getLocale();
        $currency = LocaleHelper::currency();
        $phone = app(GeneralSettings::class)->whatsapp_number;

        $template = WhatsappTemplate::query()
            ->where('product_type', 'tour')
            ->where('locale', $locale)
            ->value('template');

        // Fallback ke 'id' kalau template locale tidak ada
        $template ??= WhatsappTemplate::query()
            ->where('product_type', 'tour')
            ->where('locale', 'id')
            ->value('template');

        $message = strtr($template ?? '', [
            '{product_name}' => $this->getTranslation('name', $locale),
            '{duration}' => $this->duration_label,
            '{price}' => app(CurrencyService::class)->convert($this->price, $currency, $this->currency),
            '{pax}' => $pax,
        ]);

        return 'https://wa.me/'.$phone.'?text='.urlencode($message);
    }

    /**
     * Semua item include saja (exclude type 'exclude' dan 'note').
     */
    public function getIncludesOnlyAttribute()
    {
        return $this->includes->where('type', 'include');
    }

    /**
     * Semua item exclude saja.
     */
    public function getExcludesOnlyAttribute()
    {
        return $this->includes->where('type', 'exclude');
    }
}
