<?php

namespace App\Providers;

use App\Contracts\ExchangeRateProvider;
use App\Services\ExchangeRateApiProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

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

        Passport::tokensExpireIn(now()->addDays(30));
        Passport::refreshTokensExpireIn(now()->addDays(90));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        Passport::tokensCan([
            'mcp:use' => 'Mengakses dan mengeksekusi Model Context Protocol (MCP) server',
            'mcp:read' => 'Membaca konten artikel, kategori, paket tour, dan data Baharsyah Jelajah',
            'mcp:write' => 'Membuat dan menyunting draf artikel serta kategori',
            'mcp:publish' => 'Mempublikasikan atau menarik kembali artikel',
            'mcp:admin' => 'Akses penuh ke seluruh eksekusi Model Context Protocol (termasuk penghapusan)',
        ]);

        Passport::setDefaultScope([
            'mcp:use',
            'mcp:read',
        ]);

        Passport::authorizationView('oauth.authorize');
    }
}
