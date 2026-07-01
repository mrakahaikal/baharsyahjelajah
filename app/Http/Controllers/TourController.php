<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\TourCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TourController extends Controller
{
    public function index(Request $request): View
    {
        $categories = TourCategory::query()
            ->ordered()
            ->withCount('activeTours')
            ->get();

        $tours = Tour::query()
            ->active()
            ->with('category')
            ->when($request->string('category')->isNotEmpty(), function ($query) use ($request): void {
                $categorySlug = $request->string('category')->toString();

                $query->whereHas('category', function ($categoryQuery) use ($categorySlug): void {
                    $categoryQuery
                        ->where('slug->'.app()->getLocale(), $categorySlug)
                        ->orWhere('slug->id', $categorySlug);
                });
            })
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('pages.tour.index', compact('categories', 'tours'));
    }

    public function show(string $locale, string $tour): View
    {
        /** @var Tour $tour */
        $tour = $this->findByTranslatedSlug(Tour::class, $tour);

        $tour->load([
            'category',
            'itineraries',
            'includes',
            'galleries',
            'testimonials',
        ]);

        $relatedTours = Tour::query()
            ->active()
            ->with('category')
            ->whereKeyNot($tour->id)
            ->when($tour->category_id, fn ($query) => $query->where('category_id', $tour->category_id))
            ->latest()
            ->limit(3)
            ->get();

        return view('pages.tour.show', compact('tour', 'relatedTours'));
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function findByTranslatedSlug(string $model, string $slug): Model
    {
        return $model::query()
            ->where('slug->'.app()->getLocale(), $slug)
            ->orWhere('slug->id', $slug)
            ->firstOrFail();
    }
}
