@props([
    'icon' => 'lucide-search-x',
    'title' => 'Data tidak ditemukan',
    'subtitle' => 'Maaf, saat ini belum ada data yang dapat ditampilkan.',
    'buttonText' => null,
    'buttonLink' => '#',
])

<div class="flex flex-col items-center justify-center py-12 px-4 text-center">
    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6">
        <x-dynamic-component :component="$icon" class="w-10 h-10 text-gray-300" />
    </div>
    
    <h3 class="text-xl font-bold text-text-main mb-2 uppercase tracking-tight italic">
        {{ $title }}
    </h3>
    
    <p class="text-gray-500 max-w-sm mb-8 font-medium leading-relaxed">
        {{ $subtitle }}
    </p>

    @if($buttonText)
        <a href="{{ $buttonLink }}" class="inline-flex items-center gap-2 bg-primary hover:bg-deep-contrast text-white font-bold py-3 px-8 rounded-full transition-all duration-300 shadow-lg shadow-primary/20 group">
            {{ $buttonText }}
            <x-lucide-arrow-right class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
        </a>
    @endif
</div>