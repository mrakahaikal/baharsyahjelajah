<?php

namespace App\Livewire;

use App\Models\Country;
use App\Models\VisaService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class VisaCatalog extends Component
{
    use WithPagination;

    #[Url(history: true, except: '')]
    public string $country = '';

    public function mount(): void
    {
        if (filled($this->country) && ! $this->countries->contains('slug', $this->country)) {
            $this->country = '';
        }
    }

    public function updatedCountry(): void
    {
        $this->resetPage();
    }

    public function selectCountry(string $country): void
    {
        $this->country = $this->countries->contains('slug', $country) ? $country : '';
        $this->resetPage();
    }

    #[Computed]
    public function countries(): Collection
    {
        return Country::query()
            ->active()
            ->whereHas('visaServices', fn (Builder $query): Builder => $query->active())
            ->with('media')
            ->withCount(['visaServices as public_visa_services_count' => fn (Builder $query): Builder => $query->active()])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    #[Computed]
    public function services(): LengthAwarePaginator
    {
        return VisaService::query()
            ->publiclyAvailable()
            ->with(['country.media', 'media'])
            ->when(
                filled($this->country),
                fn (Builder $query): Builder => $query->whereHas(
                    'country',
                    fn (Builder $query): Builder => $query->where('slug', $this->country),
                ),
            )
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(9);
    }

    public function render(): View
    {
        return view('livewire.visa-catalog', [
            'countries' => $this->countries,
            'services' => $this->services,
        ]);
    }
}
