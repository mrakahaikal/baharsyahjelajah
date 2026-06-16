<!-- Blog Section -->
<section class="py-14 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-8 reveal-fade">
            <div>
                <div class="w-10 h-0.5 bg-[#89D4CF] mb-3"></div>
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-gray-900 mb-1.5">
                    {{ __('frontend.blog.title') }}
                </h2>
                <p class="text-sm text-gray-500">{{ __('frontend.blog.subtitle') }}</p>
            </div>
            @if($latestPosts->isNotEmpty())
                <a href="{{ route('blog.index', ['locale' => app()->getLocale()]) }}"
                   class="shrink-0 text-sm text-primary font-medium hover:underline flex items-center gap-1">
                    {{ __('frontend.blog.view_all') }}
                    <x-lucide-arrow-right class="w-3.5 h-3.5" />
                </a>
            @endif
        </div>

        @if($latestPosts->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($latestPosts as $post)
                    <article data-delay="{{ ($loop->index % 3) + 1 }}"
                             class="reveal-on-scroll group bg-white rounded-xl overflow-hidden border border-gray-100 hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex flex-col">
                        <!-- Image -->
                        <div class="relative h-48 overflow-hidden bg-gray-100">
                            <img src="{{ $post->cover_image_url }}"
                                 alt="{{ $post->title }}"
                                 loading="lazy"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @if($post->category)
                                <span class="absolute top-3 left-3 bg-primary text-white text-[11px] font-semibold px-2.5 py-1 rounded-full shadow-sm">
                                    {{ $post->category->name }}
                                </span>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="p-5 flex flex-col flex-grow">
                            <div class="flex items-center gap-1.5 text-gray-400 text-xs mb-2.5">
                                <x-lucide-calendar class="w-3 h-3" />
                                {{ $post->published_at ? $post->published_at->format('d M Y') : $post->created_at->format('d M Y') }}
                            </div>

                            <h3 class="text-[15px] font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-primary transition-colors">
                                {{ $post->title }}
                            </h3>

                            <p class="text-sm text-gray-500 leading-relaxed line-clamp-2 mb-4 flex-grow">
                                {{ $post->excerpt }}
                            </p>

                            <a href="{{ route('blog.show', ['locale' => app()->getLocale(), 'post' => $post->slug]) }}"
                               class="mt-auto self-start inline-flex items-center gap-1.5 text-xs font-semibold text-primary bg-primary/8 hover:bg-primary hover:text-white px-3 py-1.5 rounded-full transition-colors">
                                {{ __('frontend.blog.read_more') }}
                                <x-lucide-arrow-right class="w-3.5 h-3.5" />
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="bg-gray-50 rounded-xl border border-gray-100 p-12">
                <x-shared.empty-state
                    icon="lucide-pen-tool"
                    :title="__('frontend.blog.empty.title')"
                    :subtitle="__('frontend.blog.empty.subtitle')"
                />
            </div>
        @endif
    </div>
</section>
