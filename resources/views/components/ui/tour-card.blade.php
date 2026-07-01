@props([
    'tour',
    'locale' => app()->getLocale(),
    'imageHeight' => 'h-56',
    'showMaxPax' => true,
])

<div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow group flex flex-col justify-between h-full border border-slate-200/60">
    <div>
        <div class="relative {{ $imageHeight }} overflow-hidden bg-slate-100">
            @if($tour->is_featured)
                <span class="absolute top-4 left-4 bg-slate-900/90 text-white text-xs font-bold px-3 py-1 rounded-full z-10">
                    {{ __('frontend.tour.featured') }}
                </span>
            @elseif($tour->category)
                <span class="absolute top-4 left-4 bg-slate-900/90 text-white text-xs font-bold px-3 py-1 rounded-full z-10">
                    {{ $tour->category->name }}
                </span>
            @endif
            <img src="{{ $tour->thumbnail_url }}" alt="{{ $tour->name }}" width="640" height="448" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        </div>
        <div class="p-5">
            <h3 class="font-bold text-lg text-slate-900 mb-1 line-clamp-1 group-hover:text-blue-600 transition-colors">
                {{ $tour->name }}
            </h3>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-4">
                <span>{{ $tour->duration_label }}</span>
                @if($showMaxPax && $tour->max_pax)
                    <span>•</span>
                    <span>Max. {{ $tour->max_pax }} Pax</span>
                @endif
            </div>
            <div class="flex flex-col gap-0.5 mb-4">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                    {{ __('frontend.featured_tour.labels.start_from') }}
                </span>
                <span class="text-2xl font-extrabold text-blue-600">
                    {{ $tour->formatted_price }}
                </span>
            </div>
        </div>
    </div>
    <div class="p-5 pt-0">
        <a href="{{ route('tour.show', ['locale' => $locale, 'tour' => $tour->slug]) }}" 
           class="block w-full text-center py-2.5 border border-slate-200 text-slate-900 font-semibold rounded-full hover:bg-slate-50 transition-colors text-sm">
            {{ __('frontend.tour.view_itinerary') }}
        </a>
    </div>
</div>
