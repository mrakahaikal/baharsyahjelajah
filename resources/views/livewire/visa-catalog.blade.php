<div class="mt-9">
    @if($countries->isNotEmpty())
        <div class="border-y border-slate-200 py-4">
            <div class="hidden flex-wrap items-center gap-2 sm:flex" role="group" aria-label="{{ __('visa.filter.label') }}">
                <button type="button" wire:click="selectCountry('')" class="min-h-10 rounded-md border px-4 text-sm font-bold transition-colors {{ $country === '' ? 'border-emerald-900 bg-emerald-900 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-emerald-300 hover:text-emerald-800' }}">
                    {{ __('visa.filter.all') }}
                </button>
                @foreach($countries as $countryOption)
                    <button type="button" wire:key="visa-country-{{ $countryOption->id }}" wire:click="selectCountry('{{ $countryOption->slug }}')" class="inline-flex min-h-10 items-center gap-2 rounded-md border px-4 text-sm font-bold transition-colors {{ $country === $countryOption->slug ? 'border-emerald-900 bg-emerald-900 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-emerald-300 hover:text-emerald-800' }}">
                        @if($countryOption->flag_url)
                            <img src="{{ $countryOption->flag_url }}" alt="" width="24" height="16" class="h-4 w-6 rounded-sm object-cover" aria-hidden="true">
                        @endif
                        {{ $countryOption->name }}
                        <span class="text-xs opacity-65">{{ $countryOption->public_visa_services_count }}</span>
                    </button>
                @endforeach
            </div>

            <label class="grid gap-2 text-sm font-bold text-slate-800 sm:hidden">
                {{ __('visa.filter.label') }}
                <select wire:model.live="country" class="min-h-12 w-full rounded-lg border border-slate-300 bg-white px-3.5 text-sm text-slate-900 outline-none focus:border-emerald-700 focus:outline-2 focus:outline-offset-2 focus:outline-emerald-700">
                    <option value="">{{ __('visa.filter.all') }}</option>
                    @foreach($countries as $countryOption)
                        <option value="{{ $countryOption->slug }}">{{ $countryOption->name }} ({{ $countryOption->public_visa_services_count }})</option>
                    @endforeach
                </select>
            </label>
        </div>
    @endif

    <div class="mt-6 flex items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <p class="text-sm font-semibold text-slate-600">{{ __('visa.catalog.results', ['count' => $services->total()]) }}</p>
        <div wire:loading class="text-xs font-semibold text-emerald-700" role="status">{{ __('visa.catalog.loading') }}</div>
    </div>

    <div wire:loading.class="opacity-50" class="mt-6 transition-opacity">
        @if($services->isNotEmpty())
            <div class="grid gap-6 md:grid-cols-2">
                @foreach($services as $service)
                    <x-ui.visa-service-card :$service :locale="app()->getLocale()" wire:key="visa-service-{{ $service->id }}" />
                @endforeach
            </div>

            @if($services->hasPages())
                <div class="mt-9">{{ $services->links() }}</div>
            @endif
        @else
            <div class="border-y border-slate-200 py-14 text-center">
                <x-lucide-file-search class="mx-auto h-9 w-9 text-slate-300" aria-hidden="true" />
                <h3 class="mt-4 text-lg font-extrabold text-slate-950">{{ __('visa.empty.title') }}</h3>
                <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500">{{ __('visa.empty.description') }}</p>
                @if($country !== '')
                    <button type="button" wire:click="selectCountry('')" class="mt-5 text-sm font-bold text-emerald-700 hover:text-emerald-900">{{ __('visa.empty.reset') }}</button>
                @endif
            </div>
        @endif
    </div>
</div>
