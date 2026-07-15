<aside class="lg:sticky lg:top-28">
    <form wire:submit="submit" class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg shadow-slate-900/8">
        <div class="border-b border-emerald-800 bg-emerald-950 p-5 text-white sm:p-6">
            <p class="text-xs font-bold uppercase text-lime-300">{{ __('visa.inquiry.eyebrow') }}</p>
            <h2 class="mt-2 text-xl font-extrabold">{{ __('visa.inquiry.title') }}</h2>
            <p class="mt-2 text-xs leading-5 text-emerald-100/70">{{ __('visa.inquiry.description') }}</p>
        </div>

        <div class="grid gap-5 p-5 sm:p-6">
            <label class="grid gap-2 text-sm font-bold text-slate-700">
                {{ __('visa.inquiry.name') }}
                <input wire:model="customerName" type="text" autocomplete="name" maxlength="100" class="min-h-11 rounded-lg border border-slate-300 bg-white px-3.5 text-sm text-slate-900 outline-none focus:border-emerald-700 focus:outline-2 focus:outline-offset-2 focus:outline-emerald-700">
                @error('customerName')<span class="text-xs font-semibold text-rose-600">{{ $message }}</span>@enderror
            </label>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-1">
                <label class="grid gap-2 text-sm font-bold text-slate-700">
                    {{ __('visa.inquiry.applicants') }}
                    <input wire:model="applicants" type="number" min="1" max="100" inputmode="numeric" class="min-h-11 rounded-lg border border-slate-300 bg-white px-3.5 text-sm text-slate-900 outline-none focus:border-emerald-700 focus:outline-2 focus:outline-offset-2 focus:outline-emerald-700">
                    @error('applicants')<span class="text-xs font-semibold text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-2 text-sm font-bold text-slate-700">
                    {{ __('visa.inquiry.departure') }}
                    <input wire:model="departureDate" type="date" min="{{ now()->toDateString() }}" class="min-h-11 rounded-lg border border-slate-300 bg-white px-3.5 text-sm text-slate-900 outline-none focus:border-emerald-700 focus:outline-2 focus:outline-offset-2 focus:outline-emerald-700">
                    @error('departureDate')<span class="text-xs font-semibold text-rose-600">{{ $message }}</span>@enderror
                </label>
            </div>

            <label class="grid gap-2 text-sm font-bold text-slate-700">
                {{ __('visa.inquiry.notes') }}
                <textarea wire:model="notes" rows="3" maxlength="500" placeholder="{{ __('visa.inquiry.notes_placeholder') }}" class="resize-y rounded-lg border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:border-emerald-700 focus:outline-2 focus:outline-offset-2 focus:outline-emerald-700"></textarea>
                @error('notes')<span class="text-xs font-semibold text-rose-600">{{ $message }}</span>@enderror
            </label>

            @error('service')
                <div role="alert" class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-xs font-semibold leading-5 text-rose-700">{{ $message }}</div>
            @enderror

            <div class="border-t border-slate-100 pt-5">
                <div class="flex items-end justify-between gap-4">
                    <span class="text-xs font-semibold text-slate-500">{{ __('visa.inquiry.estimated_price') }}</span>
                    <strong class="text-right text-lg text-emerald-800">{{ $this->service->formatted_price ?? __('visa.price_on_request') }}</strong>
                </div>
                <p class="mt-3 flex items-start gap-2 text-xs leading-5 text-slate-500">
                    <x-lucide-shield-check class="mt-0.5 h-4 w-4 shrink-0 text-emerald-700" aria-hidden="true" />
                    {{ __('visa.inquiry.privacy') }}
                </p>
                <x-ui::button type="submit" size="lg" :loading="true" loading-target="submit" :loading-text="__('visa.inquiry.submitting')" class="mt-5 w-full hover:bg-emerald-800 disabled:cursor-wait">
                    {{ __('visa.inquiry.submit') }}
                    <x-slot:trailingIcon><x-lucide-message-circle /></x-slot:trailingIcon>
                </x-ui::button>
            </div>
        </div>
    </form>
</aside>
