<?php

namespace App\Livewire;

use App\Models\PackageTier;
use App\Models\TourPackage;
use App\Models\TourPriceTier;
use App\Models\WhatsappTemplate;
use App\Settings\GeneralSettings;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TourBookingForm extends Component
{
    #[Locked]
    public int $packageId;

    public string $customerName = '';

    public string $whatsappNumber = '';

    public string $email = '';

    public string $departureDate = '';

    public string $pax = '2';

    public ?int $selectedTierId = null;

    public string $notes = '';

    public function mount(TourPackage $package, ?int $initialTierId = null, ?int $initialPax = null): void
    {
        $package->loadMissing('tiers.priceTiers');

        $this->packageId = $package->id;
        $this->selectedTierId = $package->tiers->firstWhere('id', $initialTierId)?->id
            ?? $package->tiers->first()?->id;
        $this->pax = (string) ($initialPax ?? app(GeneralSettings::class)->default_pax);
    }

    #[Computed]
    public function package(): TourPackage
    {
        return TourPackage::query()
            ->with(['media', 'tiers.priceTiers', 'tour'])
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

    public function submit(): void
    {
        $this->validate();

        $settings = app(GeneralSettings::class);
        $recipient = preg_replace('/\D+/', '', $settings->whatsapp_number);

        if (blank($recipient)) {
            $this->addError('service', __('frontend.tour.booking.form.service_unavailable'));

            return;
        }

        $pricePerPerson = $this->selectedPriceTier?->formatted_price
            ?? __('frontend.tour.booking.summary.on_request');
        $estimatedTotal = $this->formattedTotal
            ?? __('frontend.tour.booking.summary.on_request');
        $intro = WhatsappTemplate::render('tour', app()->getLocale(), [
            '{product_name}' => $this->package->name,
            '{duration}' => $this->package->duration_label,
            '{pax}' => (string) ((int) $this->pax),
            '{price}' => $pricePerPerson,
        ]);
        $intro = filled($intro) ? trim($intro) : __('frontend.tour.booking.whatsapp.intro');

        $details = [
            __('frontend.tour.booking.whatsapp.heading'),
            __('frontend.tour.booking.whatsapp.tour', ['value' => $this->package->tour->name]),
            __('frontend.tour.booking.whatsapp.package', ['value' => $this->package->name]),
            __('frontend.tour.booking.whatsapp.tier', ['value' => $this->selectedTier?->name ?? '-']),
            __('frontend.tour.booking.whatsapp.name', ['value' => trim($this->customerName)]),
            __('frontend.tour.booking.whatsapp.phone', ['value' => trim($this->whatsappNumber)]),
            __('frontend.tour.booking.whatsapp.email', ['value' => filled(trim($this->email)) ? trim($this->email) : '-']),
            __('frontend.tour.booking.whatsapp.date', ['value' => $this->departureDate]),
            __('frontend.tour.booking.whatsapp.pax', ['value' => (int) $this->pax]),
            __('frontend.tour.booking.whatsapp.per_person', ['value' => $pricePerPerson]),
            __('frontend.tour.booking.whatsapp.total', ['value' => $estimatedTotal]),
            __('frontend.tour.booking.whatsapp.notes', ['value' => filled(trim($this->notes)) ? trim($this->notes) : '-']),
        ];

        $message = $intro."\n\n".implode("\n", $details);

        $this->redirect('https://wa.me/'.$recipient.'?text='.rawurlencode($message));
    }

    public function render(): View
    {
        return view('livewire.tour-booking-form');
    }

    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'customerName' => ['required', 'string', 'max:100'],
            'whatsappNumber' => ['required', 'string', 'min:8', 'max:30', 'regex:/^[0-9+()\-\s]+$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'departureDate' => ['required', 'date', 'after_or_equal:today'],
            'pax' => ['required', 'integer', 'min:1', 'max:1000'],
            'selectedTierId' => [
                Rule::requiredIf(fn (): bool => $this->package->tiers->isNotEmpty()),
                'nullable',
                'integer',
                Rule::exists(PackageTier::class, 'id')
                    ->where('tour_package_id', $this->packageId),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function hasValidPax(): bool
    {
        return ctype_digit($this->pax)
            && (int) $this->pax >= 1
            && (int) $this->pax <= 1000;
    }
}
