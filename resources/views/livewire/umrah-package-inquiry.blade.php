@php($validPax = ctype_digit($pax) && (int) $pax >= 1 && (int) $pax <= $this->maximumPax)

<div class="sticky top-28 overflow-hidden rounded-lg border border-stone-200 bg-white shadow-lg shadow-stone-900/8">
    <div class="border-b border-stone-100 p-5 sm:p-6">
        <p class="text-xs font-bold uppercase text-amber-700">{{ __('umrah.inquiry.eyebrow') }}</p>
        <h2 class="mt-2 text-xl font-extrabold text-neutral-950">{{ __('umrah.inquiry.title') }}</h2>
        <p class="mt-2 text-sm leading-6 text-neutral-600">{{ __('umrah.inquiry.description') }}</p>
    </div>

    <div class="grid gap-5 p-5 sm:p-6">
        <label class="grid gap-2 text-sm font-semibold text-neutral-800">
            {{ __('umrah.inquiry.departure_label') }}
            <select wire:model.live="selectedDepartureId" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2.5 text-sm text-neutral-950 focus:border-amber-600 focus:outline-2 focus:outline-offset-2 focus:outline-amber-600">
                <option value="">{{ __('umrah.inquiry.schedule_confirmation') }}</option>
                @foreach($this->availableDepartures as $departure)
                    <option value="{{ $departure->id }}">
                        {{ $departure->departure_date->translatedFormat('d M Y') }} · {{ __('umrah.inquiry.remaining_quota', ['count' => $departure->quota_sisa]) }}
                    </option>
                @endforeach
            </select>
        </label>

        @if($this->package->prices->isNotEmpty())
            <label class="grid gap-2 text-sm font-semibold text-neutral-800">
                {{ __('umrah.inquiry.room_label') }}
                <select wire:model.live="selectedPackagePriceId" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2.5 text-sm text-neutral-950 focus:border-amber-600 focus:outline-2 focus:outline-offset-2 focus:outline-amber-600">
                    @foreach($this->package->prices as $price)
                        <option value="{{ $price->id }}">{{ __('umrah.rooms.'.$price->room_type) }}</option>
                    @endforeach
                </select>
            </label>
        @endif

        <label class="grid gap-2 text-sm font-semibold text-neutral-800">
            {{ __('umrah.inquiry.pax_label') }}
            <div class="relative">
                <x-lucide-users class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" aria-hidden="true" />
                <input wire:model.live.debounce.400ms="pax" type="number" min="1" max="{{ $this->maximumPax }}" inputmode="numeric" class="w-full rounded-lg border bg-white py-2.5 pl-10 pr-3 text-sm text-neutral-950 focus:outline-2 focus:outline-offset-2 {{ $validPax ? 'border-stone-300 focus:border-amber-600 focus:outline-amber-600' : 'border-red-300 focus:border-red-600 focus:outline-red-600' }}">
            </div>
            @unless($validPax)
                <span class="text-xs font-medium text-red-700">{{ __('umrah.inquiry.pax_error', ['max' => $this->maximumPax]) }}</span>
            @endunless
        </label>
    </div>

    <div class="border-t border-stone-100 bg-stone-50 p-5 sm:p-6" wire:loading.class="opacity-60">
        @if($validPax)
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase text-stone-500">{{ __('umrah.inquiry.per_person') }}</p>
                    <p class="mt-1 text-2xl font-extrabold text-amber-700">{{ $this->formattedPrice }}</p>
                </div>
                <x-lucide-calculator class="h-5 w-5 shrink-0 text-amber-700" aria-hidden="true" />
            </div>
            <div class="mt-4 flex items-end justify-between gap-4 border-t border-stone-200 pt-4">
                <div>
                    <p class="text-xs font-bold uppercase text-stone-500">{{ __('umrah.inquiry.estimated_total') }}</p>
                    <p class="mt-1 text-lg font-extrabold text-neutral-950">{{ $this->formattedTotal }}</p>
                </div>
                <span class="text-xs text-stone-500">{{ __('umrah.inquiry.for_pax', ['count' => (int) $pax]) }}</span>
            </div>
            <p class="mt-3 text-xs leading-5 text-stone-500">{{ __('umrah.inquiry.disclaimer') }}</p>
        @endif

        @if($this->whatsappUrl)
            <x-ui::button tag="a" href="{{ $this->whatsappUrl }}" target="_blank" rel="noopener" variant="gold" class="mt-5 w-full">
                <x-slot:icon><x-lucide-message-circle /></x-slot:icon>
                {{ __('umrah.inquiry.cta') }}
            </x-ui::button>
        @else
            <x-ui::button disabled variant="gold" class="mt-5 w-full">{{ __('umrah.inquiry.cta') }}</x-ui::button>
        @endif
    </div>
</div>
