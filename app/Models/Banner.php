<?php

namespace App\Models;

use App\Enums\BannerCtaType;
use App\Enums\BannerPlacement;
use App\Settings\GeneralSettings;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'title', 'subtitle', 'image_path', 'placement', 'cta_type', 'cta_value',
    'cta_label', 'sort_order', 'is_active', 'starts_at', 'ends_at',
])]
class Banner extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia;

    public const MEDIA_COLLECTION_IMAGE = 'image';

    /** @var array<string, string> */
    public const CTA_ROUTE_OPTIONS = [
        'tour.index' => 'Daftar Tur',
        'umroh.index' => 'Daftar Umrah',
        'transport.index' => 'Sewa Kendaraan',
        'blog.index' => 'Blog',
        'contact.index' => 'Kontak',
    ];

    protected $attributes = [
        'placement' => BannerPlacement::HomePromo->value,
        'sort_order' => 0,
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'placement' => BannerPlacement::class,
            'cta_type' => BannerCtaType::class,
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public array $translatable = ['title', 'subtitle', 'cta_label'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_IMAGE)
            ->useDisk('public')
            ->singleFile();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForPlacement(Builder $query, BannerPlacement|string $placement): Builder
    {
        return $query->where(
            'placement',
            $placement instanceof BannerPlacement ? $placement->value : $placement,
        );
    }

    public function scopeWithImage(Builder $query): Builder
    {
        return $query->where(fn (Builder $query): Builder => $query
            ->where(fn (Builder $query): Builder => $query
                ->whereNotNull('image_path')
                ->where('image_path', '!=', ''))
            ->orWhereHas('media', fn (Builder $query): Builder => $query
                ->where('collection_name', self::MEDIA_COLLECTION_IMAGE)));
    }

    public function scopeCurrentlyVisible(Builder $query, ?CarbonInterface $at = null): Builder
    {
        $at ??= now();

        return $query
            ->where(fn (Builder $query): Builder => $query
                ->whereNull('starts_at')
                ->orWhere('starts_at', '<=', $at))
            ->where(fn (Builder $query): Builder => $query
                ->whereNull('ends_at')
                ->orWhere('ends_at', '>=', $at));
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getImageUrlAttribute(): ?string
    {
        $mediaUrl = $this->getFirstMediaUrl(self::MEDIA_COLLECTION_IMAGE);

        if (filled($mediaUrl)) {
            return $mediaUrl;
        }

        if (blank($this->image_path)) {
            return null;
        }

        return str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')
            ? $this->image_path
            : Storage::disk('public')->url($this->image_path);
    }

    public function getCtaUrlAttribute(): ?string
    {
        return $this->ctaUrl();
    }

    public function ctaUrl(?string $locale = null): ?string
    {
        return match ($this->cta_type) {
            BannerCtaType::Route => $this->internalRouteUrl($locale),
            BannerCtaType::Url => $this->externalUrl(),
            BannerCtaType::Whatsapp => $this->whatsappUrl(),
            default => null,
        };
    }

    public function opensCtaInNewTab(): bool
    {
        return in_array($this->cta_type, [BannerCtaType::Url, BannerCtaType::Whatsapp], true);
    }

    private function internalRouteUrl(?string $locale): ?string
    {
        if (! is_string($this->cta_value) || ! array_key_exists($this->cta_value, self::CTA_ROUTE_OPTIONS)) {
            return null;
        }

        return route($this->cta_value, ['locale' => $locale ?? app()->getLocale()]);
    }

    private function externalUrl(): ?string
    {
        if (! is_string($this->cta_value) || filter_var($this->cta_value, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return in_array(parse_url($this->cta_value, PHP_URL_SCHEME), ['http', 'https'], true)
            ? $this->cta_value
            : null;
    }

    private function whatsappUrl(): ?string
    {
        $number = preg_replace('/\D+/', '', app(GeneralSettings::class)->whatsapp_number ?? '');

        return filled($number) ? 'https://wa.me/'.$number : null;
    }
}
