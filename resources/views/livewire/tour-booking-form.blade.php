<div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-start">
    <form wire:submit="submit" class="min-w-0">
        <div class="grid gap-7">
            <section aria-labelledby="booking-contact-heading">
                <div class="flex items-start gap-3">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-blue-50 text-sm font-bold text-blue-700">1</span>
                    <div>
                        <h2 id="booking-contact-heading" class="text-xl font-extrabold text-slate-900">{{ __('frontend.tour.booking.form.contact_title') }}</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-500">{{ __('frontend.tour.booking.form.contact_description') }}</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <label class="grid gap-2 text-sm font-semibold text-slate-700">
                        {{ __('frontend.tour.booking.form.name') }}
                        <input wire:model="customerName" type="text" autocomplete="name" class="rounded-lg border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 focus:border-blue-500 focus:outline-2 focus:outline-offset-2 focus:outline-blue-600">
                        @error('customerName')<span class="text-xs font-medium text-red-600">{{ $message }}</span>@enderror
                    </label>
                    <label class="grid gap-2 text-sm font-semibold text-slate-700">
                        {{ __('frontend.tour.booking.form.whatsapp') }}
                        <input wire:model="whatsappNumber" type="tel" inputmode="tel" autocomplete="tel" placeholder="0812..." class="rounded-lg border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 focus:border-blue-500 focus:outline-2 focus:outline-offset-2 focus:outline-blue-600">
                        @error('whatsappNumber')<span class="text-xs font-medium text-red-600">{{ $message }}</span>@enderror
                    </label>
                    <label class="grid gap-2 text-sm font-semibold text-slate-700 sm:col-span-2">
                        {{ __('frontend.tour.booking.form.email') }}
                        <input wire:model="email" type="email" autocomplete="email" spellcheck="false" class="rounded-lg border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 focus:border-blue-500 focus:outline-2 focus:outline-offset-2 focus:outline-blue-600">
                        @error('email')<span class="text-xs font-medium text-red-600">{{ $message }}</span>@enderror
                    </label>
                </div>
            </section>

            <section class="border-t border-slate-200 pt-7" aria-labelledby="booking-trip-heading">
                <div class="flex items-start gap-3">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-blue-50 text-sm font-bold text-blue-700">2</span>
                    <div>
                        <h2 id="booking-trip-heading" class="text-xl font-extrabold text-slate-900">{{ __('frontend.tour.booking.form.trip_title') }}</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-500">{{ __('frontend.tour.booking.form.trip_description') }}</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <label class="grid gap-2 text-sm font-semibold text-slate-700">
                        {{ __('frontend.tour.booking.form.departure_date') }}
                        <input wire:model="departureDate" type="date" min="{{ now()->toDateString() }}" class="rounded-lg border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 focus:border-blue-500 focus:outline-2 focus:outline-offset-2 focus:outline-blue-600">
                        @error('departureDate')<span class="text-xs font-medium text-red-600">{{ $message }}</span>@enderror
                    </label>
                    <label class="grid gap-2 text-sm font-semibold text-slate-700">
                        {{ __('frontend.tour.booking.form.pax') }}
                        <input wire:model.live.debounce.400ms="pax" type="number" min="1" max="1000" inputmode="numeric" class="rounded-lg border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 focus:border-blue-500 focus:outline-2 focus:outline-offset-2 focus:outline-blue-600">
                        @error('pax')<span class="text-xs font-medium text-red-600">{{ $message }}</span>@enderror
                    </label>
                    @if($this->package->tiers->isNotEmpty())
                        <label class="grid gap-2 text-sm font-semibold text-slate-700 sm:col-span-2">
                            {{ __('frontend.tour.booking.form.tier') }}
                            <select wire:model.live="selectedTierId" class="rounded-lg border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 focus:border-blue-500 focus:outline-2 focus:outline-offset-2 focus:outline-blue-600">
                                @foreach($this->package->tiers as $tier)
                                    <option value="{{ $tier->id }}">{{ $tier->name }}</option>
                                @endforeach
                            </select>
                            @error('selectedTierId')<span class="text-xs font-medium text-red-600">{{ $message }}</span>@enderror
                        </label>
                    @endif
                    <label class="grid gap-2 text-sm font-semibold text-slate-700 sm:col-span-2">
                        {{ __('frontend.tour.booking.form.notes') }}
                        <textarea wire:model="notes" rows="4" maxlength="1000" placeholder="{{ __('frontend.tour.booking.form.notes_placeholder') }}" class="resize-y rounded-lg border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-2 focus:outline-offset-2 focus:outline-blue-600"></textarea>
                        @error('notes')<span class="text-xs font-medium text-red-600">{{ $message }}</span>@enderror
                    </label>
                </div>
            </section>
        </div>

        @error('service')
            <div role="alert" class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $message }}</div>
        @enderror

        <div class="mt-8 border-t border-slate-200 pt-6">
            <p class="flex items-start gap-2 text-xs leading-5 text-slate-500">
                <x-lucide-shield-check class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" aria-hidden="true" />
                {{ __('frontend.tour.booking.form.privacy_note') }}
            </p>
            <button type="submit" wire:loading.attr="disabled" wire:target="submit" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-full bg-slate-900 px-6 py-3.5 text-sm font-semibold text-white transition-colors hover:bg-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:cursor-wait disabled:bg-slate-400 sm:w-auto">
                <span wire:loading.remove wire:target="submit">{{ __('frontend.tour.booking.form.submit') }}</span>
                <span wire:loading wire:target="submit">{{ __('frontend.tour.booking.form.submitting') }}</span>
                <x-lucide-message-circle class="h-4 w-4" aria-hidden="true" />
            </button>
        </div>
    </form>

    <aside class="lg:sticky lg:top-28">
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm shadow-slate-900/5">
            <img src="{{ $this->package->cover_url }}" alt="{{ $this->package->name }}" width="720" height="450" class="aspect-[16/9] w-full object-cover">
            <div class="p-5">
                <p class="text-xs font-semibold uppercase text-blue-600">{{ $this->package->tour->name }}</p>
                <h2 class="mt-2 text-lg font-extrabold text-slate-900">{{ $this->package->name }}</h2>
                <div class="mt-4 flex items-center gap-2 text-sm text-slate-600">
                    <x-lucide-clock-3 class="h-4 w-4 text-blue-600" aria-hidden="true" />
                    {{ $this->package->duration_label }}
                </div>
                @if($this->selectedTier)
                    <div class="mt-2 flex items-center gap-2 text-sm text-slate-600">
                        <x-lucide-layers-3 class="h-4 w-4 text-blue-600" aria-hidden="true" />
                        {{ $this->selectedTier->name }}
                    </div>
                @endif
            </div>

            <dl class="border-t border-slate-100 bg-slate-50 p-5" wire:loading.class="opacity-60">
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-sm text-slate-500">{{ __('frontend.tour.booking.summary.per_person') }}</dt>
                    <dd class="text-right text-sm font-bold text-slate-900">{{ $this->selectedPriceTier?->formatted_price ?? __('frontend.tour.booking.summary.on_request') }}</dd>
                </div>
                <div class="mt-3 flex items-start justify-between gap-4 border-t border-slate-200 pt-3">
                    <dt class="text-sm font-semibold text-slate-700">{{ __('frontend.tour.booking.summary.estimated_total') }}</dt>
                    <dd class="text-right text-lg font-extrabold text-blue-600">{{ $this->formattedTotal ?? __('frontend.tour.booking.summary.on_request') }}</dd>
                </div>
                <p class="mt-3 text-xs leading-5 text-slate-500">{{ __('frontend.tour.booking.summary.disclaimer') }}</p>
            </dl>
        </div>

        <a href="{{ route('tour.package.show', ['locale' => app()->getLocale(), 'tour' => $this->package->tour->slug, 'package' => $this->package->slug]) }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
            <x-lucide-arrow-left class="h-4 w-4" aria-hidden="true" />
            {{ __('frontend.tour.booking.back_to_package') }}
        </a>
    </aside>
</div>
