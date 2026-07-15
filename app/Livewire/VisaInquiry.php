<?php

namespace App\Livewire;

use App\Models\VisaService;
use App\Models\WhatsappTemplate;
use App\Settings\GeneralSettings;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class VisaInquiry extends Component
{
    #[Locked]
    public int $visaServiceId;

    public string $customerName = '';

    public string $applicants = '1';

    public string $departureDate = '';

    public string $notes = '';

    public function mount(VisaService $service): void
    {
        $this->visaServiceId = $service->id;
    }

    #[Computed]
    public function service(): VisaService
    {
        return VisaService::query()
            ->publiclyAvailable()
            ->with(['country', 'media'])
            ->findOrFail($this->visaServiceId);
    }

    public function submit(): void
    {
        $this->validate();

        $recipient = preg_replace('/\D+/', '', app(GeneralSettings::class)->whatsapp_number);

        if (blank($recipient)) {
            $this->addError('service', __('visa.inquiry.service_unavailable'));

            return;
        }

        $departure = filled($this->departureDate)
            ? $this->departureDate
            : __('visa.inquiry.not_determined');
        $price = $this->service->formatted_price ?? __('visa.price_on_request');
        $intro = WhatsappTemplate::render('visa', app()->getLocale(), [
            '{product_name}' => $this->service->name,
            '{country}' => $this->service->country->name,
            '{visa_type}' => $this->service->visa_type,
            '{applicant_name}' => trim($this->customerName),
            '{applicants}' => (string) ((int) $this->applicants),
            '{departure_date}' => $departure,
            '{price}' => $price,
            '{notes}' => filled(trim($this->notes)) ? trim($this->notes) : '-',
        ]);
        $intro = filled($intro)
            ? trim($intro)
            : __('visa.inquiry.whatsapp_intro', ['service' => $this->service->name]);
        $details = [
            __('visa.inquiry.whatsapp_heading'),
            __('visa.inquiry.whatsapp_service', ['value' => $this->service->name]),
            __('visa.inquiry.whatsapp_country', ['value' => $this->service->country->name]),
            __('visa.inquiry.whatsapp_type', ['value' => $this->service->visa_type]),
            __('visa.inquiry.whatsapp_name', ['value' => trim($this->customerName)]),
            __('visa.inquiry.whatsapp_applicants', ['value' => (int) $this->applicants]),
            __('visa.inquiry.whatsapp_departure', ['value' => $departure]),
            __('visa.inquiry.whatsapp_price', ['value' => $price]),
            __('visa.inquiry.whatsapp_notes', ['value' => filled(trim($this->notes)) ? trim($this->notes) : '-']),
        ];

        $this->redirect('https://wa.me/'.$recipient.'?text='.rawurlencode($intro."\n\n".implode("\n", $details)));
    }

    public function render(): View
    {
        return view('livewire.visa-inquiry');
    }

    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'customerName' => ['required', 'string', 'max:100'],
            'applicants' => ['required', 'integer', 'min:1', 'max:100'],
            'departureDate' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
