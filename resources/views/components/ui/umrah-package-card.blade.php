@props([
    'package',
    'locale' => app()->getLocale(),
    'whatsappNumber' => '',
    'dark' => false,
])

@php
    $departure = $package->upcomingDepartures->first();
    $isLimited = $departure && ($departure->status === 'nearly_full' || $departure->quota_sisa <= 10);
    $message = __('umrah.whatsapp', ['package' => $package->name]);
    $consultUrl = filled($whatsappNumber)
        ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode($message)
        : route('contact.index', ['locale' => $locale]);
    $detailUrl = route('umroh.show', [
        'locale' => $locale,
        'umrah' => $package->slug ?: $package->getKey(),
    ]);
@endphp

<article {{ $attributes->merge(['class' => 'group flex h-full flex-col overflow-hidden rounded-lg border '.($dark ? 'border-white/12 bg-white/6 text-white' : 'border-slate-200 bg-white text-slate-950 shadow-sm')]) }}>
    <div class="relative aspect-[4/3] overflow-hidden bg-neutral-900">
        <img src="{{ $package->thumbnail_url }}" alt="{{ $package->name }}" width="720" height="540" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
        <div class="absolute inset-0 bg-linear-to-t from-black/65 via-transparent to-transparent"></div>
        <span class="absolute left-4 top-4 rounded-full border border-amber-300/30 bg-black/70 px-3 py-1 text-xs font-bold uppercase text-amber-200 backdrop-blur-sm">
            {{ __('umrah.types.'.$package->package_type) }}
        </span>
        <span class="absolute bottom-4 left-4 inline-flex items-center gap-1.5 text-xs font-semibold text-white">
            <x-lucide-moon-star class="h-4 w-4 text-amber-300" aria-hidden="true" />
            {{ __('umrah.card.days', ['count' => $package->duration_days]) }}
        </span>
    </div>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="line-clamp-2 text-lg font-bold">
            <a href="{{ $detailUrl }}" class="transition-colors hover:text-amber-600 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-amber-500">{{ $package->name }}</a>
        </h3>

        <dl class="mt-5 grid gap-3 text-sm">
            <div class="flex items-start justify-between gap-4 border-b pb-3 {{ $dark ? 'border-white/10' : 'border-slate-100' }}">
                <dt class="inline-flex items-center gap-2 {{ $dark ? 'text-neutral-400' : 'text-slate-500' }}">
                    <x-lucide-calendar-days class="h-4 w-4 shrink-0 text-amber-500" aria-hidden="true" />
                    {{ __('umrah.card.departure') }}
                </dt>
                <dd class="text-right font-semibold">
                    @if($departure)
                        <time datetime="{{ $departure->departure_date->toDateString() }}">{{ $departure->departure_date->translatedFormat('d M Y') }}</time>
                    @else
                        {{ __('umrah.card.no_departure') }}
                    @endif
                </dd>
            </div>

            @if($package->airline)
                <div class="flex items-start justify-between gap-4">
                    <dt class="{{ $dark ? 'text-neutral-400' : 'text-slate-500' }}">{{ __('umrah.card.airline') }}</dt>
                    <dd class="text-right font-semibold">{{ $package->airline }}</dd>
                </div>
            @endif

            @if($package->hotel_makkah)
                <div class="flex items-start justify-between gap-4">
                    <dt class="{{ $dark ? 'text-neutral-400' : 'text-slate-500' }}">{{ __('umrah.card.hotel') }}</dt>
                    <dd class="line-clamp-1 text-right font-semibold">{{ $package->hotel_makkah }}</dd>
                </div>
            @endif
        </dl>

        <div class="mt-auto pt-6">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-[11px] font-bold uppercase {{ $dark ? 'text-neutral-400' : 'text-slate-400' }}">{{ __('umrah.card.start_from') }}</p>
                    <p class="mt-1 text-xl font-extrabold {{ $dark ? 'text-amber-300' : 'text-amber-700' }}">{{ $package->formatted_price }}</p>
                </div>
                @if($departure)
                    <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $isLimited ? 'bg-amber-400/15 text-amber-600 '.($dark ? 'text-amber-200' : '') : 'bg-emerald-400/15 text-emerald-700 '.($dark ? 'text-emerald-300' : '') }}">
                        {{ $isLimited ? __('umrah.card.limited') : __('umrah.card.available') }}
                    </span>
                @endif
            </div>

            <x-ui::button tag="a" href="{{ $detailUrl }}" :variant="$dark ? 'gold' : 'primary'" class="mt-5 w-full {{ $dark ? '' : 'hover:bg-amber-700' }}">
                {{ __('umrah.card.details') }}
                <x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon>
            </x-ui::button>

            <a
                href="{{ $consultUrl }}"
                @if(filled($whatsappNumber)) target="_blank" rel="noopener" @endif
                class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-md py-2 text-sm font-bold {{ $dark ? 'text-amber-200 hover:text-white' : 'text-amber-700 hover:text-amber-800' }} focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-500">
                <x-lucide-message-circle class="h-4 w-4" aria-hidden="true" />
                {{ __('umrah.card.consult') }}
            </a>
        </div>
    </div>
</article>
