@if($faqs->isNotEmpty())
<!-- FAQ Section -->
<section class="py-14 bg-[#F5F3EF]">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10 reveal-fade">
            <div class="w-10 h-0.5 bg-[#89D4CF] mb-3 mx-auto"></div>
            <h2 class="font-display text-3xl sm:text-4xl font-bold text-gray-900 mb-1.5">
                {{ __('frontend.faq.title') }}
            </h2>
            <p class="text-sm text-gray-500">{{ __('frontend.faq.subtitle') }}</p>
        </div>

        <div x-data="{ open: null }" class="reveal-on-scroll space-y-2">
            @foreach($faqs as $faq)
                <div class="border border-[#E8E5FF] rounded-xl overflow-hidden bg-white">
                    <button
                        @click="open = open === {{ $loop->index }} ? null : {{ $loop->index }}"
                        :class="open === {{ $loop->index }} ? 'bg-[#796FE1]/5' : 'bg-white hover:bg-gray-50'"
                        class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left transition-colors">
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
