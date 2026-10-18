@php
    $locale = app()->getLocale();
    $seoTitle = __('frontend.gallery_page.seo_title', ['brand' => config('app.name')]);
    $seoDescription = __('frontend.gallery_page.seo_description');

    $allImages = collect();

    foreach ($tourGalleries as $media) {
        $allImages->push([
            'id' => 'tour-'.$media->id,
            'url' => $media->getUrl(),
            'title' => $media->name ?: ($media->model?->name ?? 'Wisata Halal'),
            'category' => 'Tour',
            'badge' => 'Tour Wisata',
        ]);
    }

    foreach ($vehicleGalleries as $vg) {
        $allImages->push([
            'id' => 'vehicle-'.$vg->id,
            'url' => asset('storage/'.$vg->image_path),
            'title' => $vg->vehicle?->name ?? 'Armada Transportasi',
            'category' => 'Transport',
            'badge' => 'Armada',
        ]);
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'ImageGallery',
        'name' => $seoTitle,
        'description' => $seoDescription,
        'url' => $canonicalUrl,
        'inLanguage' => $locale,
        'publisher' => [
            '@type' => 'TravelAgency',
            'name' => config('app.name'),
            'url' => url('/'),
        ],
    ];

    if ($allImages->isNotEmpty()) {
        $schema['image'] = $allImages->take(12)->map(fn ($img) => [
            '@type' => 'ImageObject',
            'contentUrl' => $img['url'],
            'name' => $img['title'],
        ])->values()->all();
    }
@endphp

<x-layouts::app
    seo-page="gallery_index"
    :title="$seoTitle"
    :meta-description="$seoDescription"
    breadcrumb-name="gallery.index"
    :breadcrumb-parameters="[$locale]"
    :$canonicalUrl
    :$alternateUrls
    :schema="$schema">

    <!-- Hero Section -->
    <section class="bg-slate-950 text-white">
        <div class="mx-auto max-w-7xl px-4 pb-14 pt-5 sm:px-6 sm:pb-16 lg:px-8 lg:pb-20">
            <x-ui.breadcrumbs name="gallery.index" :parameters="[$locale]" variant="dark" class="mb-10" />

            <div class="max-w-3xl">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-400">
                    {{ __('frontend.home.hero_badge', ['default' => 'Dokumentasi Perjalanan']) }}
                </p>
                <h1 class="mt-4 text-balance text-3xl font-black leading-tight sm:text-4xl lg:text-5xl">
                    {{ __('frontend.gallery_page.title') }}
                </h1>
                <p class="mt-5 text-pretty text-base leading-8 text-slate-300 sm:text-lg">
                    {{ __('frontend.gallery_page.subtitle') }}
                </p>
            </div>
        </div>
    </section>

    <!-- Gallery Grid Section -->
    <section class="bg-slate-50 py-12 sm:py-16 lg:py-20" x-data="{ activeImage: null }">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if($allImages->isNotEmpty())
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                    @foreach($allImages as $image)
                        <div
                            class="group relative aspect-4/3 cursor-pointer overflow-hidden rounded-2xl bg-slate-200 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl focus-visible:outline-2 focus-visible:outline-blue-600"
                            tabindex="0"
                            role="button"
                            aria-label="{{ $image['title'] }}"
                            @click="activeImage = @js($image)"
                            @keydown.enter="activeImage = @js($image)"
                            @keydown.space.prevent="activeImage = @js($image)">
                            <img
                                src="{{ $image['url'] }}"
                                alt="{{ $image['title'] }}"
                                loading="lazy"
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent opacity-80 transition duration-300 group-hover:opacity-90"></div>

                            <div class="absolute inset-x-0 bottom-0 p-4">
                                <span class="inline-block rounded-full bg-blue-600/90 px-2.5 py-0.5 text-[10px] font-bold text-white uppercase tracking-wider backdrop-blur-xs">
                                    {{ $image['badge'] }}
                                </span>
                                <h3 class="mt-1.5 line-clamp-2 text-sm font-bold text-white">
                                    {{ $image['title'] }}
                                </h3>
                            </div>

                            <div class="absolute right-3 top-3 grid h-8 w-8 place-items-center rounded-full bg-white/20 text-white backdrop-blur-sm opacity-0 transition duration-300 group-hover:opacity-100">
                                <x-lucide-maximize-2 class="h-4 w-4" aria-hidden="true" />
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Lightbox Modal -->
                <div
                    x-cloak
                    x-show="activeImage"
                    x-transition.opacity
                    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/90 p-4 backdrop-blur-md"
                    @keydown.escape.window="activeImage = null"
                    role="dialog"
                    aria-modal="true">
                    <div
                        class="relative max-h-[90vh] max-w-4xl overflow-hidden rounded-2xl bg-slate-900 shadow-2xl"
                        @click.outside="activeImage = null">
                        <button
                            type="button"
                            @click="activeImage = null"
                            class="absolute right-4 top-4 z-10 grid h-10 w-10 place-items-center rounded-full bg-slate-950/70 text-white transition hover:bg-slate-950 focus:outline-none"
                            aria-label="Tutup">
                            <x-lucide-x class="h-5 w-5" aria-hidden="true" />
                        </button>

                        <template x-if="activeImage">
                            <div>
                                <img
                                    :src="activeImage.url"
                                    :alt="activeImage.title"
                                    class="max-h-[75vh] w-full object-contain">
                                <div class="bg-slate-900 px-6 py-4">
                                    <span class="inline-block rounded-full bg-blue-600 px-2.5 py-0.5 text-xs font-bold text-white" x-text="activeImage.badge"></span>
                                    <h4 class="mt-1 text-base font-bold text-white" x-text="activeImage.title"></h4>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            @else
                <div class="rounded-3xl border border-slate-200 bg-white p-12 text-center shadow-sm">
                    <div class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-blue-50 text-blue-600">
                        <x-lucide-images class="h-8 w-8" aria-hidden="true" />
                    </div>
                    <h2 class="mt-4 text-lg font-bold text-slate-900">{{ __('frontend.gallery_page.empty') }}</h2>
                    <p class="mt-2 text-sm text-slate-500 max-w-md mx-auto">
                        {{ __('frontend.gallery_page.subtitle') }}
                    </p>
                    <div class="mt-6">
                        <x-ui::button tag="a" href="{{ route('tour.index', ['locale' => $locale]) }}">
                            {{ __('frontend.tour.show.breadcrumb_tours') }}
                        </x-ui::button>
                    </div>
                </div>
            @endif

            <!-- Bottom CTA -->
            <div class="mt-16 rounded-3xl bg-radial from-blue-700 via-blue-800 to-slate-950 p-8 text-center text-white sm:p-12">
                <h2 class="text-2xl font-black sm:text-3xl">
                    {{ __('frontend.footer.consultation_prompt') }}
                </h2>
                <p class="mx-auto mt-3 max-w-2xl text-sm text-blue-100 sm:text-base">
                    {{ __('frontend.contact_page.hero.description') }}
                </p>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-4">
                    <x-ui::button tag="a" href="{{ route('tour.index', ['locale' => $locale]) }}" class="bg-white text-slate-950 hover:bg-slate-100">
                        {{ __('frontend.tour.show.breadcrumb_tours') }}
                    </x-ui::button>
                    <x-ui::button tag="a" href="{{ route('contact.index', ['locale' => $locale]) }}" variant="outline" class="border-white/30 text-white hover:bg-white/10">
                        {{ __('frontend.contact_page.title') }}
                    </x-ui::button>
                </div>
            </div>
        </div>
    </section>
</x-layouts::app>