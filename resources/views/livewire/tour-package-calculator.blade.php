@php($validPax = ctype_digit($pax) && (int) $pax >= 1 && (int) $pax <= 1000)

<div class="sticky top-36 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg shadow-slate-900/5">
    <div class="border-b border-slate-100 p-6">
        <p class="text-xs font-semibold uppercase text-blue-600">{{ __('frontend.tour.package.calculator.eyebrow') }}</p>
        <h2 class="mt-2 text-xl font-extrabold text-slate-900">{{ __('frontend.tour.package.calculator.title') }}</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('frontend.tour.package.calculator.description') }}</p>
    </div>

    <div class="grid gap-5 p-6">
        @if($this->package->tiers->isNotEmpty())
            <label class="grid gap-2 text-sm font-semibold text-slate-700">
                {{ __('frontend.tour.package.calculator.tier_label') }}
                <select wire:model.live="selectedTierId" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-2 focus:outline-offset-2 focus:outline-blue-600">
                    @foreach($this->package->tiers as $tier)
                        <option value="{{ $tier->id }}">{{ $tier->name }}</option>
                    @endforeach
                </select>
            </label>
        @endif

        <label class="grid gap-2 text-sm font-semibold text-slate-700">
            {{ __('frontend.tour.package.calculator.pax_label') }}
            <div class="relative">
                <x-lucide-users class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" aria-hidden="true" />
                <input wire:model.live.debounce.400ms="pax" type="number" min="1" max="1000" inputmode="numeric" class="w-full rounded-lg border bg-white py-2.5 pl-10 pr-3 text-sm text-slate-900 focus:outline-2 focus:outline-offset-2 {{ $validPax ? 'border-slate-300 focus:border-blue-500 focus:outline-blue-600' : 'border-red-300 focus:border-red-500 focus:outline-red-600' }}">
            </div>
            @unless($validPax)
                <span class="text-xs font-medium text-red-600">{{ __('frontend.tour.package.calculator.pax_error') }}</span>
            @endunless
        </label>
    </div>

    <div class="border-t border-slate-100 bg-slate-50 p-6" wire:loading.class="opacity-60">
        @if($validPax && $this->selectedPriceTier)
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-400">{{ __('frontend.tour.package.calculator.per_person') }}</p>
                    <p class="mt-1 text-2xl font-extrabold text-blue-600">{{ $this->selectedPriceTier->formatted_price }}</p>
                </div>
                <x-lucide-calculator class="h-5 w-5 shrink-0 text-blue-600" aria-hidden="true" />
            </div>
            <div class="mt-4 flex items-end justify-between gap-4 border-t border-slate-200 pt-4">
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-400">{{ __('frontend.tour.package.calculator.estimated_total') }}</p>
                    <p class="mt-1 text-lg font-extrabold text-slate-900">{{ $this->formattedTotal }}</p>
                </div>
                <span class="text-xs text-slate-500">{{ __('frontend.tour.package.calculator.for_pax', ['count' => (int) $pax]) }}</span>
            </div>
            <p class="mt-3 text-xs leading-5 text-slate-500">{{ __('frontend.tour.package.calculator.estimate_note') }}</p>
        @elseif($validPax)
            <div class="flex items-start gap-3">
                <x-lucide-message-square-text class="mt-0.5 h-5 w-5 shrink-0 text-blue-600" aria-hidden="true" />
                <p class="text-sm leading-6 text-slate-600">{{ __('frontend.tour.package.calculator.price_unavailable') }}</p>
            </div>
        @endif

        @if($this->bookingUrl)
            <x-ui::button tag="a" href="{{ $this->bookingUrl }}" class="mt-5 w-full hover:bg-blue-600">
                {{ __('frontend.tour.package.calculator.booking_cta') }}
                <x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon>
            </x-ui::button>
        @else
            <x-ui::button disabled class="mt-5 w-full">
                {{ __('frontend.tour.package.calculator.booking_cta') }}
            </x-ui::button>
        @endif

        <a href="{{ route('tour.show', ['locale' => app()->getLocale(), 'tour' => $this->package->tour->localizedSlug()]) }}" class="mt-3 inline-flex w-full items-center justify-center text-sm font-semibold text-blue-600 hover:text-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
            {{ __('frontend.tour.package.calculator.other_packages') }}
        </a>
    </div>
</div>
