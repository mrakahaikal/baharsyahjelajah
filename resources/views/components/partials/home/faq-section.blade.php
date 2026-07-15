@props(['faqs'])

@if($faqs->isNotEmpty())
    <section id="faq" class="scroll-mt-24 bg-slate-50 py-16 sm:py-20" aria-labelledby="faq-heading">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.75fr_1.25fr] lg:gap-14 lg:px-8">
            <div class="lg:sticky lg:top-28 lg:self-start">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">{{ __('home.faq.eyebrow') }}</p>
                <h2 id="faq-heading" class="mt-3 text-balance text-3xl font-extrabold text-slate-950 sm:text-4xl">{{ __('home.faq.title') }}</h2>
                <p class="mt-4 max-w-lg text-sm leading-7 text-slate-500">{{ __('home.faq.subtitle') }}</p>
            </div>

            <div class="divide-y divide-slate-200 border-y border-slate-200">
                @foreach($faqs as $faq)
                    <details name="home-faq" class="group py-5 sm:py-6">
                        <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-4 font-bold leading-6 text-slate-950 transition-colors hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600">
                            <span>{{ $faq->question }}</span>
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white text-slate-500 shadow-sm ring-1 ring-slate-200 transition group-open:bg-blue-600 group-open:text-white group-open:ring-blue-600">
                                <x-lucide-plus class="h-4 w-4 transition-transform duration-200 group-open:rotate-45" aria-hidden="true" />
                            </span>
                        </summary>
                        <p class="max-w-3xl pt-4 pr-12 text-sm leading-7 text-slate-600">{{ $faq->answer }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
@endif
