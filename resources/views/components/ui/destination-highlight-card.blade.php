@props(['destination'])

@php
    $destinationImage = $destination->getFirstMedia(
        \App\Models\Destination::MEDIA_COLLECTION_GALLERY,
    );
@endphp

@if($destinationImage)
    <x-ui.lightbox-gallery
        :images="[[
            'src' => $destinationImage->getUrl(),
            'alt' => $destination->name,
            'caption' => $destination->name,
        ]]"
        :alt="$destination->name"
        :label="$destination->name"
        variant="compact"
        {{ $attributes }}
    />
@else
    <figure {{ $attributes->merge(['class' => 'relative aspect-4/3 overflow-hidden rounded-lg bg-slate-900']) }}>
        <div class="absolute inset-0 grid place-items-center bg-slate-800" aria-hidden="true">
            <x-lucide-image class="h-6 w-6 text-slate-500" />
        </div>
        <div class="absolute inset-0 bg-linear-to-t from-slate-950/90 via-slate-950/25 to-transparent" aria-hidden="true"></div>
        <figcaption class="absolute inset-x-0 bottom-0 flex items-center gap-1.5 p-3 text-xs font-bold leading-5 text-white sm:text-sm">
            <x-lucide-map-pin class="h-3.5 w-3.5 shrink-0 text-blue-300" aria-hidden="true" />
            <span class="line-clamp-2">{{ $destination->name }}</span>
        </figcaption>
    </figure>
@endif
