<!-- Testimonials Section -->
<section class="py-14 bg-[#796FE1]/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-8 reveal-fade">
            <div>
                <div class="w-10 h-0.5 bg-[#89D4CF] mb-3"></div>
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-gray-900 mb-1.5">
                    {{ __('frontend.testimonials.title') }}
                </h2>
                <p class="text-sm text-gray-500">{{ __('frontend.testimonials.subtitle') }}</p>
            </div>
            @if($testimonials->isNotEmpty())
                <a href="{{ route('testimonials.index', ['locale' => app()->getLocale()]) }}"
                   class="shrink-0 text-sm text-primary font-medium hover:underline flex items-center gap-1">
                    {{ __('frontend.testimonials.view_all') }}
                    <x-lucide-arrow-right class="w-3.5 h-3.5" />
                </a>
            @endif
        </div>

        @if($testimonials->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($testimonials as $testimonial)
                    <div data-delay="{{ ($loop->index % 3) + 1 }}"
                         class="reveal-on-scroll bg-white rounded-xl p-6 border border-gray-100 flex flex-col hover:shadow-md transition-shadow duration-300">
                        <!-- Stars -->
                        <div class="flex gap-0.5 mb-4">
                            @for($i = 0; $i < ($testimonial->rating ?? 5); $i++)
                                <x-lucide-star class="w-3.5 h-3.5 fill-amber-400 text-amber-400" />
                            @endfor
                        </div>

                        <blockquote class="flex-grow relative">
                            <span class="absolute -top-4 -left-1 font-display text-5xl leading-none text-[#796FE1]/20 select-none" aria-hidden="true">&ldquo;</span>
                            <p class="relative text-sm text-gray-600 leading-relaxed mb-5">
                                {{ $testimonial->content }}
                            </p>
                        </blockquote>

                        <div class="flex items-center gap-3 pt-4 border-t border-gray-50">
                            <div class="w-9 h-9 rounded-full overflow-hidden bg-gray-100 shrink-0">
                                <img src="{{ $testimonial->avatar ? asset('storage/' . $testimonial->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($testimonial->name) . '&color=796FE1&background=EDE9FF&bold=true&size=64' }}"
                                     alt="{{ $testimonial->name }}"
                                     loading="lazy"
                                     class="w-full h-full object-cover">
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $testimonial->name }}</p>
                                @if($testimonial->location)
                                    <p class="text-xs text-gray-400">{{ $testimonial->location }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-100 p-12">
                <x-shared.empty-state
                    icon="lucide-message-square"
                    :title="__('frontend.testimonials.empty.title')"
                    :subtitle="__('frontend.testimonials.empty.subtitle')"
                />
            </div>
        @endif

        {{-- Video Highlight — Instagram & TikTok embeds.
             TODO: ganti URL placeholder di bawah dengan URL postingan asli. --}}
        @php
            $videoHighlights = [
                ['type' => 'instagram', 'url' => 'https://www.instagram.com/p/C8Qw1aBxYzA/'],
                ['type' => 'tiktok', 'url' => 'https://www.tiktok.com/@baharsyah.jelajah/video/7300000000000000000', 'id' => '7300000000000000000'],
                ['type' => 'instagram', 'url' => 'https://www.instagram.com/reel/C8Rt2cDxYzB/'],
            ];
        @endphp

        <div class="mt-16">
            <div class="text-center mb-8 reveal-fade">
                <div class="w-10 h-0.5 bg-[#89D4CF] mb-3 mx-auto"></div>
                <h3 class="font-display text-2xl sm:text-3xl font-bold text-gray-900 mb-1.5">
                    {{ __('frontend.testimonials.video_title') }}
                </h3>
                <p class="text-sm text-gray-500 max-w-xl mx-auto">
                    {{ __('frontend.testimonials.video_subtitle') }}
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 justify-items-center">
                @foreach($videoHighlights as $i => $video)
                    <div class="reveal-on-scroll w-full max-w-[340px] flex justify-center" data-delay="{{ ($i % 3) + 1 }}">
                        @if($video['type'] === 'instagram')
                            <blockquote class="instagram-media"
                                        data-instgrm-permalink="{{ $video['url'] }}"
                                        data-instgrm-version="14"
                                        style="background:#fff; border:0; border-radius:12px; margin:0; max-width:340px; width:100%;"></blockquote>
                        @else
                            <blockquote class="tiktok-embed"
                                        cite="{{ $video['url'] }}"
                                        data-video-id="{{ $video['id'] }}"
                                        style="max-width:340px; min-width:280px; margin:0;">
                                <section></section>
                            </blockquote>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

@once
    @push('scripts')
        <script async src="//www.instagram.com/embed.js"></script>
        <script async src="https://www.tiktok.com/embed.js"></script>
    @endpush
@endonce
