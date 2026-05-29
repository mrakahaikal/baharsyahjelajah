@if($faqs->isNotEmpty())
<!-- FAQ Section -->
<section class="py-14 bg-white border-t border-gray-100">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1.5">
                {{ __('frontend.faq.title') }}
            </h2>
            <p class="text-sm text-gray-500">{{ __('frontend.faq.subtitle') }}</p>
        </div>

        <div x-data="{ open: null }" class="space-y-2">
            @foreach($faqs as $faq)
                <div class="border border-gray-100 rounded-xl overflow-hidden">
                    <button
                        @click="open = open === {{ $loop->index }} ? null : {{ $loop->index }}"
                        class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left bg-white hover:bg-gray-50 transition-colors">
                        <span class="text-sm font-semibold text-gray-900">{{ $faq->question }}</span>
                        <span class="shrink-0 w-5 h-5 text-gray-400 transition-transform duration-200"
                              :class="open === {{ $loop->index }} ? 'rotate-45' : ''">
                            <x-lucide-plus class="w-5 h-5" />
                        </span>
                    </button>

                    <div x-show="open === {{ $loop->index }}"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="px-5 pb-4">
                        <p class="text-sm text-gray-500 leading-relaxed">{{ $faq->answer }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
