<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Models\TourPackage;
use App\Models\VehicleGallery;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PageController extends Controller
{
    public function visa(): View
    {
        return view('pages.visa.index');
    }

    public function shop(): View
    {
        return view('pages.shop.index');
    }

    public function shopShow(string $locale, string $product): View
    {
        return view('pages.shop.show', compact('product'));
    }

    public function gallery(string $locale): View
    {
        $supportedLocales = ['id', 'ms', 'en'];
        $alternateUrls = collect($supportedLocales)
            ->mapWithKeys(fn (string $supportedLocale): array => [
                $supportedLocale => route('gallery.index', ['locale' => $supportedLocale]),
            ])
            ->all();

        $tourGalleries = Media::query()
            ->where('model_type', TourPackage::class)
            ->whereIn('collection_name', [TourPackage::MEDIA_COLLECTION_GALLERY, TourPackage::MEDIA_COLLECTION_COVER])
            ->orderBy('order_column')
            ->limit(24)
            ->get();

        $vehicleGalleries = VehicleGallery::query()
            ->with('vehicle')
            ->orderBy('sort_order')
            ->limit(12)
            ->get();

        return view('pages.gallery.index', [
            'tourGalleries' => $tourGalleries,
            'vehicleGalleries' => $vehicleGalleries,
            'alternateUrls' => $alternateUrls,
            'canonicalUrl' => $alternateUrls[$locale] ?? route('gallery.index', ['locale' => $locale]),
            'locale' => $locale,
        ]);
    }

    public function testimonials(string $locale): View
    {
        $supportedLocales = ['id', 'ms', 'en'];
        $alternateUrls = collect($supportedLocales)
            ->mapWithKeys(fn (string $supportedLocale): array => [
                $supportedLocale => route('testimonials.index', ['locale' => $supportedLocale]),
            ])
            ->all();

        $testimonials = Testimonial::query()
            ->active()
            ->with('product')
            ->latest()
            ->paginate(12);

        return view('pages.testimonials.index', [
            'testimonials' => $testimonials,
            'alternateUrls' => $alternateUrls,
            'canonicalUrl' => $alternateUrls[$locale] ?? route('testimonials.index', ['locale' => $locale]),
            'locale' => $locale,
        ]);
    }
}
