@php
    $locale = app()->getLocale();
@endphp

<x-layouts::app
    :title="__('home.seo.title')"
    :meta-description="__('home.seo.description')"
    :show-floating-whatsapp="false"
    :$schemaJson
    :$canonicalUrl
    :$alternateUrls>
    <x-partials.home.hero-section :$locale :$heroBanner />
    <x-partials.home.search-panel :$locale />

    <section class="border-b border-slate-200 bg-white pt-24 sm:pt-28" aria-label="{{ __('home.hero.eyebrow') }}">
        <div class="mx-auto grid max-w-7xl gap-px bg-slate-200 px-4 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
            @foreach(__('home.trust') as $item)
                <div class="flex min-w-0 gap-3 bg-white py-6 sm:px-4 lg:px-5">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-blue-50 text-blue-600">
                        @if($loop->index === 0)
                            <x-lucide-route class="h-4.5 w-4.5" aria-hidden="true" />
                        @elseif($loop->index === 1)
                            <x-lucide-receipt-text class="h-4.5 w-4.5" aria-hidden="true" />
                        @elseif($loop->index === 2)
                            <x-lucide-moon-star class="h-4.5 w-4.5" aria-hidden="true" />
                        @else
                            <x-lucide-headphones class="h-4.5 w-4.5" aria-hidden="true" />
                        @endif
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-sm font-bold text-slate-950">{{ $item['title'] }}</h2>
                        <p class="mt-1 text-xs leading-5 text-slate-500">{{ $item['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="bg-slate-50 py-16 sm:py-20" aria-labelledby="featured-tours-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-2xl">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">{{ __('home.featured.eyebrow') }}</p>
                    <h2 id="featured-tours-heading" class="mt-3 text-balance text-3xl font-extrabold text-slate-950 sm:text-4xl">{{ __('home.featured.title') }}</h2>
                    <p class="mt-4 text-sm leading-7 text-slate-500">{{ __('home.featured.subtitle') }}</p>
                </div>
                <x-ui::button tag="a" href="{{ route('tour.index', ['locale' => $locale]) }}" variant="outline" class="w-full hover:border-blue-600 hover:text-blue-600 sm:w-auto">
                    {{ __('home.featured.view_all') }}
                    <x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon>
                </x-ui::button>
            </div>

            @if($featuredTours->isNotEmpty())
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($featuredTours as $tour)
                        <x-ui.tour-card :$tour :$locale />
                    @endforeach
                </div>
            @else
                <div class="mt-10 border-y border-slate-200 py-12 text-center">
                    <h3 class="font-bold text-slate-950">{{ __('frontend.featured_tour.empty.title') }}</h3>
                    <p class="mt-2 text-sm text-slate-500">{{ __('frontend.featured_tour.empty.subtitle') }}</p>
                </div>
            @endif
        </div>
    </section>

    <x-partials.home.featured-vehicles :vehicles="$featuredVehicles" :$locale />

    @if($promoBanner)
        <x-partials.home.promo-banner :banner="$promoBanner" :$locale />
    @endif

    <section class="relative overflow-hidden bg-neutral-950 py-16 text-white sm:py-20" aria-labelledby="featured-umrah-heading">
        <div class="absolute inset-x-0 top-0 h-px bg-linear-to-r from-transparent via-amber-300/70 to-transparent"></div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:items-end">
                <div class="max-w-2xl">
                    <p class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-amber-300">
                        <x-lucide-moon-star class="h-4 w-4" aria-hidden="true" />
                        {{ __('home.umrah.eyebrow') }}
                    </p>
                    <h2 id="featured-umrah-heading" class="mt-4 text-balance text-3xl font-extrabold sm:text-4xl">{{ __('home.umrah.title') }}</h2>
                </div>
                <div class="lg:justify-self-end">
                    <p class="max-w-2xl text-sm leading-7 text-neutral-300">{{ __('home.umrah.subtitle') }}</p>
                    <x-ui::button tag="a" href="{{ route('umroh.index', ['locale' => $locale]) }}" variant="gold-outline" class="mt-5">
                        {{ __('home.umrah.view_all') }}
                        <x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon>
                    </x-ui::button>
                </div>
            </div>

            @if($featuredUmrahPackages->isNotEmpty())
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($featuredUmrahPackages as $package)
                        <x-ui.umrah-package-card :$package :$locale :$whatsappNumber dark />
                    @endforeach
                </div>
            @else
                <div class="mt-10 border-y border-white/10 py-10 text-sm leading-7 text-neutral-300">
                    {{ __('home.umrah.empty') }}
                </div>
            @endif
        </div>
    </section>

    <x-partials.home.featured-visa-services :services="$featuredVisaServices" :$locale />

    <section class="bg-white py-16 sm:py-20" aria-labelledby="planning-heading">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-start lg:px-8">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">{{ __('home.planning.eyebrow') }}</p>
                <h2 id="planning-heading" class="mt-3 max-w-2xl text-balance text-3xl font-extrabold text-slate-950 sm:text-4xl">{{ __('home.planning.title') }}</h2>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-500">{{ __('home.planning.subtitle') }}</p>

                <div class="mt-8 divide-y divide-slate-200 border-y border-slate-200">
                    @foreach(__('home.planning.benefits') as $benefit)
                        <div class="grid gap-2 py-5 sm:grid-cols-[0.7fr_1.3fr] sm:gap-6">
                            <h3 class="font-bold text-slate-950">{{ $benefit['title'] }}</h3>
                            <p class="text-sm leading-6 text-slate-500">{{ $benefit['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-slate-950 text-white">
                <div class="relative h-52 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1524492412937-b28074a5d7da?auto=format&fit=crop&w=1000&q=85" alt="" width="1000" height="600" loading="lazy" class="h-full w-full object-cover opacity-45">
                    <div class="absolute inset-0 bg-linear-to-t from-slate-950 to-transparent"></div>
                    <h3 class="absolute inset-x-6 bottom-6 text-2xl font-extrabold">{{ __('home.planning.steps_title') }}</h3>
                </div>
                <ol class="divide-y divide-white/10 px-6 pb-3">
                    @foreach(__('home.planning.steps') as $step)
                        <li class="flex gap-4 py-5">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full border border-blue-300/40 bg-blue-400/10 text-xs font-bold text-blue-200">{{ $loop->iteration }}</span>
                            <div>
                                <h4 class="font-bold">{{ $step['title'] }}</h4>
                                <p class="mt-1 text-sm leading-6 text-slate-300">{{ $step['text'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    @if($testimonials->isNotEmpty())
        <section class="bg-slate-50 py-16 sm:py-20" aria-labelledby="testimonials-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">{{ __('home.testimonials.eyebrow') }}</p>
                        <h2 id="testimonials-heading" class="mt-3 text-balance text-3xl font-extrabold text-slate-950 sm:text-4xl">{{ __('home.testimonials.title') }}</h2>
                        <p class="mt-4 text-sm leading-7 text-slate-500">{{ __('home.testimonials.subtitle') }}</p>
                    </div>
                    <div class="hidden gap-2 sm:flex">
                        <x-ui::button variant="outline" size="icon" aria-label="{{ __('home.testimonials.previous') }}" class="swiper-button-prev static! m-0! hover:border-blue-600 after:text-sm! after:font-bold! after:text-slate-700!" />
                        <x-ui::button variant="outline" size="icon" aria-label="{{ __('home.testimonials.next') }}" class="swiper-button-next static! m-0! hover:border-blue-600 after:text-sm! after:font-bold! after:text-slate-700!" />
                    </div>
                </div>

                <div class="swiper js-testimonial-swiper mt-10 pb-10">
                    <div class="swiper-wrapper">
                        @foreach($testimonials as $testimonial)
                            <div class="swiper-slide h-auto">
                                <figure class="flex h-full flex-col rounded-lg border border-slate-200 bg-white p-6">
                                    <div class="text-sm tracking-wider text-amber-500" aria-label="{{ $testimonial->rating }} / 5">{{ $testimonial->stars }}</div>
                                    <blockquote class="mt-5 flex-1 text-pretty text-base leading-7 text-slate-600">“{{ $testimonial->content }}”</blockquote>
                                    <figcaption class="mt-6 border-t border-slate-100 pt-5">
                                        <p class="font-bold text-slate-950">{{ $testimonial->reviewer_name }}</p>
                                        @if($testimonial->reviewer_country)
                                            <p class="mt-1 text-xs text-slate-500">{{ $testimonial->reviewer_country }}</p>
                                        @endif
                                    </figcaption>
                                </figure>
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination text-blue-600"></div>
                </div>
            </div>
        </section>
    @endif

    <section class="bg-white py-16 sm:py-20" aria-labelledby="blog-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-2xl">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">{{ __('home.blog.eyebrow') }}</p>
                    <h2 id="blog-heading" class="mt-3 text-balance text-3xl font-extrabold text-slate-950 sm:text-4xl">{{ __('home.blog.title') }}</h2>
                    <p class="mt-4 text-sm leading-7 text-slate-500">{{ __('home.blog.subtitle') }}</p>
                </div>
                <x-ui::button tag="a" href="{{ route('blog.index', ['locale' => $locale]) }}" variant="outline" class="w-full hover:border-blue-600 hover:text-blue-600 sm:w-auto">
                    {{ __('home.blog.view_all') }}
                    <x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon>
                </x-ui::button>
            </div>

            @if($latestPosts->isNotEmpty())
                <div class="mt-10 grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                    <x-ui.post-card :post="$latestPosts->first()" :$locale image-height="h-64 sm:h-80" featured />
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-1">
                        @foreach($latestPosts->skip(1) as $post)
                            <x-ui.post-card :$post :$locale image-height="h-44" />
                        @endforeach
                    </div>
                </div>
            @else
                <div class="mt-10 border-y border-slate-200 py-12 text-center">
                    <h3 class="font-bold text-slate-950">{{ __('frontend.blog.empty.title') }}</h3>
                    <p class="mt-2 text-sm text-slate-500">{{ __('frontend.blog.empty.subtitle') }}</p>
                </div>
            @endif
        </div>
    </section>

    <x-partials.home.faq-section :$faqs />

    <section class="bg-blue-600 py-14 text-white" aria-labelledby="final-cta-heading">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[1fr_auto] lg:items-center lg:px-8">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-100">{{ __('home.cta.eyebrow') }}</p>
                <h2 id="final-cta-heading" class="mt-3 text-balance text-3xl font-extrabold sm:text-4xl">{{ __('home.cta.title') }}</h2>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-blue-100">{{ __('home.cta.subtitle') }}</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                @if(filled($whatsappNumber))
                    <x-ui::button tag="a" href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener" variant="light" size="lg">
                        <x-slot:icon><x-lucide-message-circle /></x-slot:icon>
                        {{ __('home.cta.whatsapp') }}
                    </x-ui::button>
                @endif
                <x-ui::button tag="a" href="{{ route('contact.index', ['locale' => $locale]) }}" variant="inverse" size="lg" class="bg-blue-700 hover:bg-blue-800">
                    {{ __('home.cta.consult') }}
                    <x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon>
                </x-ui::button>
            </div>
        </div>
    </section>
</x-layouts::app>
