<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Models\TourGallery;
use App\Models\VehicleGallery;
use Illuminate\View\View;

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
        $tourGalleries = TourGallery::query()
            ->with('tour')
            ->orderBy('sort_order')
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
