@props(['country', 'locale' => app()->getLocale(), 'isLarge' => false])

@php
    $url = route('country.show', ['locale' => $locale, 'country' => $country]);
    $toursCount = $country->tour_packages_count ?? $country->tourPackages()->count();
    $umrahCount = $country->umrah_packages_count ?? $country->umrahPackages()->count();
    $visaCount = $country->visa_services_count ?? $country->visaServices()->count();
    $totalCount = $toursCount + $umrahCount + $visaCount;
@endphp

<article {{ $attributes->class([
    'group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-950 text-white shadow-md transition duration-500 hover:-translate-y-1 hover:border-blue-500/50 hover:shadow-2xl hover:shadow-blue-950/20',
    'min-h-[380px] lg:col-span-2 lg:row-span-2 lg:min-h-[460px]' => $isLarge,
    'min-h-[260px] sm:min-h-[280px]' => ! $isLarge,
]) }}>
    <a href="{{ $url }}" class="absolute inset-0 z-0 overflow-hidden" aria-label="{{ $country->name }}">
        <img src="{{ $country->cover_url }}" alt="{{ $country->name }}" width="1000" height="700" loading="lazy" class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-108 group-hover:opacity-90">
        <div class="absolute inset-0 bg-linear-to-t from-slate-950 via-slate-950/50 to-slate-950/20 transition-opacity group-hover:opacity-90"></div>
    </a>

    <div class="relative z-10 flex h-full flex-col justify-between p-6 sm:p-7">
        <div class="flex items-center justify-between gap-3">
            @if($country->flag_url)
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold text-white backdrop-blur-md">
                    <img src="{{ $country->flag_url }}" alt="" class="h-4 w-6 rounded-xs object-cover">
                    <span>{{ $country->iso_alpha_2 }}</span>
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-bold text-white backdrop-blur-md">
                    <x-lucide-globe class="h-3.5 w-3.5" aria-hidden="true" />
                    {{ $country->iso_alpha_2 }}
                </span>
            @endif

            @if($country->is_featured)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-400 px-3 py-1 text-xs font-extrabold text-slate-950 shadow-xs">
                    <x-lucide-sparkles class="h-3.5 w-3.5" aria-hidden="true" />
                    Featured
                </span>
            @endif
        </div>

        <div class="mt-auto pt-8">
            <h3 class="text-balance text-2xl font-black text-white sm:text-3xl">
                <a href="{{ $url }}" class="hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                    {{ $country->name }}
                </a>
            </h3>

            @if($isLarge && filled($country->description))
                <p class="mt-3 max-w-xl line-clamp-2 text-sm leading-6 text-slate-300">
                    {{ str(strip_tags((string) $country->description))->limit(160) }}
                </p>
            @endif

            <div class="mt-4 flex flex-wrap items-center gap-2">
                @if($toursCount > 0)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-500/20 px-3 py-1 text-xs font-bold text-blue-200 border border-blue-400/30 backdrop-blur-xs">
                        <x-lucide-route class="h-3.5 w-3.5" aria-hidden="true" />
                        {{ __('country.home.tours_count', ['count' => $toursCount]) }}
                    </span>
                @endif
                @if($umrahCount > 0)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/20 px-3 py-1 text-xs font-bold text-amber-200 border border-amber-400/30 backdrop-blur-xs">
                        <x-lucide-moon-star class="h-3.5 w-3.5" aria-hidden="true" />
                        {{ __('country.home.umrah_count', ['count' => $umrahCount]) }}
                    </span>
                @endif
                @if($visaCount > 0)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-bold text-emerald-200 border border-emerald-400/30 backdrop-blur-xs">
                        <x-lucide-file-text class="h-3.5 w-3.5" aria-hidden="true" />
                        {{ __('country.home.visa_count', ['count' => $visaCount]) }}
                    </span>
                @endif
                @if($totalCount === 0)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-slate-300 backdrop-blur-xs">
                        <x-lucide-compass class="h-3.5 w-3.5" aria-hidden="true" />
                        Destinasi Pilihan
                    </span>
                @endif
            </div>

            <div class="mt-5 flex items-center justify-between border-t border-white/10 pt-4">
                <span class="text-xs font-semibold text-slate-300 group-hover:text-white">
                    {{ __('country.home.offerings_count', ['count' => $totalCount]) }}
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-400 transition group-hover:translate-x-1 group-hover:text-blue-300">
                    Jelajah
                    <x-lucide-arrow-right class="h-4 w-4" aria-hidden="true" />
                </span>
            </div>
        </div>
    </div>
</article>
