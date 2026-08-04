<?php

namespace App\View\Components;

use App\Models\Country;
use App\Models\Destination;
use App\Models\Tour;
use App\Models\TourCategory;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;

class SharedHeader extends Component
{
    /** @var Collection<int, TourCategory> */
    public Collection $menuCategories;

    /** @var Collection<int, Tour> */
    public Collection $menuFeaturedTours;

    /** @var Collection<int, Country> */
    public Collection $menuCountries;

    /** @var Collection<int, Destination> */
    public Collection $menuDestinations;

    /** @param array<string, string> $localeUrls */
    public function __construct(public array $localeUrls = [])
    {
        $this->menuCategories = TourCategory::query()
            ->ordered()
            ->withCount('activeTours')
            ->take(4)
            ->get();

        $this->menuFeaturedTours = Tour::query()
            ->active()
            ->featured()
            ->with([
                'category',
                'packages' => fn ($query) => $query
                    ->oldest('id')
                    ->with(['media', 'tiers.priceTiers']),
            ])
            ->withCount('packages')
            ->take(2)
            ->get();

        $this->menuCountries = Country::query()
            ->active()
            ->with('media')
            ->withCount([
                'tourPackages as tour_packages_count' => fn (Builder $query): Builder => $query
                    ->whereHas('tour', fn (Builder $q) => $q->active()),
                'umrahPackages as umrah_packages_count' => fn (Builder $query): Builder => $query->active(),
                'visaServices as visa_services_count' => fn (Builder $query): Builder => $query->publiclyAvailable(),
            ])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->take(4)
            ->get();

        $this->menuDestinations = Destination::query()
            ->active()
            ->whereHas('itineraries.tourPackage.tour', fn (Builder $query): Builder => $query->active())
            ->with('media')
            ->inRandomOrder()
            ->take(3)
            ->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.shared.header.index', [
            'menuCategories' => $this->menuCategories,
            'menuFeaturedTours' => $this->menuFeaturedTours,
            'menuCountries' => $this->menuCountries,
            'menuDestinations' => $this->menuDestinations,
        ]);
    }
}
