<?php

namespace App\Livewire;

use App\Helpers\LocaleHelper;
use App\Models\UmrahDeparture;
use App\Models\UmrahPackage;
use App\Models\UmrahPackagePrice;
use App\Services\CurrencyService;
use App\Settings\GeneralSettings;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class UmrahPackageInquiry extends Component
{
    #[Locked]
    public int $packageId;

    public ?int $selectedDepartureId = null;

    public ?int $selectedPackagePriceId = null;

    public ?int $selectedDurationNights = null;

    public ?int $selectedPax = null;

    public string $pax = '2';

    public function mount(UmrahPackage $package): void
    {
        $this->packageId = $package->id;

        if ($package->package_type === 'private') {
            $this->selectedDurationNights = $package->privatePrices()->reorder()->orderBy('duration_nights')->value('duration_nights');
            $this->selectedPax = $package->privatePrices()
                ->reorder()
                ->where('duration_nights', $this->selectedDurationNights)
                ->orderBy('pax')
                ->value('pax');
            $this->pax = (string) ($this->selectedPax ?? 4);
        } else {
            $this->pax = (string) max(1, min(1000, app(GeneralSettings::class)->default_pax));
            $this->selectedPackagePriceId = $package->prices()->orderBy('price_idr')->value('id');
            $this->selectedDepartureId = $package->upcomingDepartures()
                ->whereNotIn('status', ['full', 'closed'])
                ->value('id');
        }
    }

    public function updatedSelectedDurationNights(): void
    {
        $availablePax = $this->package->privatePrices()
            ->where('duration_nights', $this->selectedDurationNights)
            ->pluck('pax');

        if (! $availablePax->contains((int) $this->selectedPax)) {
            $this->selectedPax = $availablePax->first();
        }
        $this->pax = (string) $this->selectedPax;
    }

    public function updatedSelectedPax(): void
    {
        $this->pax = (string) $this->selectedPax;
    }

    #[Computed]
    public function package(): UmrahPackage
    {
        return UmrahPackage::query()
            ->active()
            ->with([
                'prices',
                'privatePrices',
                'upcomingDepartures.prices',
            ])
            ->findOrFail($this->packageId);
    }

    /** @return Collection<int, UmrahDeparture> */
    #[Computed]
    public function availableDepartures(): Collection
    {
        return $this->package->upcomingDepartures
            ->whereNotIn('status', ['full', 'closed'])
            ->values();
    }

    /** @return Collection<int, int> */
    #[Computed]
    public function availableDurations(): Collection
    {
        return $this->package->privatePrices
            ->pluck('duration_nights')
            ->unique()
            ->sort()
            ->values();
    }

    /** @return Collection<int, int> */
    #[Computed]
    public function availablePaxOptions(): Collection
    {
        return $this->package->privatePrices
            ->where('duration_nights', $this->selectedDurationNights)
            ->pluck('pax')
            ->unique()
            ->sort()
            ->values();
    }

    #[Computed]
    public function selectedDeparture(): ?UmrahDeparture
    {
        return $this->availableDepartures->firstWhere('id', $this->selectedDepartureId);
    }

    #[Computed]
    public function selectedPackagePrice(): ?UmrahPackagePrice
    {
        return $this->package->prices->firstWhere('id', $this->selectedPackagePriceId);
    }

    #[Computed]
    public function effectivePriceIdr(): int
    {
        if ($this->package->package_type === 'private') {
            return (int) ($this->package->privatePrices
                ->where('duration_nights', $this->selectedDurationNights)
                ->where('pax', $this->selectedPax)
                ->first()?->price_idr ?? 0);
        }

        return $this->package->getPriceForDeparture(
            $this->selectedDeparture,
            $this->selectedPackagePrice,
        );
    }

    #[Computed]
    public function maximumPax(): int
    {
        if ($this->package->package_type === 'private') {
            return 1000;
        }

        return min(1000, $this->selectedDeparture?->quota_sisa ?? 1000);
    }

    #[Computed]
    public function formattedPrice(): string
    {
        return app(CurrencyService::class)->convert(
            $this->effectivePriceIdr,
            LocaleHelper::currency(),
        );
    }

    #[Computed]
    public function formattedTotal(): string
    {
        return app(CurrencyService::class)->convert(
            $this->effectivePriceIdr * (int) $this->pax,
            LocaleHelper::currency(),
        );
    }

    #[Computed]
    public function whatsappUrl(): ?string
    {
        $phone = app(GeneralSettings::class)->whatsapp_number;

        if (blank($phone) || ! $this->hasValidPax()) {
            return null;
        }

        if ($this->package->package_type === 'private') {
            $message = __('umrah.inquiry.whatsapp_private_message', [
                'package' => $this->package->name,
                'duration' => $this->selectedDurationNights,
                'pax' => (int) $this->pax,
                'price' => $this->formattedPrice,
                'total' => $this->formattedTotal,
            ]);
        } else {
            $message = __('umrah.inquiry.whatsapp_message', [
                'package' => $this->package->name,
                'departure' => $this->selectedDeparture?->departure_date->translatedFormat('d F Y')
                    ?? __('umrah.inquiry.schedule_confirmation'),
                'room' => $this->selectedPackagePrice
                    ? __('umrah.rooms.'.$this->selectedPackagePrice->room_type)
                    : __('umrah.inquiry.room_confirmation'),
                'pax' => (int) $this->pax,
                'price' => $this->formattedPrice,
                'total' => $this->formattedTotal,
            ]);
        }

        return 'https://wa.me/'.$phone.'?text='.urlencode($message);
    }

    public function render(): View
    {
        return view('livewire.umrah-package-inquiry');
    }

    private function hasValidPax(): bool
    {
        return ctype_digit($this->pax)
            && (int) $this->pax >= 1
            && (int) $this->pax <= $this->maximumPax;
    }
}
