@props([
    'tag' => 'button',
])

<{{$tag}} {{ $attributes->merge([
    'class' => "bg-slate-900 hover:bg-slate-800 text-white px-6 py-2.5 rounded-full text-sm font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900"
]) }}>
    {{ $slot }}
</{{$tag}}>
