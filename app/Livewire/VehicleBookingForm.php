<?php

namespace App\Livewire;

use App\Helpers\LocaleHelper;
use App\Models\Vehicle;
use App\Models\VehicleRentalArea;
use App\Models\VehicleRentalRate;
use App\Models\WhatsappTemplate;
use App\Services\CurrencyService;
use App\Settings\GeneralSettings;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class VehicleBookingForm extends Component
{
    #[Locked]
    public int $vehicleId;

    public string $customerName = '';

    public string $whatsappNumber = '';

    public string $email = '';

    public string $area = '';

    public string $pickupDate = '';

    public string $pickupTime = '';

    public string $rentalDays = '1';

    public string $pickupLocation = '';

    public string $destination = '';

    public string $pax = '1';

    public string $notes = '';

    public function mount(Vehicle $vehicle, ?string $initialArea = null, int $initialPax = 1): void
    {
        $this->vehicleId = $vehicle->id;
        $this->area = $this->availableAreas->contains('slug', $initialArea)
            ? (string) $initialArea
            : (string) ($this->availableAreas->first()?->slug ?? '');
        $maximumPax = $vehicle->capacity_pax ?: 100;
        $this->pax = (string) max(1, min($initialPax, $maximumPax));
        $this->rentalDays = (string) ($this->selectedArea?->minimum_rental_days ?? 1);
    }

    public function updatedArea(): void
    {
        $minimumDays = $this->selectedArea?->minimum_rental_days ?? 1;

        if ((int) $this->rentalDays < $minimumDays) {
            $this->rentalDays = (string) $minimumDays;
        }
    }

    #[Computed]
    public function vehicle(): Vehicle
    {
        return Vehicle::query()->active()->with('media')->findOrFail($this->vehicleId);
    }

    /** @return Collection<int, VehicleRentalArea> */
    #[Computed]
    public function availableAreas(): Collection
    {
        return VehicleRentalArea::query()
            ->active()
            ->whereHas('rates', fn (Builder $query) => $query->active()->where('vehicle_id', $this->vehicleId))
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function selectedArea(): ?VehicleRentalArea
    {
        return $this->availableAreas->firstWhere('slug', $this->area);
    }

    #[Computed]
    public function selectedRate(): ?VehicleRentalRate
    {
        $date = $this->validPickupDate() ?? today();

        return $this->vehicle->rentalRates()
            ->active()
            ->effectiveOn($date)
            ->when($this->selectedArea, fn ($query) => $query->forArea($this->selectedArea))
            ->latest('valid_from')
            ->first();
    }

    #[Computed]
    public function rentalEndDate(): ?Carbon
    {
        $pickupDate = $this->validPickupDate();

        return $pickupDate?->copy()->addDays(max(1, (int) $this->rentalDays) - 1);
    }

    #[Computed]
    public function formattedEstimate(): ?string
    {
        if (! $this->selectedRate) {
            return null;
        }

        return app(CurrencyService::class)->convert(
            $this->selectedRate->price_per_day_idr * max(1, (int) $this->rentalDays),
            LocaleHelper::currency(),
        );
    }

    public function submit(): void
    {
        $this->validate();

        if (! $this->selectedRate) {
            $this->addError('area', __('transport.booking.rate_unavailable'));

            return;
        }

        $recipient = preg_replace('/\D+/', '', app(GeneralSettings::class)->whatsapp_number);

        if (blank($recipient)) {
            $this->addError('service', __('transport.booking.service_unavailable'));

            return;
        }

        $estimate = $this->formattedEstimate ?? __('transport.booking.on_request');
        $intro = WhatsappTemplate::render('vehicle', app()->getLocale(), [
            '{product_name}' => $this->vehicle->name,
            '{capacity}' => $this->vehicle->capacity_display,
            '{date}' => $this->pickupDate,
            '{price}' => $estimate,
        ]);
        $details = [
            __('transport.booking.whatsapp_heading'),
            __('transport.booking.whatsapp.vehicle', ['value' => $this->vehicle->name]),
            __('transport.booking.whatsapp.area', ['value' => $this->selectedArea?->name]),
            __('transport.booking.whatsapp.name', ['value' => trim($this->customerName)]),
            __('transport.booking.whatsapp.phone', ['value' => trim($this->whatsappNumber)]),
            __('transport.booking.whatsapp.email', ['value' => trim($this->email) ?: '-']),
            __('transport.booking.whatsapp.pickup', ['value' => $this->pickupDate.' '.$this->pickupTime.' - '.trim($this->pickupLocation)]),
            __('transport.booking.whatsapp.duration', ['value' => (int) $this->rentalDays]),
            __('transport.booking.whatsapp.return', ['value' => $this->rentalEndDate?->translatedFormat('d M Y')]),
            __('transport.booking.whatsapp.route', ['value' => trim($this->destination)]),
            __('transport.booking.whatsapp.pax', ['value' => (int) $this->pax]),
            __('transport.booking.whatsapp.daily_rate', ['value' => $this->selectedRate->formatted_price]),
            __('transport.booking.whatsapp.price', ['value' => $estimate]),
            __('transport.booking.whatsapp.notes', ['value' => trim($this->notes) ?: '-']),
        ];
        $message = filled($intro)
            ? trim($intro)."\n\n".implode("\n", $details)
            : implode("\n", $details);

        $this->redirect('https://wa.me/'.$recipient.'?text='.rawurlencode($message));
    }

    public function render(): View
    {
        return view('livewire.vehicle-booking-form');
    }

    /** @return array<string, mixed> */
    protected function rules(): array
    {
        $minimumDays = $this->selectedArea?->minimum_rental_days ?? 1;
        $paxRules = ['required', 'integer', 'min:1'];

        if ($this->vehicle->capacity_pax) {
            $paxRules[] = 'max:'.$this->vehicle->capacity_pax;
        }

        return [
            'customerName' => ['required', 'string', 'max:100'],
            'whatsappNumber' => ['required', 'string', 'min:8', 'max:30', 'regex:/^[0-9+()\-\s]+$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'area' => [
                'required',
                Rule::exists('vehicle_rental_areas', 'slug')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'pickupDate' => ['required', 'date', 'after_or_equal:today'],
            'pickupTime' => ['required', 'date_format:H:i'],
            'rentalDays' => ['required', 'integer', 'min:'.$minimumDays, 'max:365'],
            'pickupLocation' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'pax' => $paxRules,
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function validPickupDate(): ?Carbon
    {
        if (blank($this->pickupDate)) {
            return null;
        }

        try {
            return Carbon::parse($this->pickupDate)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
