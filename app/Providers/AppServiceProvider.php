<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::macro('image', fn(string $asset) => $this->asset("resources/images/{$asset}"));

        Blade::anonymousComponentPath(resource_path('views/components/partials'), 'partials');
        Blade::anonymousComponentPath(resource_path('views/components/shared'), 'shared');
        Blade::anonymousComponentPath(resource_path('views/components/ui'), 'ui');
        Blade::anonymousComponentPath(resource_path('views/components/forms'), 'forms');
    }
}
