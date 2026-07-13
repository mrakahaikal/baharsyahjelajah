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

    public function gallery(): View
    {
        $tourGalleries = Media::query()
            ->where('model_type', TourPackage::class)
            ->where('collection_name', TourPackage::MEDIA_COLLECTION_GALLERY)
            ->orderBy('order_column')
            ->limit(18)
            ->get();

        $vehicleGalleries = VehicleGallery::query()
            ->with('vehicle')
            ->orderBy('sort_order')
            ->limit(12)
            ->get();

        return view('pages.gallery.index', compact('tourGalleries', 'vehicleGalleries'));
    }

    public function testimonials(): View
    {
        $testimonials = Testimonial::query()
            ->active()
            ->with('product')
            ->latest()
            ->paginate(12);

        return view('pages.testimonials.index', compact('testimonials'));
    }
}
