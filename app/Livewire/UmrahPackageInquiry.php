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

    public string $pax = '2';

    public function mount(UmrahPackage $package): void
    {
        $this->packageId = $package->id;
        $this->pax = (string) max(1, min(1000, app(GeneralSettings::class)->default_pax));
        $this->selectedPackagePriceId = $package->prices()->orderBy('price_idr')->value('id');
        $this->selectedDepartureId = $package->upcomingDepartures()
            ->whereNotIn('status', ['full', 'closed'])
            ->value('id');
    }

    #[Computed]
    public function package(): UmrahPackage
    {
        return UmrahPackage::query()
            ->active()
            ->with([
                'prices',
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
        return $this->package->getPriceForDeparture(
            $this->selectedDeparture,
            $this->selectedPackagePrice,
        );
    }

    #[Computed]
    public function maximumPax(): int
    {
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
