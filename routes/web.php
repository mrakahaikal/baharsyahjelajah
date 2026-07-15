<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\UmrahPackageController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VisaServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    $supported = ['id', 'ms', 'en'];

    $detected = collect($request->getLanguages())
        ->map(fn (string $lang) => strtolower(substr($lang, 0, 2)))
        ->first(fn (string $primary) => in_array($primary, $supported))
        ?? 'id';

    return redirect('/'.$detected);
});

Route::get('/kontak', function (Request $request) {
    $supported = ['id', 'ms', 'en'];

    $detected = collect($request->getLanguages())
        ->map(fn (string $lang) => strtolower(substr($lang, 0, 2)))
        ->first(fn (string $primary) => in_array($primary, $supported))
        ?? 'id';

    return redirect()->route('contact.index', [
        'locale' => $detected,
        ...$request->query(),
    ]);
})->name('contact.redirect');

Route::get('/tour/{path?}', function (Request $request, ?string $path = null) {
    $destination = '/id/tour'.($path !== null ? '/'.$path : '');

    if ($queryString = $request->getQueryString()) {
        $destination .= '?'.$queryString;
    }

    return redirect($destination);
})
    ->where('path', '.*')
    ->name('tour.redirect');

Route::get('/blog/{path?}', function (Request $request, ?string $path = null) {
    $destination = '/id/blog'.($path !== null ? '/'.$path : '');

    if ($queryString = $request->getQueryString()) {
        $destination .= '?'.$queryString;
    }

    return redirect($destination);
})
    ->where('path', '.*')
    ->name('blog.redirect');

Route::get('/umroh/{path?}', function (Request $request, ?string $path = null) {
    $destination = '/id/umroh'.($path !== null ? '/'.$path : '');

    if ($queryString = $request->getQueryString()) {
        $destination .= '?'.$queryString;
    }

    return redirect($destination);
})
    ->where('path', '.*')
    ->name('umroh.redirect');

Route::get('/transport/{path?}', function (Request $request, ?string $path = null) {
    $destination = '/id/transport'.($path !== null ? '/'.$path : '');

    if ($queryString = $request->getQueryString()) {
        $destination .= '?'.$queryString;
    }

    return redirect($destination);
})
    ->where('path', '.*')
    ->name('transport.redirect');

Route::get('/destinasi/{path?}', function (Request $request, ?string $path = null) {
    $destination = '/id/destinasi'.($path !== null ? '/'.$path : '');

    if ($queryString = $request->getQueryString()) {
        $destination .= '?'.$queryString;
    }

    return redirect($destination);
})
    ->where('path', '.*')
    ->name('destination.redirect');

Route::get('/visa/{path?}', function (Request $request, ?string $path = null) {
    $destination = '/id/visa'.($path !== null ? '/'.$path : '');

    if ($queryString = $request->getQueryString()) {
        $destination .= '?'.$queryString;
    }

    return redirect($destination);
})
    ->where('path', '.*')
    ->name('visa.redirect');

// Route for Currency Switcher
Route::get('/set-currency/{currency}', function (string $currency) {
    $currency = strtoupper($currency);

    if (array_key_exists($currency, config('currencies.supported', []))) {
        session(['app_currency' => $currency]);
    }

    return back();
})->name('set.currency');

Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => 'id|ms|en'],
    'middleware' => 'set.locale',
], function () {
    Route::get('/', HomeController::class)->name('home');

    Route::prefix('transport')->group(function () {
        Route::get('/', [VehicleController::class, 'index'])->name('transport.index');
        Route::get('/{vehicle}/booking', [VehicleController::class, 'booking'])->name('transport.booking');
        Route::get('/{vehicle}', [VehicleController::class, 'show'])->name('transport.show');
    });

    Route::prefix('tour')->group(function () {
        Route::get('/', [TourController::class, 'index'])
            ->name('tour.index');
        Route::get('/{tour}/package/{package}', [TourController::class, 'showPackage'])
            ->name('tour.package.show');
        Route::get('/{tour}/package/{package}/booking', [TourController::class, 'booking'])
            ->name('tour.package.booking');
        Route::get('/{tour}', [TourController::class, 'show'])
            ->name('tour.show');
    });

    Route::prefix('umroh')->group(function () {
        Route::get('/', [UmrahPackageController::class, 'index'])->name('umroh.index');
        Route::get('/{umrah}', [UmrahPackageController::class, 'show'])->name('umroh.show');
    });

    Route::prefix('destinasi')->group(function () {
        Route::get('/', [DestinationController::class, 'index'])->name('destination.index');
        Route::get('/{destination}', [DestinationController::class, 'show'])->name('destination.show');
    });

    Route::prefix('blog')->group(function () {
        Route::get('/', [BlogController::class, 'index'])->name('blog.index');
        Route::get('/{post}', [BlogController::class, 'show'])->name('blog.show');
    });

    Route::prefix('visa')->group(function () {
        Route::get('/', [VisaServiceController::class, 'index'])->name('visa.index');
        Route::get('/{visaService}', [VisaServiceController::class, 'show'])->name('visa.show');
    });
    Route::get('/shop', function () {
        return view('pages.shop.index');
    })->name('shop.index');
    Route::get('/shop/{product}', function () {
        return view('pages.shop.show');
    })->name('shop.show');
    Route::get('/gallery', function () {
        return view('pages.gallery.index');
    })->name('gallery.index');
    Route::get('/testimonials', function () {
        return view('pages.testimonials.index');
    })->name('testimonials.index');
    Route::get('/kontak', function (string $locale) {
        $alternateUrls = collect(['id', 'ms', 'en'])
            ->mapWithKeys(fn (string $supportedLocale): array => [
                $supportedLocale => route('contact.index', ['locale' => $supportedLocale]),
            ])
            ->all();

        return view('pages.contact.index', [
            'alternateUrls' => $alternateUrls,
            'canonicalUrl' => $alternateUrls[$locale],
        ]);
    })->name('contact.index');
});

Route::view('/design-system', 'pages.design-system')->name('design-system.index');
