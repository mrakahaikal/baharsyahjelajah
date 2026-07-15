<?php

namespace App\Livewire;

use App\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class VehicleCatalog extends Component
{
    use WithPagination;

    #[Url(as: 'pax', except: '')]
    public string $capacity = '';

    #[Url(except: '')]
    public string $transmission = '';

    #[Url(except: '')]
    public string $rate = '';

    #[Url(except: 'featured')]
    public string $sort = 'featured';

    public function updated(string $property): void
    {
        if (in_array($property, ['capacity', 'transmission', 'rate', 'sort'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('capacity', 'transmission', 'rate');
        $this->sort = 'featured';
        $this->resetPage();
    }

    #[Computed]
    public function vehicles(): LengthAwarePaginator
    {
        return Vehicle::query()
            ->active()
            ->with('media')
            ->when(ctype_digit($this->capacity) && (int) $this->capacity > 0, fn ($query) => $query->byCapacity((int) $this->capacity))
            ->when(in_array($this->transmission, ['automatic', 'manual'], true), fn ($query) => $query->where('transmission', $this->transmission))
            ->when($this->rate === 'daily', fn ($query) => $query->whereNotNull('price_per_day_idr'))
            ->when($this->rate === 'trip', fn ($query) => $query->whereNotNull('price_per_trip_idr'))
            ->when(
                $this->sort === 'capacity',
                fn ($query) => $query->orderBy('capacity_pax'),
                fn ($query) => $query->orderByDesc('is_featured')->latest(),
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
