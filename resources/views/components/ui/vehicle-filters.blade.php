<div {{ $attributes->merge(['class' => 'space-y-5']) }}>
    <label class="block">
        <span class="text-xs font-bold uppercase text-slate-500">{{ __('transport.index.area') }}</span>
        <select wire:model.live="area" class="mt-2 min-h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/15">
            <option value="">{{ __('transport.index.area_placeholder') }}</option>
            @foreach($this->areas as $rentalArea)
                <option value="{{ $rentalArea->slug }}">{{ $rentalArea->name }} · {{ trans_choice('transport.index.minimum_days', $rentalArea->minimum_rental_days, ['count' => $rentalArea->minimum_rental_days]) }}</option>
            @endforeach
        </select>
    </label>

    <label class="block">
        <span class="text-xs font-bold uppercase text-slate-500">{{ __('transport.index.capacity') }}</span>
        <span class="mt-2 flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-600/15">
            <x-lucide-users class="h-4 w-4 shrink-0 text-slate-400" aria-hidden="true" />
            <input wire:model.live.debounce.300ms="capacity" type="number" min="1" max="100" inputmode="numeric" placeholder="{{ __('transport.index.capacity_placeholder') }}" class="min-h-11 min-w-0 flex-1 border-0 bg-transparent text-sm text-slate-950 outline-none">
        </span>
    </label>

    <label class="block">
        <span class="text-xs font-bold uppercase text-slate-500">{{ __('transport.index.category') }}</span>
        <select wire:model.live="category" class="mt-2 min-h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/15">
            <option value="">{{ __('transport.index.all_categories') }}</option>
            @foreach(\App\Enums\VehicleCategory::cases() as $vehicleCategory)
                <option value="{{ $vehicleCategory->value }}">{{ __('transport.category.'.$vehicleCategory->value) }}</option>
            @endforeach
        </select>
    </label>

    <button wire:click="resetFilters" type="button" class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-lg border border-slate-200 px-3 text-sm font-bold text-slate-600 transition hover:border-blue-300 hover:text-blue-700">
        <x-lucide-rotate-ccw class="h-4 w-4" aria-hidden="true" />{{ __('transport.index.reset') }}
    </button>
</div>
