<?php

namespace App\Livewire;

use App\Helpers\LocaleHelper;
use App\Models\Vehicle;
use App\Models\WhatsappTemplate;
use App\Services\CurrencyService;
use App\Settings\GeneralSettings;
use Carbon\Carbon;
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

    public string $rate = '';

    public string $pickupDate = '';

    public string $pickupTime = '';

    public string $returnDate = '';

    public string $pickupLocation = '';

    public string $destination = '';

    public string $pax = '1';

    public string $notes = '';

    public function mount(Vehicle $vehicle, ?string $initialRate = null, int $initialPax = 1): void
    {
        $this->vehicleId = $vehicle->id;
        $this->rate = in_array($initialRate, $this->availableRates($vehicle), true)
            ? $initialRate
            : ($this->availableRates($vehicle)[0] ?? 'daily');
        $this->pax = (string) max(1, min($initialPax, $vehicle->capacity_pax));
    }

    #[Computed]
    public function vehicle(): Vehicle
    {
        return Vehicle::query()->active()->with('media')->findOrFail($this->vehicleId);
    }

    #[Computed]
    public function rentalDays(): int
    {
        if ($this->rate !== 'daily' || blank($this->pickupDate) || blank($this->returnDate)) {
            return 1;
        }

        try {
            return max(1, (int) Carbon::parse($this->pickupDate)->diffInDays(Carbon::parse($this->returnDate), false));
        } catch (\Throwable) {
            return 1;
        }
    }

    #[Computed]
    public function formattedEstimate(): ?string
    {
        $price = match ($this->rate) {
            'daily' => $this->vehicle->price_per_day_idr
                ? $this->vehicle->price_per_day_idr * $this->rentalDays
                : null,
            'trip' => $this->vehicle->price_per_trip_idr,
            default => null,
        };

        return $price
            ? app(CurrencyService::class)->convert($price, LocaleHelper::currency())
            : null;
    }

    public function submit(): void
    {
        $this->validate();

        $recipient = preg_replace('/\D+/', '', app(GeneralSettings::class)->whatsapp_number);

        if (blank($recipient)) {
            $this->addError('service', __('transport.booking.service_unavailable'));

            return;
        }

        $rateLabel = __('transport.booking.'.$this->rate);
        $estimate = $this->formattedEstimate ?? __('transport.booking.on_request');
        $intro = WhatsappTemplate::render('vehicle', app()->getLocale(), [
            '{product_name}' => $this->vehicle->name,
            '{capacity}' => (string) $this->vehicle->capacity_pax,
            '{date}' => $this->pickupDate,
            '{price}' => $estimate,
        ]);
        $details = [
            __('transport.booking.whatsapp_heading'),
            __('transport.booking.whatsapp.vehicle', ['value' => $this->vehicle->name]),
            __('transport.booking.whatsapp.rate', ['value' => $rateLabel]),
            __('transport.booking.whatsapp.name', ['value' => trim($this->customerName)]),
            __('transport.booking.whatsapp.phone', ['value' => trim($this->whatsappNumber)]),
            __('transport.booking.whatsapp.email', ['value' => trim($this->email) ?: '-']),
            __('transport.booking.whatsapp.pickup', ['value' => $this->pickupDate.' '.$this->pickupTime.' - '.trim($this->pickupLocation)]),
            __('transport.booking.whatsapp.return', ['value' => $this->rate === 'daily' ? $this->returnDate : '-']),
            __('transport.booking.whatsapp.route', ['value' => trim($this->destination)]),
            __('transport.booking.whatsapp.pax', ['value' => (int) $this->pax]),
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
        return [
            'customerName' => ['required', 'string', 'max:100'],
            'whatsappNumber' => ['required', 'string', 'min:8', 'max:30', 'regex:/^[0-9+()\-\s]+$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'rate' => ['required', Rule::in($this->availableRates($this->vehicle))],
            'pickupDate' => ['required', 'date', 'after_or_equal:today'],
            'pickupTime' => ['required', 'date_format:H:i'],
            'returnDate' => [Rule::requiredIf($this->rate === 'daily'), 'nullable', 'date', 'after_or_equal:pickupDate'],
            'pickupLocation' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'pax' => ['required', 'integer', 'min:1', 'max:'.$this->vehicle->capacity_pax],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** @return list<string> */
    private function availableRates(Vehicle $vehicle): array
    {
        return collect([
            $vehicle->price_per_day_idr ? 'daily' : null,
            $vehicle->price_per_trip_idr ? 'trip' : null,
        ])->filter()->values()->all();
    }
}
