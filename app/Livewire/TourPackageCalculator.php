<?php

namespace App\Livewire;

use App\Models\PackageTier;
use App\Models\TourPackage;
use App\Models\TourPriceTier;
use App\Settings\GeneralSettings;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TourPackageCalculator extends Component
{
    #[Locked]
    public int $packageId;

    public string $pax = '2';

    public ?int $selectedTierId = null;

    public function mount(TourPackage $package): void
    {
        $this->packageId = $package->id;
        $this->pax = (string) max(1, min(1000, app(GeneralSettings::class)->default_pax));
        $this->selectedTierId = $package->tiers()->oldest('id')->value('id');
    }

    #[Computed]
    public function package(): TourPackage
    {
        return TourPackage::query()
            ->with(['tiers.priceTiers', 'tour'])
            ->findOrFail($this->packageId);
    }

    #[Computed]
    public function selectedTier(): ?PackageTier
    {
        return $this->package->tiers->firstWhere('id', $this->selectedTierId);
    }

    #[Computed]
    public function selectedPriceTier(): ?TourPriceTier
    {
        if (! $this->hasValidPax() || ! $this->selectedTier) {
            return null;
        }

        return $this->selectedTier->priceTierForPax((int) $this->pax);
    }

    #[Computed]
    public function formattedTotal(): ?string
    {
        return $this->selectedPriceTier?->formattedTotalForPax((int) $this->pax);
    }

    #[Computed]
    public function bookingUrl(): ?string
    {
        if (! $this->hasValidPax()) {
            return null;
        }

        return route('tour.package.booking', array_filter([
            'locale' => app()->getLocale(),
            'tour' => $this->package->tour->localizedSlug(),
            'package' => $this->package->localizedSlug(),
            'tier' => $this->selectedTier?->id,
            'pax' => (int) $this->pax,
        ], fn (mixed $value): bool => $value !== null));
    }

    public function render(): View
    {
        return view('livewire.tour-package-calculator');
    }

    private function hasValidPax(): bool
    {
        return ctype_digit($this->pax)
            && (int) $this->pax >= 1
            && (int) $this->pax <= 1000;
    }
}
