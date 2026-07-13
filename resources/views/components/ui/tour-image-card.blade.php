@props(['tour', 'locale' => app()->getLocale()])

@php
    $previewPackage = $tour->packages->first();
    $description = trim(strip_tags((string) ($tour->short_description ?: $tour->description)));
@endphp

<a href="{{ route('tour.show', ['locale' => $locale, 'tour' => $tour->slug]) }}" class="group relative block aspect-3/4 overflow-hidden rounded-lg bg-slate-900 shadow-md">
    @if($previewPackage?->cover_url)
        <img src="{{ $previewPackage->cover_url }}" alt="{{ $tour->name }}" width="480" height="640" class="absolute inset-0 size-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">
    @else
        <div class="absolute inset-0 bg-linear-to-br from-blue-800 to-slate-950"></div>
    @endif
    <div class="absolute inset-0 bg-linear-to-t from-black/80 via-black/20 to-transparent"></div>
    <div class="absolute inset-x-0 bottom-0 p-4">
        @if($tour->category)
            <span class="rounded-full bg-white/90 px-2.5 py-1 text-[10px] font-bold text-slate-900">{{ $tour->category->name }}</span>
        @endif
        <h3 class="mt-3 text-base font-bold leading-snug text-white">{{ $tour->name }}</h3>
        @if($description)<p class="mt-1 line-clamp-2 text-xs leading-5 text-white/70">{{ \Illuminate\Support\Str::limit($description, 70) }}</p>@endif
        <p class="mt-3 text-[11px] font-semibold text-blue-200">{{ $tour->packages_count ?? $tour->packages->count() }} {{ $locale === 'en' ? 'package options' : ($locale === 'ms' ? 'pilihan pakej' : 'pilihan paket') }}</p>
    </div>
</a>
