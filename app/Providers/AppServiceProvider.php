<?php

namespace App\Providers;

use App\Contracts\ExchangeRateProvider;
use App\Services\ExchangeRateApiProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ExchangeRateProvider::class, ExchangeRateApiProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Lang::handleMissingKeysUsing(function (string $key, array $replace, string $locale, bool $fallback): ?string {
            if (! str_starts_with($key, 'filament-log-viewer::')) {
                return null;
            }

            $englishTranslation = Lang::get($key, $replace, 'en', false);

            return is_string($englishTranslation) && $englishTranslation !== $key
                ? $englishTranslation
                : null;
        });

        Vite::macro('image', fn (string $asset) => $this->asset("resources/images/{$asset}"));

        Blade::anonymousComponentPath(resource_path('views/components/partials'), 'partials');
        Blade::anonymousComponentPath(resource_path('views/components/shared'), 'shared');
        Blade::anonymousComponentPath(resource_path('views/components/ui'), 'ui');
        Blade::anonymousComponentPath(resource_path('views/components/forms'), 'forms');
    }
}
