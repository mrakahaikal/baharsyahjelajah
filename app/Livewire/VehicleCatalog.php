<?php

namespace App\Livewire;

use App\Enums\VehicleCategory;
use App\Models\Vehicle;
use App\Models\VehicleRentalArea;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class VehicleCatalog extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $area = '';

    #[Url(as: 'pax', except: '')]
    public string $capacity = '';

    #[Url(except: '')]
    public string $category = '';

    #[Url(except: 'featured')]
    public string $sort = 'featured';

    public function updated(string $property): void
    {
        if (in_array($property, ['area', 'capacity', 'category', 'sort'], true)) {
            $this->resetPage();
            unset($this->selectedArea, $this->vehicles);
        }
    }

    public function resetFilters(): void
    {
        $this->reset('capacity', 'category');
        $this->sort = 'featured';
        $this->resetPage();
        unset($this->vehicles);
    }

    /** @return Collection<int, VehicleRentalArea> */
    #[Computed]
    public function areas(): Collection
    {
        return VehicleRentalArea::query()->active()->orderBy('sort_order')->get();
    }

    #[Computed]
    public function selectedArea(): ?VehicleRentalArea
    {
        return $this->areas->firstWhere('slug', $this->area);
    }

    #[Computed]
    public function vehicles(): LengthAwarePaginator
    {
        $area = $this->selectedArea;

        return Vehicle::query()
            ->active()
            ->with('media')
            ->when(
                $area,
                fn (Builder $query) => $query
                    ->whereHas('rentalRates', fn (Builder $query) => $query->active()->effectiveOn(today())->forArea($area))
                    ->with(['rentalRates' => fn ($query) => $query->active()->effectiveOn(today())->forArea($area)->latest('valid_from')]),
                fn (Builder $query) => $query->whereRaw('1 = 0'),
            )
            ->when(ctype_digit($this->capacity) && (int) $this->capacity > 0, fn (Builder $query) => $query->byCapacity((int) $this->capacity))
            ->when(in_array($this->category, array_column(VehicleCategory::cases(), 'value'), true), fn (Builder $query) => $query->where('category', $this->category))
            ->when(
                $this->sort === 'capacity',
                fn (Builder $query) => $query->orderBy('capacity_pax'),
                fn (Builder $query) => $query->orderByDesc('is_featured')->orderBy('sort_order'),
            )
            ->paginate(9);
    }

    public function render(): View
    {
        return view('livewire.vehicle-catalog', [
            'vehicles' => $this->vehicles,
        ]);
    }
}
