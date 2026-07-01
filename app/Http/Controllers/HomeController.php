<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Faq;
use App\Models\Post;
use App\Models\Testimonial;
use App\Models\Tour;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featuredTours = Tour::query()
            ->where('is_featured', true)
            ->where('is_active', true)
            ->with('category')
            ->latest()
            ->limit(3)
            ->get();

        $testimonials = Testimonial::query()
            ->latest()
            ->limit(3)
            ->get();

        $latestPosts = Post::query()
            ->where('status', 'published')
            ->with('category')
            ->latest()
            ->limit(3)
            ->get();

        $banners = Banner::active()->get();

        $faqs = Faq::active()->ordered()->limit(8)->get();

        return view('pages.home', compact(
            'featuredTours',
            'testimonials',
            'latestPosts',
            'banners',
            'faqs',
        ));
    }
}
