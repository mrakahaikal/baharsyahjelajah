<?php

namespace App\View\Components;

use App\Models\Tour;
use App\Models\TourCategory;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;

class SharedHeader extends Component
{
    /** @var Collection<int, TourCategory> */
    public Collection $menuCategories;

    /** @var Collection<int, Tour> */
    public Collection $menuFeaturedTours;

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
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.shared.header.index');
    }
}
