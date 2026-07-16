@props([
    'name',
    'parameters' => [],
    'variant' => 'light',
])

@php
    $breadcrumbs = \Diglactic\Breadcrumbs\Breadcrumbs::generate($name, ...$parameters);

    $styles = match ($variant) {
        'dark' => [
            'link' => 'text-white/65 hover:text-white focus-visible:outline-white',
            'current' => 'text-white',
            'separator' => 'text-white/30',
        ],
        'emerald' => [
            'link' => 'text-emerald-100/70 hover:text-white focus-visible:outline-emerald-200',
            'current' => 'text-lime-200',
            'separator' => 'text-emerald-200/35',
        ],
        default => [
            'link' => 'text-slate-500 hover:text-slate-950 focus-visible:outline-blue-600',
            'current' => 'text-slate-950',
            'separator' => 'text-slate-300',
        ],
    };
@endphp

<nav
    aria-label="Breadcrumb"
    data-breadcrumbs="{{ $name }}"
    {{ $attributes->class('text-sm') }}
>
    <ol class="flex min-w-0 items-center gap-1.5 sm:gap-2">
        @foreach($breadcrumbs as $breadcrumb)
            <li class="{{ $loop->last ? 'min-w-0' : 'shrink-0' }}">
                @if(! $loop->last && $breadcrumb->url)
                    <a
                        href="{{ $breadcrumb->url }}"
                        class="rounded-sm transition-colors focus-visible:outline-2 focus-visible:outline-offset-4 {{ $styles['link'] }}"
                    >
                        {{ $breadcrumb->title }}
                    </a>
                @else
                    <span
                        @if($loop->last) aria-current="page" @endif
                        class="block truncate font-medium {{ $styles['current'] }}"
                    >
                        {{ $breadcrumb->title }}
                    </span>
                @endif
            </li>

            @unless($loop->last)
                <li class="shrink-0 {{ $styles['separator'] }}" aria-hidden="true">
                    <x-lucide-chevron-right class="h-3.5 w-3.5" />
                </li>
            @endunless
        @endforeach
    </ol>
</nav>
