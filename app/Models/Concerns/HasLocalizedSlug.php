<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasLocalizedSlug
{
    /** @var list<string> */
    private static array $slugLocales = ['id', 'en', 'ms'];

    public function localizedSlug(?string $locale = null): string
    {
        $preferredLocales = array_values(array_unique(array_filter([
            $locale ?? app()->getLocale(),
            'id',
            ...self::$slugLocales,
        ])));

        foreach ($preferredLocales as $preferredLocale) {
            $slug = $this->getTranslation('slug', $preferredLocale, false);

            if (filled($slug)) {
                return (string) $slug;
            }
        }

        $firstAvailableSlug = collect($this->getTranslations('slug'))
            ->first(fn (mixed $slug): bool => filled($slug));

        return (string) ($firstAvailableSlug ?: $this->getKey());
    }

    public function scopeWhereLocalizedSlug(Builder $query, string $slug): Builder
    {
        return $query->where(function (Builder $query) use ($slug): void {
            $query
                ->where('slug->id', $slug)
                ->orWhere('slug->en', $slug)
                ->orWhere('slug->ms', $slug);

            if (ctype_digit($slug)) {
                $query->orWhereKey((int) $slug);
            }
        });
    }
}
