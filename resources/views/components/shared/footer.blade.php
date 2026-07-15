<footer id="site-footer" class="scroll-mt-20 border-t border-slate-800 bg-brand-bg-dark text-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-6 border-b border-white/10 py-10 md:grid-cols-[minmax(0,1fr)_auto] md:items-center lg:py-12">
            <div class="max-w-2xl">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-300">Baharsyah Jelajah</p>
                <h2 class="mt-3 text-balance text-2xl font-extrabold text-white sm:text-3xl">{{ $ctaTitle }}</h2>
                <p class="mt-3 text-sm leading-7 text-slate-300">{{ $ctaDescription }}</p>
            </div>

            @if($ctaUrl)
                <x-ui::button tag="a" :href="$ctaUrl" variant="light" size="lg" class="w-full md:w-auto">
                    {{ $ctaLabel }}
                    <x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon>
                </x-ui::button>
            @endif
        </div>

        <div class="grid gap-12 py-12 lg:grid-cols-[minmax(16rem,0.8fr)_minmax(0,1.6fr)] lg:gap-16 lg:py-16">
            <div class="flex flex-col gap-9">
                <div>
                    <a href="{{ route('home', ['locale' => $locale]) }}" class="inline-flex rounded-md focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-400" aria-label="Baharsyah Jelajah">
                        <x-shared::application-logo inverted />
                    </a>
                    <p class="mt-5 max-w-md text-sm leading-7 text-slate-300">{{ $brandDescription }}</p>
                </div>

                @if($contactItems)
                    <div>
                        <h2 class="text-sm font-bold text-white">{{ $contactTitle }}</h2>
                        <ul class="mt-4 flex flex-col gap-3">
                            @foreach($contactItems as $item)
                                <li class="flex min-w-0 items-start gap-3 text-sm leading-6 text-slate-300">
                                    @if($item['icon'] === 'message')
                                        <x-lucide-message-circle class="mt-0.5 size-4 shrink-0 text-blue-300" aria-hidden="true" />
                                    @elseif($item['icon'] === 'mail')
                                        <x-lucide-mail class="mt-0.5 size-4 shrink-0 text-blue-300" aria-hidden="true" />
                                    @else
                                        <x-lucide-map-pin class="mt-0.5 size-4 shrink-0 text-blue-300" aria-hidden="true" />
                                    @endif

                                    @if(filled($item['url']))
                                        <a href="{{ $item['url'] }}" @if($item['external']) target="_blank" rel="noopener" @endif class="min-w-0 break-words transition-colors hover:text-white focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-400">
                                            {{ $item['value'] }}
                                        </a>
                                    @else
                                        <span class="min-w-0 break-words">{{ $item['value'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="grid gap-x-8 gap-y-10 sm:grid-cols-2 xl:grid-cols-4">
                @foreach($linkGroups as $group)
                    <nav aria-labelledby="footer-link-group-{{ $loop->index }}">
                        <h2 id="footer-link-group-{{ $loop->index }}" class="text-sm font-bold text-white">{{ $group['title'] }}</h2>
                        <ul class="mt-5 flex flex-col gap-3.5">
                            @foreach($group['links'] as $link)
                                <li>
                                    <a href="{{ $link['url'] }}" class="inline-flex items-start gap-2 text-sm leading-6 text-slate-400 transition-colors hover:text-white focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-400">
                                        <span>{{ $link['label'] }}</span>
                                        @if($group['source'] === 'destinations' && $loop->last)
                                            <x-lucide-arrow-up-right class="mt-1 size-3.5 shrink-0" aria-hidden="true" />
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endforeach
            </div>
        </div>

        @if($socialLinks)
            <div class="grid gap-6 border-t border-white/10 py-8 md:grid-cols-[minmax(0,1fr)_auto] md:items-center">
                <div class="max-w-xl">
                    <h2 class="text-sm font-bold text-white">{{ $socialTitle }}</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-400">{{ $socialDescription }}</p>
                </div>

                <ul class="flex flex-wrap gap-2" aria-label="Media sosial Baharsyah Jelajah">
                    @foreach($socialLinks as $socialLink)
                        <li>
                            <a href="{{ $socialLink['url'] }}" target="_blank" rel="noopener" class="grid size-11 place-items-center rounded-md border border-white/15 text-slate-300 transition-[background-color,border-color,color] hover:border-white/30 hover:bg-white/10 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-400">
                                <span class="sr-only">{{ $socialLink['label'] }}</span>
                                @if($socialLink['icon'] === 'instagram')
                                    <x-lucide-instagram class="size-4" aria-hidden="true" />
                                @elseif($socialLink['icon'] === 'facebook')
                                    <x-lucide-facebook class="size-4" aria-hidden="true" />
                                @elseif($socialLink['icon'] === 'youtube')
                                    <x-lucide-youtube class="size-4" aria-hidden="true" />
                                @else
                                    <x-lucide-music-2 class="size-4" aria-hidden="true" />
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-col gap-4 border-t border-white/10 py-6 text-xs leading-5 text-slate-500 md:flex-row md:items-center md:justify-between">
            <p>&copy; {{ now()->year }} {{ $copyrightText }}</p>

            @if($legalLinks)
                <nav aria-label="Legal">
                    <ul class="flex flex-wrap gap-x-5 gap-y-2">
                        @foreach($legalLinks as $link)
                            <li>
                                <a href="{{ $link['url'] }}" class="transition-colors hover:text-slate-300 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-400">{{ $link['label'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            @endif
        </div>
    </div>
</footer>
