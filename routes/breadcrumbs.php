<?php

use App\Models\Destination;
use App\Models\Post;
use App\Models\Tour;
use App\Models\TourPackage;
use App\Models\UmrahPackage;
use App\Models\Vehicle;
use App\Models\VisaService;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

$breadcrumbTitle = static function (Model|string $value, string $attribute = 'name'): string {
    if (is_string($value)) {
        return Str::headline($value);
    }

    return (string) $value->{$attribute};
};

$breadcrumbSlug = static function (Model|string $value, string $locale): string {
    if (is_string($value)) {
        return $value;
    }

    if ($value instanceof Destination || $value instanceof VisaService) {
        return (string) $value->getRouteKey();
    }

    $slug = $value->getTranslation('slug', $locale, false)
        ?: $value->getTranslation('slug', 'id', false);

    return filled($slug) ? $slug : (string) $value->getKey();
};

Breadcrumbs::for('home', function (BreadcrumbTrail $trail, string $locale): void {
    $trail->push(__('frontend.blog.breadcrumb.home'), route('home', ['locale' => $locale]));
});

$indexBreadcrumbs = [
    'transport.index' => 'frontend.nav.transport',
    'tour.index' => 'frontend.tour.show.breadcrumb_tours',
    'umroh.index' => 'frontend.nav.umroh',
    'destination.index' => 'destination.breadcrumb.index',
    'blog.index' => 'frontend.blog.breadcrumb.current',
    'visa.index' => 'visa.breadcrumb.index',
    'shop.index' => 'frontend.nav.shop',
    'gallery.index' => 'frontend.tour.gallery',
    'testimonials.index' => 'frontend.testimonials.title',
    'contact.index' => 'frontend.contact_page.breadcrumb.current',
];

foreach ($indexBreadcrumbs as $routeName => $translationKey) {
    Breadcrumbs::for($routeName, function (BreadcrumbTrail $trail, string $locale) use ($routeName, $translationKey): void {
        $trail->parent('home', $locale);
        $trail->push(__($translationKey), route($routeName, ['locale' => $locale]));
    });
}

Breadcrumbs::for('transport.show', function (
    BreadcrumbTrail $trail,
    string $locale,
    Vehicle|string $vehicle,
) use ($breadcrumbSlug, $breadcrumbTitle): void {
    $trail->parent('transport.index', $locale);
    $trail->push($breadcrumbTitle($vehicle), route('transport.show', [
        'locale' => $locale,
        'vehicle' => $breadcrumbSlug($vehicle, $locale),
    ]));
});

Breadcrumbs::for('transport.booking', function (
    BreadcrumbTrail $trail,
    string $locale,
    Vehicle|string $vehicle,
) use ($breadcrumbSlug): void {
    $trail->parent('transport.show', $locale, $vehicle);
    $trail->push(__('transport.booking.eyebrow'), route('transport.booking', [
        'locale' => $locale,
        'vehicle' => $breadcrumbSlug($vehicle, $locale),
    ]));
});

Breadcrumbs::for('tour.show', function (
    BreadcrumbTrail $trail,
    string $locale,
    Tour|string $tour,
) use ($breadcrumbSlug, $breadcrumbTitle): void {
    $trail->parent('tour.index', $locale);
    $trail->push($breadcrumbTitle($tour), route('tour.show', [
        'locale' => $locale,
        'tour' => $breadcrumbSlug($tour, $locale),
    ]));
});

Breadcrumbs::for('tour.package.show', function (
    BreadcrumbTrail $trail,
    string $locale,
    Tour|string $tour,
    TourPackage|string $package,
) use ($breadcrumbSlug, $breadcrumbTitle): void {
    $trail->parent('tour.show', $locale, $tour);
    $trail->push($breadcrumbTitle($package), route('tour.package.show', [
        'locale' => $locale,
        'tour' => $breadcrumbSlug($tour, $locale),
        'package' => $breadcrumbSlug($package, $locale),
    ]));
});

Breadcrumbs::for('tour.package.booking', function (
    BreadcrumbTrail $trail,
    string $locale,
    Tour|string $tour,
    TourPackage|string $package,
) use ($breadcrumbSlug): void {
    $trail->parent('tour.package.show', $locale, $tour, $package);
    $trail->push(__('frontend.tour.booking.breadcrumb'), route('tour.package.booking', [
        'locale' => $locale,
        'tour' => $breadcrumbSlug($tour, $locale),
        'package' => $breadcrumbSlug($package, $locale),
    ]));
});

Breadcrumbs::for('umroh.show', function (
    BreadcrumbTrail $trail,
    string $locale,
    UmrahPackage|string $umrah,
) use ($breadcrumbSlug, $breadcrumbTitle): void {
    $trail->parent('umroh.index', $locale);
    $trail->push($breadcrumbTitle($umrah), route('umroh.show', [
        'locale' => $locale,
        'umrah' => $breadcrumbSlug($umrah, $locale),
    ]));
});

Breadcrumbs::for('destination.show', function (
    BreadcrumbTrail $trail,
    string $locale,
    Destination|string $destination,
) use ($breadcrumbSlug, $breadcrumbTitle): void {
    $trail->parent('destination.index', $locale);
    $trail->push($breadcrumbTitle($destination), route('destination.show', [
        'locale' => $locale,
        'destination' => $breadcrumbSlug($destination, $locale),
    ]));
});

Breadcrumbs::for('blog.show', function (
    BreadcrumbTrail $trail,
    string $locale,
    Post|string $post,
) use ($breadcrumbSlug, $breadcrumbTitle): void {
    $trail->parent('blog.index', $locale);
    $trail->push($breadcrumbTitle($post, 'title'), route('blog.show', [
        'locale' => $locale,
        'post' => $breadcrumbSlug($post, $locale),
    ]));
});

Breadcrumbs::for('visa.show', function (
    BreadcrumbTrail $trail,
    string $locale,
    VisaService|string $visaService,
) use ($breadcrumbSlug, $breadcrumbTitle): void {
    $trail->parent('visa.index', $locale);
    $trail->push($breadcrumbTitle($visaService), route('visa.show', [
        'locale' => $locale,
        'visaService' => $breadcrumbSlug($visaService, $locale),
    ]));
});

Breadcrumbs::for('shop.show', function (BreadcrumbTrail $trail, string $locale, string $product): void {
    $trail->parent('shop.index', $locale);
    $trail->push(Str::headline($product), route('shop.show', [
        'locale' => $locale,
        'product' => $product,
    ]));
});
