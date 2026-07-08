@php
    $locale = app()->getLocale();
    $generalSettings = app(\App\Settings\GeneralSettings::class);
    $footerSettings = app(\App\Settings\FooterSettings::class);
    $socialSettings = app(\App\Settings\SocialSettings::class);

    $localized = function (mixed $value) use ($locale): string {
        if (is_array($value)) {
            return $value[$locale] ?? $value['id'] ?? $value['en'] ?? (string) collect($value)->filter()->first();
        }

        return (string) $value;
    };

    $linkUrl = function (array $link) use ($locale): ?string {
        $manualUrl = $link['url'] ?? null;

        if (filled($manualUrl)) {
            return $manualUrl;
        }

        $routeName = $link['route'] ?? null;

        if (! filled($routeName) || ! \Illuminate\Support\Facades\Route::has($routeName)) {
            return null;
        }

        $url = route($routeName, ['locale' => $locale]);
        $fragment = $link['fragment'] ?? null;

        return filled($fragment) ? $url.'#'.ltrim($fragment, '#') : $url;
    };

    $socialUrl = function (?string $value, string $platform): ?string {
        if (! filled($value)) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        $handle = ltrim($value, '@');

        return match ($platform) {
            'instagram' => 'https://instagram.com/'.$handle,
            'facebook' => 'https://facebook.com/'.$handle,
            'tiktok' => 'https://tiktok.com/@'.$handle,
            'youtube' => 'https://youtube.com/@'.$handle,
            default => null,
        };
    };

    $contactItems = [
        [
            'label' => 'WhatsApp',
            'value' => $generalSettings->whatsapp_number ? '+'.$generalSettings->whatsapp_number : null,
            'url' => $generalSettings->whatsapp_number ? 'https://wa.me/'.$generalSettings->whatsapp_number : null,
            'icon' => 'message',
        ],
        [
            'label' => 'Email',
            'value' => $generalSettings->email,
            'url' => filled($generalSettings->email) ? 'mailto:'.$generalSettings->email : null,
            'icon' => 'mail',
        ],
    ];

    $address = $localized($generalSettings->address);
    $ctaUrl = $linkUrl(['route' => $footerSettings->cta_button_route]);
    $socialLinks = [
        ['label' => 'Instagram', 'url' => $socialUrl($socialSettings->instagram, 'instagram'), 'icon' => 'instagram'],
        ['label' => 'Facebook', 'url' => $socialUrl($socialSettings->facebook, 'facebook'), 'icon' => 'facebook'],
        ['label' => 'TikTok', 'url' => $socialUrl($socialSettings->tiktok, 'tiktok'), 'icon' => 'music'],
        ['label' => 'YouTube', 'url' => $socialUrl($socialSettings->youtube, 'youtube'), 'icon' => 'youtube'],
    ];
@endphp

