<?php

namespace App\Models;

use App\Helpers\LocaleHelper;
use App\Services\CurrencyService;
use App\Settings\GeneralSettings;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

#[Fillable(['tour_package_id', 'name', 'hotel_stars'])]
class PackageTier extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];

    protected function casts(): array
    {
        return [
            'hotel_stars' => 'integer',
        ];
    }

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function priceTiers(): HasMany
    {
        return $this->hasMany(TourPriceTier::class)->orderBy('min_pax');
    }

    public function priceTierForPax(int $pax): ?TourPriceTier
    {
        if ($this->relationLoaded('priceTiers')) {
            return $this->priceTiers
                ->filter(fn (TourPriceTier $priceTier): bool => $priceTier->min_pax <= $pax
                    && ($priceTier->max_pax === null || $priceTier->max_pax >= $pax))
                ->sortByDesc('min_pax')
                ->first();
        }

        return $this->priceTiers()
            ->where('min_pax', '<=', $pax)
            ->where(function ($query) use ($pax): void {
                $query->where('max_pax', '>=', $pax)
                    ->orWhereNull('max_pax');
            })
            ->reorder('min_pax', 'desc')
            ->first();
    }

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

        $priceTier = $this->priceTierForPax($pax);
        $tourPackage = $this->tourPackage;

        $message = strtr($template ?? '', [
            '{product_name}' => $tourPackage->tour->getTranslation('name', $locale),
            '{duration}' => $tourPackage->duration_label,
            '{price}' => $priceTier
                ? app(CurrencyService::class)->convert((float) $priceTier->price, $currency, $priceTier->currency)
                : '-',
            '{pax}' => $pax,
        ]);

        return 'https://wa.me/'.$phone.'?text='.urlencode($message);
    }
}
