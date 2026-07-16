<?php

use App\Models\Post;
use App\Models\Tour;
use App\Models\TourPackage;
use App\Models\UmrahPackage;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Lang;

it('uses Indonesian as the fallback for translatable model titles', function (): void {
    app()->setLocale('en');

    $models = [
        [new Tour, 'name', 'Tour Indonesia'],
        [new TourPackage, 'name', 'Paket Indonesia'],
        [new UmrahPackage, 'name', 'Umrah Indonesia'],
        [new Vehicle, 'name', 'Kendaraan Indonesia'],
        [new Post, 'title', 'Artikel Indonesia'],
    ];

    foreach ($models as [$model, $attribute, $indonesianValue]) {
        $model->setTranslations($attribute, [
            'id' => $indonesianValue,
            'en' => '',
        ]);

        expect($model->{$attribute})->toBe($indonesianValue);
    }
});

it('falls back missing log viewer translations to English only', function (): void {
    app()->setLocale('id');

    expect(config('app.fallback_locale'))->toBe('id')
        ->and(Lang::get('filament-log-viewer::log.navigation.title'))->toBe('Log Viewer')
        ->and(Lang::get('filament-log-viewer::log.table.actions.refresh.label'))->toBe('Refresh')
        ->and(Lang::get('filament-log-viewer::log.table.filters.indicators.logs_from_to', [
            'from' => '1 July',
            'until' => '16 July',
        ]))->toBe('Logs from 1 July to 16 July')
        ->and(Lang::get('application.missing.translation'))->toBe('application.missing.translation');
});
