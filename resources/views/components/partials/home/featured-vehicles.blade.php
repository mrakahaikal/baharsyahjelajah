@props(['vehicles', 'locale'])

<section class="border-t border-slate-200 bg-white py-16 sm:py-20" aria-labelledby="featured-vehicles-heading">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-2xl">
                <p class="text-xs font-bold uppercase text-blue-600">{{ __('transport.home.eyebrow') }}</p>
                <h2 id="featured-vehicles-heading" class="mt-3 text-balance text-3xl font-extrabold text-slate-950 sm:text-4xl">{{ __('transport.home.title') }}</h2>
                <p class="mt-4 text-sm leading-7 text-slate-500">{{ __('transport.home.subtitle') }}</p>
            </div>
            <x-ui::button tag="a" href="{{ route('transport.index', ['locale' => $locale]) }}" variant="outline" class="w-full hover:border-blue-600 hover:text-blue-600 sm:w-auto">
                {{ __('transport.home.view_all') }}<x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon>
            </x-ui::button>
        </div>

        @if($vehicles->isNotEmpty())
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($vehicles as $vehicle)
                    <x-ui.vehicle-card :$vehicle :$locale compact />
                @endforeach
            </div>
        @else
            <div class="mt-10 border-y border-slate-200 py-10 text-sm text-slate-500">{{ __('transport.index.empty_text') }}</div>
        @endif
    </div>
</section>
