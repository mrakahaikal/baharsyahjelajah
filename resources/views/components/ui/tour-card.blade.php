@props([
    'tour',
    'locale' => app()->getLocale(),
    'imageHeight' => 'h-56',
    'showMaxPax' => true,
])

@php
    $description = trim(strip_tags((string) $tour->description));
@endphp

<article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-sm transition-[border-color,box-shadow,transform] duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
    <div>
        <div class="relative {{ $imageHeight }} overflow-hidden bg-slate-100">
            @if($tour->is_featured)
                <span class="absolute left-4 top-4 z-10 rounded-full bg-slate-900/90 px-3 py-1 text-xs font-bold text-white">
                    {{ __('frontend.tour.featured') }}
                </span>
            @elseif($tour->category)
                <span class="absolute left-4 top-4 z-10 rounded-full bg-slate-900/90 px-3 py-1 text-xs font-bold text-white">
                    {{ $tour->category->name }}
                </span>
            @endif
            <img src="{{ $tour->thumbnail_url }}" alt="{{ $tour->name }}" width="640" height="448" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
        </div>
        <div class="p-5">
            <div class="mb-3 flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                @if($tour->category)
                    <span>{{ $tour->category->name }}</span>
                    <span aria-hidden="true">/</span>
                @endif
                <span>{{ __('frontend.tour.' . $tour->tour_type) ?? ucfirst($tour->tour_type) }}</span>
            </div>

            <h3 class="mb-2 line-clamp-2 text-lg font-bold text-slate-900 transition-colors group-hover:text-blue-600">
                {{ $tour->name }}
            </h3>

            @if($description)
                <p class="mb-4 line-clamp-2 text-sm leading-6 text-slate-500">
                    {{ \Illuminate\Support\Str::limit($description, 130) }}
                </p>
            @endif

            <div class="mb-4 flex flex-wrap gap-2 text-xs font-semibold text-slate-600">
                <span class="rounded-full bg-slate-100 px-3 py-1">{{ $tour->duration_label }}</span>
                @if($tour->difficulty)
                    <span class="rounded-full bg-slate-100 px-3 py-1">{{ __('frontend.tour.' . $tour->difficulty) ?? ucfirst($tour->difficulty) }}</span>
                @endif
                @if($showMaxPax && $tour->max_pax)
                    <span class="rounded-full bg-slate-100 px-3 py-1">{{ __('frontend.tour.max_pax', ['count' => $tour->max_pax]) }}</span>
                @endif
            </div>

            <div class="mb-4 flex flex-col gap-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    {{ __('frontend.featured_tour.labels.start_from') }}
                </span>
                <span class="text-2xl font-extrabold text-blue-600">
                    {{ $tour->formatted_price }}
                </span>
            </div>
        </div>
    </div>
    <div class="mt-auto p-5 pt-0">
        <a href="{{ route('tour.show', ['locale' => $locale, 'tour' => $tour->slug]) }}" 
           class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-slate-200 px-4 py-2.5 text-center text-sm font-semibold text-slate-900 transition-colors hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
            {{ __('frontend.tour.view_details') }}
            <x-lucide-arrow-right class="h-4 w-4" aria-hidden="true" />
        </a>
    </div>
</article>
