@props([
    'tag' => 'button',
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'loading' => false,
    'loadingTarget' => null,
    'loadingText' => null,
])

@php
    $tag = in_array($tag, ['a', 'button'], true) ? $tag : 'button';

    $variantClasses = [
        'primary' => 'bg-slate-950 text-white hover:bg-slate-800 focus-visible:outline-slate-950',
        'secondary' => 'bg-blue-600 text-white hover:bg-blue-700 focus-visible:outline-blue-600',
        'outline' => 'border border-slate-300 bg-white text-slate-800 hover:border-slate-400 hover:bg-slate-50 focus-visible:outline-slate-700',
        'soft' => 'bg-blue-50 text-blue-700 hover:bg-blue-100 focus-visible:outline-blue-600',
        'ghost' => 'bg-transparent text-slate-700 hover:bg-slate-100 hover:text-slate-950 focus-visible:outline-slate-600',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus-visible:outline-red-600',
        'inverse' => 'border border-white/35 bg-white/10 text-white hover:bg-white/20 focus-visible:outline-white',
        'light' => 'bg-white text-blue-700 hover:bg-blue-50 focus-visible:outline-white',
        'gold' => 'bg-amber-400 text-neutral-950 hover:bg-amber-300 focus-visible:outline-amber-300',
        'gold-outline' => 'border border-amber-300/50 bg-transparent text-amber-200 hover:bg-amber-300 hover:text-neutral-950 focus-visible:outline-amber-300',
    ];

    $sizeClasses = [
        'sm' => 'min-h-9 gap-1.5 px-3.5 text-xs',
        'md' => 'min-h-11 gap-2 px-5 text-sm',
        'lg' => 'min-h-12 gap-2.5 px-6 text-base',
        'icon' => 'size-11 justify-center p-0 text-sm',
    ];

    $variantClass = $variantClasses[$variant] ?? $variantClasses['primary'];
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
    $wireTarget = $loadingTarget ?: $attributes->get('wire:click');
    $showsLoadingState = $tag === 'button' && ($loading || filled($wireTarget));

    $buttonAttributes = $attributes->class([
        'inline-flex shrink-0 items-center justify-center rounded-lg font-semibold whitespace-nowrap transition-[background-color,border-color,color,box-shadow,transform] duration-200',
        'focus-visible:outline-2 focus-visible:outline-offset-2',
        'disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-60',
        $variantClass,
        $sizeClass,
    ]);

    if ($tag === 'button') {
        $buttonAttributes = $buttonAttributes->merge(['type' => $type]);

        if ($showsLoadingState) {
            $buttonAttributes = $buttonAttributes->merge([
                'wire:loading.attr' => 'disabled',
            ]);
        }
    }
@endphp

<{{ $tag }}
    {{ $buttonAttributes }}
    @if($showsLoadingState && filled($wireTarget)) wire:target="{{ $wireTarget }}" @endif
>
    @if($showsLoadingState)
        <span class="inline-flex min-w-0 items-center justify-center gap-[inherit]" wire:loading.remove @if(filled($wireTarget)) wire:target="{{ $wireTarget }}" @endif>
            @isset($icon)
                <span class="shrink-0 [&>svg]:size-[1.1em]" aria-hidden="true">{{ $icon }}</span>
            @endisset
            <span class="min-w-0">{{ $slot }}</span>
            @isset($trailingIcon)
                <span class="shrink-0 [&>svg]:size-[1.1em]" aria-hidden="true">{{ $trailingIcon }}</span>
            @endisset
        </span>

        <span class="hidden min-w-0 items-center justify-center gap-2" role="status" wire:loading.flex @if(filled($wireTarget)) wire:target="{{ $wireTarget }}" @endif>
            <x-lucide-loader-circle class="size-[1.1em] shrink-0 animate-spin motion-reduce:animate-none" aria-hidden="true" />
            <span class="min-w-0">{{ $loadingText ?: $slot }}</span>
        </span>
    @else
        @isset($icon)
            <span class="shrink-0 [&>svg]:size-[1.1em]" aria-hidden="true">{{ $icon }}</span>
        @endisset
        <span class="min-w-0">{{ $slot }}</span>
        @isset($trailingIcon)
            <span class="shrink-0 [&>svg]:size-[1.1em]" aria-hidden="true">{{ $trailingIcon }}</span>
        @endisset
    @endif
</{{ $tag }}>
