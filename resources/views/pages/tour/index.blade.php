<x-layouts::app>
    @php
        $locale = app()->getLocale();
        $activeCategory = request('category');
    @endphp

    <!-- Breadcrumb & Header Section -->
    <div class="bg-slate-50 border-b border-slate-100 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex text-sm text-slate-500 mb-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li><a href="{{ route('home', ['locale' => $locale]) }}" class="hover:text-slate-900 transition-colors">{{ $locale === 'id' ? 'Beranda' : ($locale === 'ms' ? 'Utama' : 'Home') }}</a></li>
                    <li><span>/</span></li>
                    <li class="text-slate-900 font-medium" aria-current="page">{{ $locale === 'id' ? 'Paket Wisata Halal' : ($locale === 'ms' ? 'Pakej Pelancongan' : 'Halal Tour Packages') }}</li>
                </ol>
            </nav>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight sm:text-4xl">
                {{ $locale === 'id' ? 'Paket Tour Wisata Halal' : ($locale === 'ms' ? 'Pakej Pelancongan Halal' : 'Halal Tour Packages') }}
            </h1>
            <p class="mt-2 text-sm text-slate-500 max-w-2xl leading-relaxed">
                {{ $locale === 'id' ? 'Temukan rute perjalanan wisata terbaik dengan layanan profesional terkurasi bersama Baharsyah Jelajah.' : ($locale === 'ms' ? 'Temui laluan pelancongan terbaik dengan perkhidmatan terurus profesional bersama Baharsyah Jelajah.' : 'Find the best tour routes with curated professional services with Baharsyah Jelajah.') }}
            </p>
        </div>
    </div>

    <!-- Main Content Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Sidebar Filters (col-span-3) -->
            <aside class="col-span-1 lg:col-span-3 space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">
                        {{ $locale === 'id' ? 'Kategori & Destinasi' : ($locale === 'ms' ? 'Kategori & Destinasi' : 'Categories & Destinations') }}
                    </h2>
                    <div class="space-y-1.5">
                        <a href="{{ route('tour.index', ['locale' => $locale]) }}" 
                           class="flex items-center justify-between rounded-xl px-3.5 py-2.5 text-xs font-bold transition-all duration-200 {{ !$activeCategory ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <span>{{ $locale === 'id' ? 'Semua Paket' : ($locale === 'ms' ? 'Semua Pakej' : 'All Packages') }}</span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] {{ !$activeCategory ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-500' }}">
                                {{ \App\Models\Tour::active()->count() }}
                            </span>
                        </a>
                        
                        @foreach($categories as $cat)
                            @php 
                                $isCurrent = $activeCategory === $cat->slug || $activeCategory == $cat->id; 
                            @endphp
                            <a href="{{ route('tour.index', ['locale' => $locale, 'category' => $cat->slug]) }}" 
                               class="flex items-center justify-between rounded-xl px-3.5 py-2.5 text-xs font-bold transition-all duration-200 {{ $isCurrent ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <span>{{ $cat->name }}</span>
                                <span class="rounded-full px-2 py-0.5 text-[10px] {{ $isCurrent ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $cat->active_tours_count }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>

            <!-- Tours Grid Section (col-span-9) -->
            <main class="col-span-1 lg:col-span-9">
                @if($tours->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($tours as $tour)
                            <x-ui.tour-card :$tour :$locale />
                        @endforeach
                    </div>

                    <!-- Pagination Controls -->
                    <div class="mt-12">
                        {{ $tours->links() }}
                    </div>
                @else
                    <div class="rounded-2xl bg-white p-12 text-center border border-slate-200/80 shadow-xs">
                        <x-lucide-map class="h-10 w-10 text-slate-400 mx-auto" />
                        <h3 class="font-bold text-slate-900 mt-4">
                            {{ $locale === 'id' ? 'Tidak ada paket tour' : ($locale === 'ms' ? 'Tiada pakej pelancongan' : 'No tour packages found') }}
                        </h3>
                        <p class="mt-2 text-sm text-slate-500 max-w-sm mx-auto leading-relaxed">
                            {{ $locale === 'id' ? 'Saat ini belum ada paket aktif untuk kategori yang Anda pilih.' : ($locale === 'ms' ? 'Pada masa ini tiada pakej aktif untuk kategori yang anda pilih.' : 'There are currently no active packages for your selected category.') }}
                        </p>
                        <a href="{{ route('tour.index', ['locale' => $locale]) }}" 
                           class="mt-6 inline-flex items-center gap-1.5 rounded-full bg-slate-900 px-5 py-2.5 text-xs font-semibold text-white hover:bg-slate-800 transition-colors">
                            {{ $locale === 'id' ? 'Lihat Semua Paket' : ($locale === 'ms' ? 'Lihat Semua Pakej' : 'View All Packages') }}
                        </a>
                    </div>
                @endif
            </main>

        </div>
    </div>
</x-layouts::app>