<footer class="border-t border-slate-800 bg-brand-bg-dark text-white">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <div class="grid gap-8 border-b border-white/10 pb-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-end">
            <div>
                <a href="{{ route('home', ['locale' => $locale]) }}" class="inline-flex rounded-md focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-400" aria-label="Baharsyah Jelajah Home">
                    <img src="{{ asset('images/logo-baharsyah-jelajah.webp') }}" alt="Baharsyah Jelajah" width="176" height="44" class="h-11 w-auto brightness-0 invert">
                </a>
                <p class="mt-5 max-w-2xl text-sm leading-7 text-slate-300">
                    {{ $localized($footerSettings->brand_description) }}
                </p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/4 p-6">
                <p class="text-lg font-bold text-white">{{ $localized($footerSettings->cta_title) }}</p>
                <p class="mt-2 text-sm leading-6 text-slate-300">{{ $localized($footerSettings->cta_subtitle) }}</p>

                @if($ctaUrl)
                    <a href="{{ $ctaUrl }}" class="mt-5 inline-flex items-center justify-center gap-2 rounded-full bg-white px-5 py-3 text-sm font-bold text-slate-950 transition-colors hover:bg-blue-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-400">
                        {{ $localized($footerSettings->cta_button_label) }}
                        <x-lucide-arrow-right class="h-4 w-4" aria-hidden="true" />
                    </a>
                @endif
            </div>
        </div>

        <div class="grid gap-10 py-10 md:grid-cols-2 lg:grid-cols-[1.1fr_1fr_1fr_1fr]">
            <div>
                <h2 class="text-sm font-bold text-white">{{ $localized($footerSettings->subscribe_title) }}</h2>
                <p class="mt-3 text-sm leading-7 text-slate-400">{{ $localized($footerSettings->subscribe_subtitle) }}</p>

                <div class="mt-6 flex flex-wrap gap-3">
                    @foreach($socialLinks as $socialLink)
                        @if($socialLink['url'])
                            <a href="{{ $socialLink['url'] }}" target="_blank" rel="noopener" class="grid h-10 w-10 place-items-center rounded-full bg-white/10 text-slate-200 transition-colors hover:bg-white/20 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-400">
                                <span class="sr-only">{{ $socialLink['label'] }}</span>
                                @if($socialLink['icon'] === 'instagram')
                                    <x-lucide-instagram class="h-4 w-4" aria-hidden="true" />
                                @elseif($socialLink['icon'] === 'facebook')
                                    <x-lucide-facebook class="h-4 w-4" aria-hidden="true" />
                                @elseif($socialLink['icon'] === 'youtube')
                                    <x-lucide-youtube class="h-4 w-4" aria-hidden="true" />
                                @else
                                    <x-lucide-music-2 class="h-4 w-4" aria-hidden="true" />
                                @endif
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            @foreach($footerSettings->link_groups as $group)
                <nav aria-labelledby="footer-link-group-{{ $loop->index }}">
                    <h2 id="footer-link-group-{{ $loop->index }}" class="text-sm font-bold text-white">
                        {{ $localized($group['title'] ?? '') }}
                    </h2>
                    <ul class="mt-5 flex flex-col gap-3">
                        @foreach($group['links'] ?? [] as $link)
                            @php $url = $linkUrl($link); @endphp
                            @if($url)
                                <li>
                                    <a href="{{ $url }}" class="text-sm text-slate-400 transition-colors hover:text-white focus-visible:rounded-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-400">
                                        {{ $localized($link['label'] ?? '') }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </nav>
            @endforeach

            <div>
                <h2 class="text-sm font-bold text-white">{{ $locale === 'en' ? 'Contact' : 'Kontak' }}</h2>
                <ul class="mt-5 flex flex-col gap-4">
                    @foreach($contactItems as $item)
                        @if($item['value'])
                            <li>
                                <a href="{{ $item['url'] }}" @if(str_starts_with($item['url'] ?? '', 'https://wa.me/')) target="_blank" rel="noopener" @endif class="group flex items-start gap-3 text-sm leading-6 text-slate-400 transition-colors hover:text-white focus-visible:rounded-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-400">
                                    @if($item['icon'] === 'message')
                                        <x-lucide-message-circle class="mt-0.5 h-4 w-4 shrink-0 text-blue-300" aria-hidden="true" />
                                    @else
                                        <x-lucide-mail class="mt-0.5 h-4 w-4 shrink-0 text-blue-300" aria-hidden="true" />
                                    @endif
                                    <span>{{ $item['value'] }}</span>
                                </a>
                            </li>
                        @endif
                    @endforeach

                    @if(filled($address))
                        <li class="flex items-start gap-3 text-sm leading-6 text-slate-400">
                            <x-lucide-map-pin class="mt-0.5 h-4 w-4 shrink-0 text-blue-300" aria-hidden="true" />
                            <span>{{ $address }}</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="flex flex-col gap-4 border-t border-white/10 pt-6 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
            <p>&copy; {{ date('Y') }} {{ $localized($footerSettings->copyright_text) }}</p>

            @if($footerSettings->legal_links)
                <nav aria-label="Footer legal links">
                    <ul class="flex flex-wrap gap-x-5 gap-y-2">
                        @foreach($footerSettings->legal_links as $link)
                            @php $url = $linkUrl($link); @endphp
                            @if($url)
                                <li>
                                    <a href="{{ $url }}" class="transition-colors hover:text-slate-300 focus-visible:rounded-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-400">
                                        {{ $localized($link['label'] ?? '') }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </nav>
            @endif
        </div>
    </div>
</footer>
