<div class="grid gap-8 lg:grid-cols-[16rem_minmax(0,1fr)] lg:items-start">
    <aside class="hidden rounded-lg border border-slate-200 bg-slate-50 p-5 lg:sticky lg:top-28 lg:block">
        <h2 class="font-extrabold text-slate-950">{{ __('transport.index.filter_title') }}</h2>
        <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('transport.index.filter_hint') }}</p>
        <x-ui.vehicle-filters class="mt-6" />
    </aside>

    <div class="min-w-0">
        <details class="mb-5 rounded-lg border border-slate-200 bg-slate-50 lg:hidden">
            <summary class="flex min-h-12 cursor-pointer list-none items-center justify-between gap-3 px-4 font-bold text-slate-900">
                <span class="inline-flex items-center gap-2"><x-lucide-sliders-horizontal class="h-4 w-4 text-blue-600" />{{ __('transport.index.mobile_filter') }}</span>
                <x-lucide-chevron-down class="h-4 w-4 text-slate-400" />
            </summary>
            <x-ui.vehicle-filters class="border-t border-slate-200 p-4" />
        </details>

        <div class="mb-5 flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm font-semibold text-slate-600">{{ __('transport.index.results', ['count' => $vehicles->total()]) }}</p>
            <label class="flex items-center gap-2 text-xs font-bold uppercase text-slate-500">
                {{ __('transport.index.sort') }}
                <select wire:model.live="sort" class="min-h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold normal-case text-slate-800 outline-none focus:border-blue-600">
                    <option value="featured">{{ __('transport.index.featured') }}</option>
                    <option value="capacity">{{ __('transport.index.smallest_capacity') }}</option>
                </select>
            </label>
        </div>

        <div wire:loading.class="opacity-50" class="transition-opacity">
            @if($vehicles->isNotEmpty())
                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($vehicles as $vehicle)
                        <x-ui.vehicle-card :$vehicle :locale="app()->getLocale()" wire:key="vehicle-{{ $vehicle->id }}" />
                    @endforeach
                </div>
                <div class="mt-8">{{ $vehicles->links() }}</div>
            @else
                <div class="border-y border-slate-200 py-16 text-center">
                    <x-lucide-car-front class="mx-auto h-9 w-9 text-slate-300" aria-hidden="true" />
                    <h2 class="mt-4 font-extrabold text-slate-950">{{ __('transport.index.empty_title') }}</h2>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">{{ __('transport.index.empty_text') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
