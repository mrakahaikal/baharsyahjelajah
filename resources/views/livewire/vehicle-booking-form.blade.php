<div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-start">
    <form wire:submit="submit" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <section>
            <h2 class="text-xl font-extrabold text-slate-950">{{ __('transport.booking.contact_section') }}</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <label class="block"><span class="text-sm font-bold text-slate-700">{{ __('transport.booking.name') }}</span><input wire:model="customerName" type="text" autocomplete="name" class="mt-2 min-h-11 w-full rounded-lg border border-slate-200 px-3 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/15">@error('customerName')<span class="mt-1 block text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                <label class="block"><span class="text-sm font-bold text-slate-700">{{ __('transport.booking.phone') }}</span><input wire:model="whatsappNumber" type="tel" autocomplete="tel" class="mt-2 min-h-11 w-full rounded-lg border border-slate-200 px-3 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/15">@error('whatsappNumber')<span class="mt-1 block text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                <label class="block sm:col-span-2"><span class="text-sm font-bold text-slate-700">{{ __('transport.booking.email') }}</span><input wire:model="email" type="email" autocomplete="email" class="mt-2 min-h-11 w-full rounded-lg border border-slate-200 px-3 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/15">@error('email')<span class="mt-1 block text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
            </div>
        </section>

        <section class="mt-8 border-t border-slate-200 pt-7">
            <h2 class="text-xl font-extrabold text-slate-950">{{ __('transport.booking.trip_section') }}</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <label class="block sm:col-span-2">
                    <span class="text-sm font-bold text-slate-700">{{ __('transport.booking.area') }}</span>
                    <select wire:model.live="area" class="mt-2 min-h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/15">
                        @foreach($this->availableAreas as $rentalArea)<option value="{{ $rentalArea->slug }}">{{ $rentalArea->name }} · {{ trans_choice('transport.index.minimum_days', $rentalArea->minimum_rental_days, ['count' => $rentalArea->minimum_rental_days]) }}</option>@endforeach
                    </select>
                    @error('area')<span class="mt-1 block text-xs font-semibold text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="block"><span class="text-sm font-bold text-slate-700">{{ __('transport.booking.pickup_date') }}</span><input wire:model.live="pickupDate" type="date" min="{{ now()->toDateString() }}" class="mt-2 min-h-11 w-full rounded-lg border border-slate-200 px-3 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/15">@error('pickupDate')<span class="mt-1 block text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                <label class="block"><span class="text-sm font-bold text-slate-700">{{ __('transport.booking.pickup_time') }}</span><input wire:model="pickupTime" type="time" class="mt-2 min-h-11 w-full rounded-lg border border-slate-200 px-3 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/15">@error('pickupTime')<span class="mt-1 block text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">{{ __('transport.booking.rental_days') }}</span>
                    <input wire:model.live="rentalDays" type="number" min="{{ $this->selectedArea?->minimum_rental_days ?? 1 }}" max="365" inputmode="numeric" class="mt-2 min-h-11 w-full rounded-lg border border-slate-200 px-3 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/15">
                    <span class="mt-1 block text-xs text-slate-500">{{ trans_choice('transport.booking.minimum_days', $this->selectedArea?->minimum_rental_days ?? 1, ['count' => $this->selectedArea?->minimum_rental_days ?? 1]) }}</span>
                    @error('rentalDays')<span class="mt-1 block text-xs font-semibold text-red-600">{{ $message }}</span>@enderror
                </label>
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3"><span class="text-xs font-bold uppercase text-slate-500">{{ __('transport.booking.return_date') }}</span><strong class="mt-2 block text-sm text-slate-900">{{ $this->rentalEndDate?->translatedFormat('d M Y') ?? '-' }}</strong></div>
                <label class="block sm:col-span-2"><span class="text-sm font-bold text-slate-700">{{ __('transport.booking.pickup_location') }}</span><input wire:model="pickupLocation" type="text" autocomplete="street-address" class="mt-2 min-h-11 w-full rounded-lg border border-slate-200 px-3 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/15">@error('pickupLocation')<span class="mt-1 block text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                <label class="block"><span class="text-sm font-bold text-slate-700">{{ __('transport.booking.destination') }}</span><input wire:model="destination" type="text" class="mt-2 min-h-11 w-full rounded-lg border border-slate-200 px-3 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/15">@error('destination')<span class="mt-1 block text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                <label class="block"><span class="text-sm font-bold text-slate-700">{{ __('transport.booking.pax') }}</span><input wire:model.live="pax" type="number" min="1" @if($this->vehicle->capacity_pax) max="{{ $this->vehicle->capacity_pax }}" @endif inputmode="numeric" class="mt-2 min-h-11 w-full rounded-lg border border-slate-200 px-3 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/15">@error('pax')<span class="mt-1 block text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
                <label class="block sm:col-span-2"><span class="text-sm font-bold text-slate-700">{{ __('transport.booking.notes') }}</span><textarea wire:model="notes" rows="4" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/15"></textarea>@error('notes')<span class="mt-1 block text-xs font-semibold text-red-600">{{ $message }}</span>@enderror</label>
            </div>
        </section>

        @error('service')<div role="alert" class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $message }}</div>@enderror
        <div class="mt-8 border-t border-slate-200 pt-6"><p class="flex gap-2 text-xs leading-5 text-slate-500"><x-lucide-shield-check class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" />{{ __('transport.booking.privacy') }}</p><x-ui::button type="submit" size="lg" :loading="true" loading-target="submit" :loading-text="__('transport.booking.submitting')" class="mt-5 w-full hover:bg-blue-600 sm:w-auto">{{ __('transport.booking.submit') }}<x-slot:trailingIcon><x-lucide-message-circle /></x-slot:trailingIcon></x-ui::button></div>
    </form>

    <aside class="lg:sticky lg:top-28">
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <img src="{{ $this->vehicle->thumbnail_url }}" alt="{{ $this->vehicle->name }}" width="720" height="450" class="aspect-[16/9] w-full object-cover">
            <div class="p-5"><p class="text-xs font-bold uppercase text-blue-600">{{ __('transport.booking.summary') }}</p><h2 class="mt-2 text-xl font-extrabold text-slate-950">{{ $this->vehicle->name }}</h2><p class="mt-2 text-sm text-slate-500">{{ $this->vehicle->capacity_display }} · {{ __('transport.show.driver_included') }}</p></div>
            <dl class="border-t border-slate-100 bg-slate-50 p-5">
                <div class="flex justify-between gap-3 text-sm"><dt class="text-slate-500">{{ __('transport.booking.area') }}</dt><dd class="text-right font-bold text-slate-900">{{ $this->selectedArea?->name ?? '-' }}</dd></div>
                <div class="mt-3 flex justify-between gap-3 text-sm"><dt class="text-slate-500">{{ __('transport.card.daily') }}</dt><dd class="font-bold text-slate-900">{{ $this->selectedRate?->formatted_price ?? __('transport.booking.on_request') }}</dd></div>
                <div class="mt-3 flex justify-between gap-3 text-sm"><dt class="text-slate-500">{{ __('transport.booking.duration', ['count' => (int) $rentalDays]) }}</dt><dd class="font-bold text-slate-900">{{ (int) $rentalDays }}</dd></div>
                <div class="mt-3 flex justify-between gap-3 border-t border-slate-200 pt-3"><dt class="font-bold text-slate-700">{{ __('transport.booking.estimated_total') }}</dt><dd class="text-lg font-extrabold text-blue-600">{{ $this->formattedEstimate ?? __('transport.booking.on_request') }}</dd></div>
                <p class="mt-4 text-xs leading-5 text-slate-500">{{ __('transport.booking.disclaimer') }}</p>
            </dl>
        </div>
    </aside>
</div>
