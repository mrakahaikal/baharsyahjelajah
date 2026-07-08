@props(['tour', 'locale'])

<a href="{{ route('tour.show', ['locale' => $locale, 'tour' => $tour->slug]) }}"
   class="group relative overflow-hidden rounded-2xl aspect-3/4 block shadow-card">
    @if($tour->thumbnail_url)
        <img
            src="{{ $tour->thumbnail_url }}"
            alt="{{ $tour->name }}"
            class="absolute inset-0 size-full object-cover transition-transform duration-500 group-hover:scale-105"
            loading="lazy"
        >
    @else
        <div class="absolute inset-0 bg-linear-to-br from-brand-700 to-brand-primary"></div>
    @endif
    <div class="absolute inset-0 bg-linear-to-t from-black/75 via-black/20 to-transparent"></div>
    <div class="absolute bottom-0 left-0 right-0 p-4">
        <span class="rounded-full bg-slate-900/90 px-3 py-1 text-xs font-bold text-white">
            {{ $tour->category->name }}
        </span>
        <div class="font-display font-bold text-white text-md mt-2 leading-snug">
            {{ $tour->name }}
        </div>
        <div class="text-white/60 text-xs mt-0.5">
            {{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $tour->description)), 60) }}
        </div>
    </div>
</a>
