<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TourController;
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

    return redirect()->route('contact.index', ['locale' => $detected]);
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

// Route for Currency Switcher
Route::get('/set-currency/{currency}', function (string $currency) {
    if (in_array($currency, ['IDR', 'MYR', 'SGD'])) {
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
        Route::get('/', function () {
            return view('pages.transport.index');
        })->name('transport.index');
        Route::get('/{vehicle}', function () {
            return view('pages.transport.show');
        })->name('transport.show');
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
        Route::get('/', function () {
            return view('pages.umroh.index');
        })->name('umroh.index');
        Route::get('/{umrah}', function () {
            return view('pages.umroh.show');
        })->name('umroh.show');
    });

    Route::prefix('blog')->group(function () {
        Route::get('/', [BlogController::class, 'index'])->name('blog.index');
        Route::get('/{post}', [BlogController::class, 'show'])->name('blog.show');
    });

    Route::get('/visa', function () {
        return view('pages.visa.index');
    })->name('visa.index');
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
    Route::get('/kontak', function () {
        return view('pages.contact.index');
    })->name('contact.index');
});

Route::view('/design-system', 'pages.design-system')->name('design-system.index');
