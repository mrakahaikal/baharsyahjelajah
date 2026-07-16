<?php

use App\Models\Tour;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Support\Facades\Blade;

it('defines localized breadcrumbs for public index pages', function (string $name, string $locale): void {
    app()->setLocale($locale);

    $breadcrumbs = Breadcrumbs::generate($name, $locale);

    expect($breadcrumbs)->toHaveCount(2)
        ->and($breadcrumbs->first()->url)->toBe(route('home', ['locale' => $locale]))
        ->and($breadcrumbs->last()->url)->toBe(route($name, ['locale' => $locale]));
})->with([
    ['transport.index', 'id'],
    ['tour.index', 'en'],
    ['umroh.index', 'ms'],
    ['destination.index', 'id'],
    ['blog.index', 'en'],
    ['visa.index', 'ms'],
    ['shop.index', 'id'],
    ['gallery.index', 'en'],
    ['testimonials.index', 'ms'],
    ['contact.index', 'id'],
]);

it('defines complete hierarchies for public detail pages', function (
    string $name,
    array $parameters,
    int $expectedCount,
): void {
    app()->setLocale($parameters['locale']);

    $breadcrumbs = Breadcrumbs::generate($name, ...array_values($parameters));

    expect($breadcrumbs)->toHaveCount($expectedCount)
        ->and($breadcrumbs->last()->url)->toBe(route($name, $parameters));
})->with([
    'vehicle detail' => ['transport.show', ['locale' => 'id', 'vehicle' => 'toyota-hiace'], 3],
    'vehicle booking' => ['transport.booking', ['locale' => 'en', 'vehicle' => 'toyota-hiace'], 4],
    'tour detail' => ['tour.show', ['locale' => 'ms', 'tour' => 'lawatan-borneo'], 3],
    'tour package' => ['tour.package.show', [
        'locale' => 'id',
        'tour' => 'tour-borneo',
        'package' => 'paket-tiga-hari',
    ], 4],
    'tour package booking' => ['tour.package.booking', [
        'locale' => 'en',
        'tour' => 'borneo-tour',
        'package' => 'three-day-package',
    ], 5],
    'umrah detail' => ['umroh.show', ['locale' => 'id', 'umrah' => 'paket-umrah-plus'], 3],
    'destination detail' => ['destination.show', ['locale' => 'ms', 'destination' => 'tanjung-puting'], 3],
    'blog detail' => ['blog.show', ['locale' => 'en', 'post' => 'travel-guide'], 3],
    'visa detail' => ['visa.show', ['locale' => 'id', 'visaService' => 'visa-saudi'], 3],
    'shop detail' => ['shop.show', ['locale' => 'en', 'product' => 'travel-bag'], 3],
]);

it('uses localized model titles and slugs without querying the database', function (): void {
    app()->setLocale('en');

    $tour = new Tour([
        'name' => [
            'id' => 'Petualangan Borneo',
            'ms' => 'Pengembaraan Borneo',
            'en' => 'Borneo Adventure',
        ],
        'slug' => [
            'id' => 'petualangan-borneo',
            'ms' => 'pengembaraan-borneo',
            'en' => 'borneo-adventure',
        ],
    ]);

    $breadcrumbs = Breadcrumbs::generate('tour.show', 'en', $tour);

    expect($breadcrumbs->last()->title)->toBe('Borneo Adventure')
        ->and($breadcrumbs->last()->url)->toBe(route('tour.show', [
            'locale' => 'en',
            'tour' => 'borneo-adventure',
        ]));
});

it('renders an accessible breadcrumb component from the registered trail', function (): void {
    app()->setLocale('en');

    $html = Blade::render(
        '<x-ui.breadcrumbs name="tour.show" :parameters="$parameters" />',
        ['parameters' => ['en', 'borneo-adventure']],
    );

    expect($html)
        ->toContain('data-breadcrumbs="tour.show"')
        ->toContain('aria-label="Breadcrumb"')
        ->toContain('aria-current="page"')
        ->toContain(route('home', ['locale' => 'en']))
        ->toContain(route('tour.index', ['locale' => 'en']))
        ->toContain('Borneo Adventure');
});
