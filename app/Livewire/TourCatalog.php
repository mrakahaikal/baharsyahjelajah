<?php

namespace App\Livewire;

use App\Enums\TourType;
use App\Models\Country;
use App\Models\Destination;
use App\Models\Tour;
use App\Models\TourCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TourCatalog extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $destination = '';

    #[Url(history: true, except: '')]
    public string $category = '';

    #[Url(history: true, except: '')]
    public string $type = '';

    #[Url(as: 'place', history: true, except: '')]
    public string $destinationSlug = '';

    #[Url(as: 'country', history: true, except: '')]
    public string $country = '';

    #[Locked]
    public string $heroImageUrl = '';

    #[Locked]
    public string $heroImageAlt = '';

    public function mount(string $heroImageUrl, string $heroImageAlt): void
    {
        $this->heroImageUrl = $heroImageUrl;
        $this->heroImageAlt = $heroImageAlt;

        if ($this->type !== '' && ! $this->isValidTourType()) {
            $this->type = '';
        }
    }

    public function updatedDestination(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        if ($this->type !== '' && ! $this->isValidTourType()) {
            $this->type = '';
        }

        $this->resetPage();
    }

    public function updatedDestinationSlug(): void
    {
        $this->destinationSlug = trim($this->destinationSlug);
        $this->resetPage();
    }

    public function updatedCountry(): void
    {
        $this->country = trim($this->country);
        $this->resetPage();
    }

    public function search(): void
    {
        $this->destination = trim($this->destination);
        $this->resetPage();
    }

    public function clearFilter(string $filter): void
    {
        if (! in_array($filter, ['destination', 'category', 'type', 'destinationSlug', 'country'], true)) {
            return;
        }

        $this->{$filter} = '';
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['destination', 'category', 'type', 'destinationSlug', 'country']);
        $this->resetPage();
    }

    #[Computed]
    public function activeFilterCount(): int
    {
        return collect([
            $this->destination,
            $this->category,
            $this->isValidTourType() ? $this->type : '',
            $this->destinationSlug,
            $this->country,
        ])
            ->filter(fn (string $value): bool => filled($value))
            ->count();
    }

    public function render(): View
    {
        $locale = app()->getLocale();
        $categories = TourCategory::query()
            ->ordered()
            ->withCount('activeTours')
            ->get();

        $destinations = Destination::query()
            ->select(['id', 'name', 'slug'])
            ->whereHas(
                'itineraries.tourPackage.tour',
                fn (Builder $query): Builder => $query->active(),
            )
            ->get()
            ->sortBy(fn (Destination $destination): string => $destination->name, SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $countries = Country::query()
            ->active()
            ->where(function (Builder $query): void {
                $query->whereHas('tours', fn (Builder $q) => $q->active())
                    ->orWhereHas('tourPackages.tour', fn (Builder $q) => $q->active());
            })
            ->get()
            ->sortBy(fn (Country $c) => $c->name, SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $tours = Tour::query()
            ->active()
            ->with([
                'category',
                'packages' => fn ($query) => $query
                    ->oldest('id')
                    ->with(['media', 'tiers.priceTiers']),
            ])
            ->withCount('packages')
            ->when(filled(trim($this->destination)), function (Builder $query) use ($locale): void {
                $keyword = trim($this->destination);

                $query->where(function (Builder $keywordQuery) use ($keyword, $locale): void {
                    $keywordQuery
                        ->where("name->{$locale}", 'like', "%{$keyword}%")
                        ->orWhere('name->id', 'like', "%{$keyword}%")
                        ->orWhere("short_description->{$locale}", 'like', "%{$keyword}%")
                        ->orWhere('short_description->id', 'like', "%{$keyword}%")
                        ->orWhere("description->{$locale}", 'like', "%{$keyword}%")
                        ->orWhere('description->id', 'like', "%{$keyword}%")
                        ->orWhereHas('packages', function (Builder $packageQuery) use ($keyword, $locale): void {
                            $packageQuery->where(function (Builder $packageNameQuery) use ($keyword, $locale): void {
                                $packageNameQuery
                                    ->where("name->{$locale}", 'like', "%{$keyword}%")
                                    ->orWhere('name->id', 'like', "%{$keyword}%");
                            });
                        })
                        ->orWhereHas('packages.itineraries.destinations', function (Builder $destinationQuery) use ($keyword, $locale): void {
                            $destinationQuery->where(function (Builder $destinationNameQuery) use ($keyword, $locale): void {
                                $destinationNameQuery
                                    ->where("name->{$locale}", 'like', "%{$keyword}%")
                                    ->orWhere('name->id', 'like', "%{$keyword}%")
                                    ->orWhere('location', 'like', "%{$keyword}%");
                            });
                        });
                });
            })
            ->when($this->category !== '', function (Builder $query) use ($locale): void {
                $query->whereHas('category', function (Builder $categoryQuery) use ($locale): void {
                    $categoryQuery->where(function (Builder $categorySlugQuery) use ($locale): void {
                        $categorySlugQuery
                            ->where("slug->{$locale}", $this->category)
                            ->orWhere('slug->id', $this->category);
                    });
                });
            })
            ->when($this->isValidTourType(), fn (Builder $query): Builder => $query->where('tour_type', $this->type))
            ->when(
                filled($this->destinationSlug),
                fn (Builder $query): Builder => $query->whereHas(
                    'packages.itineraries.destinations',
                    fn (Builder $destinationQuery): Builder => $destinationQuery->where('slug', $this->destinationSlug),
                ),
            )
            ->when(filled($this->country), function (Builder $query): void {
                $query->where(function (Builder $q): void {
                    $q->whereHas('countries', fn (Builder $cq) => $cq->where('slug', $this->country))
                        ->orWhereHas('packages.countries', fn (Builder $cq) => $cq->where('slug', $this->country));
                });
            })
            ->orderByDesc('is_featured')
            ->latest()
            ->paginate(6);

        $tourTypes = TourType::cases();

        return view('livewire.tour-catalog', compact('categories', 'destinations', 'countries', 'locale', 'tours', 'tourTypes'));
    }

    private function isValidTourType(): bool
    {
        return in_array($this->type, array_column(TourType::cases(), 'value'), true);
    }
}
