<x-layouts::app>
    @php
        $galleryImages = collect([$tour->thumbnail_url]);
        foreach ($tour->galleries as $gallery) {
            $galleryImages->push(str_starts_with($gallery->image_path, 'http') ? $gallery->image_path : \Illuminate\Support\Facades\Storage::url($gallery->image_path));
        }
        $placeholders = [
            'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1490806843957-31f4c9a91c65?q=80&w=1200&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1524413840807-0c3cb6fa808d?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1590559899731-a382839ce501?q=80&w=800&auto=format&fit=crop'
        ];
        while ($galleryImages->count() < 5) {
            $galleryImages->push($placeholders[$galleryImages->count()]);
        }
        $images = $galleryImages->take(5);
    @endphp

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" aria-label="Trip Gallery">
        <div class="grid grid-cols-2 md:grid-cols-4 grid-rows-2 gap-4 h-75 md:h-112.5">
            <img src="{{ $images[0] }}" alt="{{ $tour->name }}" class="w-full h-full object-cover rounded-2xl col-span-1 row-span-1 shadow-sm">
            <img src="{{ $images[1] }}" alt="{{ $tour->name }}" class="w-full h-full object-cover rounded-2xl col-span-2 row-span-2 shadow-sm">
            <img src="{{ $images[2] }}" alt="{{ $tour->name }}" class="w-full h-full object-cover rounded-2xl col-span-1 row-span-1 shadow-sm">
            <img src="{{ $images[3] }}" alt="{{ $tour->name }}" class="w-full h-full object-cover rounded-2xl col-span-1 row-span-1 shadow-sm">
            <img src="{{ $images[4] }}" alt="{{ $tour->name }}" class="w-full h-full object-cover rounded-2xl col-span-1 row-span-1 shadow-sm">
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-12">

            <article class="w-full lg:w-2/3">

                <nav class="flex text-sm text-slate-500 mb-4" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2">
                        <li><a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="hover:text-slate-900 transition-colors">{{ app()->getLocale() === 'id' ? 'Beranda' : (app()->getLocale() === 'ms' ? 'Utama' : 'Home') }}</a></li>
                        <li><span>/</span></li>
                        @if($tour->category)
                            <li>
                                <a href="{{ route('tour.index', ['locale' => app()->getLocale(), 'category' => $tour->category->slug]) }}" class="hover:text-slate-900 transition-colors">
                                    {{ $tour->category->name }}
                                </a>
                            </li>
                            <li><span>/</span></li>
                        @endif
                        <li class="text-slate-900 font-medium line-clamp-1" aria-current="page">{{ $tour->name }}</li>
                    </ol>
                </nav>

                <h1 class="text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight mb-4">{{ $tour->name }}</h1>
                @if($tour->category)
                    <p class="text-sm font-semibold text-blue-600 uppercase tracking-wider mb-8">{{ $tour->category->name }}</p>
                @endif

                <div class="flex flex-wrap gap-6 mb-12 py-6 border-y border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-medium">{{ __('frontend.tour.duration') }}</p>
                            <p class="text-sm font-bold text-slate-900">{{ $tour->duration_label }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-medium">{{ __('frontend.tour.difficulty') }}</p>
                            <p class="text-sm font-bold text-slate-900">{{ __('frontend.tour.' . $tour->difficulty) ?? ucfirst($tour->difficulty) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-medium">{{ __('frontend.tour.group_size') }}</p>
                            <p class="text-sm font-bold text-slate-900">{{ __('frontend.tour.day') === 'Hari' ? 'Maks.' : 'Max.' }} {{ $tour->max_pax }} Pax</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-medium">{{ __('frontend.tour.tour_type') }}</p>
                            <p class="text-sm font-bold text-slate-900">{{ __('frontend.tour.' . $tour->tour_type) ?? ucfirst($tour->tour_type) }}</p>
                        </div>
                    </div>
                </div>

                <section class="mb-12">
                    <h2 class="text-2xl font-bold text-slate-900 mb-4">{{ __('frontend.tour.overview') }}</h2>
                    <div class="text-slate-600 leading-relaxed space-y-4 text-justify">
                        {!! nl2br(e($tour->description)) !!}
                    </div>
                </section>

                @if($tour->highlights)
                    <section class="mb-12">
                        <h2 class="text-2xl font-bold text-slate-900 mb-4">{{ __('frontend.tour.highlights') }}</h2>
                        <ul class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach(explode("\n", $tour->highlights) as $highlight)
                                @if(trim($highlight))
                                    <li class="flex items-start gap-3 text-slate-600 leading-relaxed">
                                        <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span>{{ trim($highlight) }}</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </section>
                @endif

                <section class="mb-12">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {{-- Includes --}}
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900 mb-4">{{ __('frontend.tour.whats_included') }}</h2>
                            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 h-full">
                                <ul class="space-y-3.5">
                                    @forelse($tour->includes_only as $include)
                                        <li class="flex items-start gap-3 text-slate-700">
                                            <svg class="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            <span class="text-sm font-medium">{{ $include->item }}</span>
                                        </li>
                                    @empty
                                        <li class="text-sm text-slate-500 italic">{{ __('frontend.tour.no_items') }}</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>

                        {{-- Excludes --}}
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900 mb-4">{{ __('frontend.tour.whats_excluded') }}</h2>
                            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 h-full">
                                <ul class="space-y-3.5">
                                    @forelse($tour->excludes_only as $exclude)
                                        <li class="flex items-start gap-3 text-slate-700">
                                            <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            <span class="text-sm font-medium">{{ $exclude->item }}</span>
                                        </li>
                                    @empty
                                        <li class="text-sm text-slate-500 italic">{{ __('frontend.tour.no_items') }}</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <section x-data="{ activeDay: 1 }" class="mb-12">
                    <h2 class="text-2xl font-bold text-slate-900 mb-6">{{ __('frontend.tour.itinerary') }}</h2>

                    <div class="space-y-0 border-l-2 border-gray-200 ml-3">
                        @foreach($tour->itineraries as $itinerary)
                            <div class="relative pl-8 pb-8">
                                <div class="absolute w-4 h-4 bg-white border-2 rounded-full -left-2.25 top-1 transition-colors duration-200"
                                     :class="activeDay === {{ $itinerary->day_number }} ? 'border-slate-900 bg-slate-900' : 'border-gray-300'"></div>
                                
                                <button @click="activeDay = activeDay === {{ $itinerary->day_number }} ? null : {{ $itinerary->day_number }}" 
                                        class="w-full text-left flex justify-between items-center group" 
                                        :aria-expanded="activeDay === {{ $itinerary->day_number }}">
                                    <div>
                                        <p class="text-sm font-semibold text-blue-600 mb-1">{{ __('frontend.tour.day') }} {{ $itinerary->day_number }}</p>
                                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ $itinerary->title }}</h3>
                                    </div>
                                    <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" 
                                         :class="activeDay === {{ $itinerary->day_number }} ? 'rotate-180' : ''" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <div x-show="activeDay === {{ $itinerary->day_number }}" x-collapse>
                                    <div class="pt-4 text-slate-600 text-sm leading-relaxed">
                                        <p class="mb-4 text-justify">{{ $itinerary->description }}</p>
                                        
                                        @if(!empty($itinerary->meal_labels) || ($itinerary->accommodation && $itinerary->accommodation !== '-'))
                                            <div class="flex flex-wrap gap-4 mt-3 py-3 px-4 bg-slate-50 rounded-xl border border-slate-100">
                                                @if(!empty($itinerary->meal_labels))
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs text-slate-500 font-semibold">{{ __('frontend.tour.meals') }}:</span>
                                                        <div class="flex gap-2">
                                                            @foreach($itinerary->meal_labels as $mealLabel)
                                                                <span class="text-xs bg-white px-2.5 py-1 rounded-md border border-slate-200 font-medium">{{ $mealLabel }}</span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($itinerary->accommodation && $itinerary->accommodation !== '-')
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs text-slate-500 font-semibold">{{ __('frontend.tour.accommodation') }}:</span>
                                                        <span class="text-xs bg-white px-2.5 py-1 rounded-md border border-slate-200 font-medium">{{ $itinerary->accommodation }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

            </article>

            <aside class="w-full lg:w-1/3">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 sticky top-28">
                    @if($tour->is_featured)
                        <span class="inline-block px-3 py-1 bg-amber-500 text-white text-xs font-bold rounded-md mb-4 uppercase tracking-wider">{{ __('frontend.tour.featured') }}</span>
                    @elseif($tour->category)
                        <span class="inline-block px-3 py-1 bg-slate-900 text-white text-xs font-bold rounded-md mb-4 uppercase tracking-wider">{{ $tour->category->name }}</span>
                    @endif
                    <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $tour->duration_label }}</h3>
                    @if($tour->category)
                        <p class="text-sm text-slate-500 mb-4">{{ $tour->category->name }}</p>
                    @endif

                    <div class="flex items-end gap-3 mb-6">
                        <span class="text-3xl font-extrabold text-slate-900">{{ $tour->formatted_price }}</span>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4 mb-6">
                        <div class="flex justify-between items-center text-sm mb-3">
                            <span class="text-slate-600 flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ __('frontend.tour.duration') }}
                            </span>
                            <span class="font-semibold text-slate-900">{{ $tour->duration_label }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm mb-3">
                            <span class="text-slate-600 flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                {{ __('frontend.tour.accommodation') }}
                            </span>
                            <span class="font-semibold text-slate-900">
                                @if($tour->duration_nights > 0)
                                    {{ $tour->duration_nights }} {{ __('frontend.tour.day') === 'Hari' ? 'Malam' : (app()->getLocale() === 'en' ? 'Night' . ($tour->duration_nights > 1 ? 's' : '') : 'Malam') }}
                                @else
                                    {{ __('frontend.tour.day_trip') }}
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600 flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                {{ __('frontend.tour.group_size') }}
                            </span>
                            <span class="font-semibold text-slate-900">{{ __('frontend.tour.day') === 'Hari' ? 'Maks.' : 'Max.' }} {{ $tour->max_pax }} Pax</span>
                        </div>
                    </div>

                    <a href="{{ $tour->whatsappUrl() }}" target="_blank" class="block w-full text-center bg-slate-900 hover:bg-slate-800 text-white py-3.5 rounded-full font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900">
                        {{ __('frontend.cta.button_whatsapp') }}
                    </a>
                </div>
            </aside>

        </div>
    </div>

    <section class="bg-gray-100 py-16" aria-label="Recommended Trips">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-slate-900 mb-8">{{ __('frontend.tour.related_title') }}</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($relatedTours as $relatedTour)
                    <x-ui.tour-card :tour="$relatedTour" locale="{{ app()->getLocale() }}" imageHeight="h-48" :showMaxPax="false" />
                @endforeach
            </div>

            <div class="mt-10 text-center">
                <a href="{{ route('tour.index', ['locale' => app()->getLocale()]) }}" class="inline-block bg-slate-900 text-white px-8 py-3 rounded-full font-semibold hover:bg-slate-800 transition-colors">
                    {{ __('frontend.tour.see_all') }}
                </a>
            </div>
        </div>
    </section>
</x-layouts::app>
