<x-layouts::app>


<!-- Design System Header -->
<header class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-xs">
    <div class="max-w-7xl mx-auto px-6 py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-brand-primary tracking-tight">AVENTRA TRAVEL</h1>
            <p class="text-sm text-slate-500">Living Design System & Theme Customizer (Tailwind v4 + Alpine.js)</p>
        </div>

        <!-- Live Theme Customizer Controls -->
        <div class="bg-slate-100 p-3 rounded-xl border border-slate-200 flex flex-wrap items-center gap-4 text-xs font-semibold">
            <span class="text-slate-700">Preset Tema:</span>
            <div class="flex gap-2">
                <button @click="setTheme('#0f172a', '#2563eb', '#22c55e', '#0b1324')" class="px-2.5 py-1 bg-white rounded shadow-xs border border-gray-200 hover:bg-gray-50">Aventra Default</button>
                <button @click="setTheme('#111827', '#0d9488', '#f59e0b', '#111827')" class="px-2.5 py-1 bg-white rounded shadow-xs border border-gray-200 hover:bg-gray-50">Teal Breeze</button>
                <button @click="setTheme('#4c1d95', '#db2777', '#f43f5e', '#1e1b4b')" class="px-2.5 py-1 bg-white rounded shadow-xs border border-gray-200 hover:bg-gray-50">Neon Berry</button>
            </div>
        </div>
    </div>
</header>

<div class="max-w-7xl mx-auto px-6 py-10 grid grid-cols-1 lg:grid-cols-4 gap-10">

    <!-- Sidebar Controls / Live Customizer -->
    <aside class="lg:col-span-1">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 sticky top-24">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Live Color Variables</h2>
            <p class="text-xs text-slate-500 mb-6">Ubah warna di bawah ini untuk melihat perubahan tema secara real-time di seluruh komponen.</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Brand Primary (Text, Header, CTA)</label>
                    <div class="flex gap-2">
                        <input type="color" x-model="primary" class="w-8 h-8 rounded border border-gray-300 cursor-pointer">
                        <input type="text" x-model="primary" class="font-mono text-xs border border-gray-300 rounded px-2 w-full bg-gray-50">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Brand Secondary (Accents, Links)</label>
                    <div class="flex gap-2">
                        <input type="color" x-model="secondary" class="w-8 h-8 rounded border border-gray-300 cursor-pointer">
                        <input type="text" x-model="secondary" class="font-mono text-xs border border-gray-300 rounded px-2 w-full bg-gray-50">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Brand Accent (Badges, Success)</label>
                    <div class="flex gap-2">
                        <input type="color" x-model="accent" class="w-8 h-8 rounded border border-gray-300 cursor-pointer">
                        <input type="text" x-model="accent" class="font-mono text-xs border border-gray-300 rounded px-2 w-full bg-gray-50">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Brand BG Dark (Footer Background)</label>
                    <div class="flex gap-2">
                        <input type="color" x-model="bgDark" class="w-8 h-8 rounded border border-gray-300 cursor-pointer">
                        <input type="text" x-model="bgDark" class="font-mono text-xs border border-gray-300 rounded px-2 w-full bg-gray-50">
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-2">Tailwind v4 Tip</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Di Tailwind v4, semua variabel CSS yang didefinisikan dengan prefix <code class="bg-gray-100 px-1 rounded text-red-600">--color-*</code> di dalam <code class="bg-gray-100 px-1 rounded">:root</code> otomatis di-mapping menjadi utility classes seperti <code class="bg-gray-100 px-1 rounded">bg-brand-primary</code> atau <code class="bg-gray-100 px-1 rounded">text-brand-secondary</code> tanpa perlu konfigurasi tambahan di file JS.
                </p>
            </div>
        </div>
    </aside>

    <!-- Main Design System Content -->
    <main class="lg:col-span-3 space-y-12">

        <!-- SECTION 1: COLOR PALETTE -->
        <section class="bg-white p-8 rounded-2xl shadow-xs border border-gray-200">
            <h2 class="text-lg font-bold text-slate-900 mb-2">1. Color Palette Swatches</h2>
            <p class="text-sm text-slate-500 mb-6">Daftar token warna utama yang digunakan pada komponen Aventra Travel.</p>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div class="border border-gray-200 rounded-xl overflow-hidden shadow-2xs">
                    <div class="h-24 bg-brand-primary transition-colors"></div>
                    <div class="p-3 bg-gray-50 text-xs">
                        <p class="font-bold text-slate-900">Brand Primary</p>
                        <p class="font-mono text-slate-500">bg-brand-primary</p>
                    </div>
                </div>
                <div class="border border-gray-200 rounded-xl overflow-hidden shadow-2xs">
                    <div class="h-24 bg-brand-secondary transition-colors"></div>
                    <div class="p-3 bg-gray-50 text-xs">
                        <p class="font-bold text-slate-900">Brand Secondary</p>
                        <p class="font-mono text-slate-500">bg-brand-secondary</p>
                    </div>
                </div>
                <div class="border border-gray-200 rounded-xl overflow-hidden shadow-2xs">
                    <div class="h-24 bg-brand-accent transition-colors"></div>
                    <div class="p-3 bg-gray-50 text-xs">
                        <p class="font-bold text-slate-900">Brand Accent</p>
                        <p class="font-mono text-slate-500">bg-brand-accent</p>
                    </div>
                </div>
                <div class="border border-gray-200 rounded-xl overflow-hidden shadow-2xs">
                    <div class="h-24 bg-brand-bg-dark transition-colors"></div>
                    <div class="p-3 bg-gray-50 text-xs">
                        <p class="font-bold text-slate-900">Brand BG Dark</p>
                        <p class="font-mono text-slate-500">bg-brand-bg-dark</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 2: TYPOGRAPHY -->
        <section class="bg-white p-8 rounded-2xl shadow-xs border border-gray-200">
            <h2 class="text-lg font-bold text-slate-900 mb-2">2. Typography Hierarchy</h2>
            <p class="text-sm text-slate-500 mb-6">Menggunakan font Plus Jakarta Sans untuk tampilan modern dan clean.</p>

            <div class="space-y-6">
                <div class="pb-4 border-b border-gray-100">
                    <span class="text-xs font-mono text-slate-400 block mb-1">Heading 1 (text-4xl atau text-5xl font-extrabold text-brand-primary)</span>
                    <h1 class="text-4xl font-extrabold text-brand-primary tracking-tight">Japan Express: Osaka to Tokyo</h1>
                </div>
                <div class="pb-4 border-b border-gray-100">
                    <span class="text-xs font-mono text-slate-400 block mb-1">Heading 2 (text-2xl font-bold text-brand-primary)</span>
                    <h2 class="text-2xl font-bold text-brand-primary">Trip Overview</h2>
                </div>
                <div class="pb-4 border-b border-gray-100">
                    <span class="text-xs font-mono text-slate-400 block mb-1">Heading 3 / Subheading (text-lg font-bold text-slate-900)</span>
                    <h3 class="text-lg font-bold text-slate-900">Arrive in Osaka</h3>
                </div>
                <div>
                    <span class="text-xs font-mono text-slate-400 block mb-1">Body Text (text-slate-600 leading-relaxed)</span>
                    <p class="text-slate-600 leading-relaxed">Experience the contrasts of Japan on a journey from the neon-lit streets of Osaka to the serene temples of Kyoto.</p>
                </div>
            </div>
        </section>

        <!-- SECTION 3: BUTTONS & INTERACTIVE -->
        <section class="bg-white p-8 rounded-2xl shadow-xs border border-gray-200">
            <h2 class="text-lg font-bold text-slate-900 mb-2">3. Buttons & Interactive Components</h2>
            <p class="text-sm text-slate-500 mb-6">Komponen interaktif yang menggunakan token warna dinamis.</p>

            <div class="flex flex-wrap gap-4 items-center">
                <div>
                    <p class="text-xs font-mono text-slate-400 mb-2">Primary Button / CTA</p>
                    <button class="bg-brand-primary hover:opacity-90 text-white px-6 py-2.5 rounded-full text-sm font-semibold transition-all">
                        Book Trip
                    </button>
                </div>

                <div>
                    <p class="text-xs font-mono text-slate-400 mb-2">Secondary / Outline Button</p>
                    <button class="py-2.5 px-6 border border-slate-200 text-brand-primary font-semibold rounded-full hover:bg-slate-50 transition-colors text-sm">
                        View Itinerary
                    </button>
                </div>

                <div>
                    <p class="text-xs font-mono text-slate-400 mb-2">Dynamic Badge</p>
                    <span class="inline-block px-3 py-1 bg-brand-primary text-white text-xs font-bold rounded-md uppercase tracking-wider">
                            Top Rated
                        </span>
                </div>

                <div>
                    <p class="text-xs font-mono text-slate-400 mb-2">Accent Highlight Tag</p>
                    <span class="text-xs font-bold text-brand-accent bg-emerald-50 px-2 py-1 rounded">
                            Save $898
                        </span>
                </div>
            </div>
        </section>

        <!-- SECTION 4: CARD IN ACTION -->
        <section class="bg-white p-8 rounded-2xl shadow-xs border border-gray-200">
            <h2 class="text-lg font-bold text-slate-900 mb-2">4. Component Pattern: Recommendation Card</h2>
            <p class="text-sm text-slate-500 mb-6">Contoh implementasi riil card dengan integrasi variabel warna penuh.</p>

            <div class="max-w-sm bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow border border-gray-100 group">
                <div class="relative h-48 overflow-hidden bg-slate-200">
                    <span class="absolute top-4 left-4 bg-white/90 backdrop-blur text-slate-900 text-xs font-bold px-3 py-1 rounded-full z-10">Best Seller</span>
                    <div class="w-full h-full bg-cover bg-center group-hover:scale-105 transition-transform duration-500" style="background-image: url('https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?q=80&w=600&auto=format&fit=crop');"></div>
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-lg text-slate-900 mb-1 line-clamp-1">Japan Explore: Osaka to Tokyo</h3>
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-4">
                        <span>8 days</span> &bull; <span class="text-brand-secondary font-medium">Osaka to Tokyo</span>
                    </div>
                    <div class="flex items-end gap-2 mb-4">
                        <span class="text-xl font-extrabold text-slate-900">$3,102</span>
                        <span class="text-xs text-slate-400 line-through mb-1">$4,000</span>
                        <span class="text-xs font-bold text-brand-accent bg-emerald-50 px-2 py-0.5 rounded ml-auto">Save $898</span>
                    </div>
                    <button class="w-full py-2 border border-slate-200 text-brand-primary font-semibold rounded-full hover:bg-slate-50 transition-colors text-sm">
                        View Itinerary
                    </button>
                </div>
            </div>
        </section>

        <!-- SECTION 5: FOOTER THEME IN ACTION -->
        <section class="bg-white p-8 rounded-2xl shadow-xs border border-gray-200">
            <h2 class="text-lg font-bold text-slate-900 mb-2">5. Theme Context: Dark Background Block</h2>
            <p class="text-sm text-slate-500 mb-6">Demonstrasi bagaimana <code class="bg-gray-100 px-1 rounded text-red-600">bg-brand-bg-dark</code> merespons perubahan variabel secara global.</p>

            <div class="bg-brand-bg-dark text-white p-6 rounded-xl transition-colors">
                <p class="text-xs font-mono text-white/50 mb-2">Simulasi Blok Footer</p>
                <h4 class="text-lg font-bold mb-2">Where's your next adventure?</h4>
                <p class="text-sm text-white/70 mb-4">Dapatkan update berkala mengenai paket perjalanan terbaik langsung ke email milikmu.</p>
                <div class="flex gap-2">
                    <input type="text" placeholder="Alamat email" class="bg-white/10 border border-white/20 rounded px-3 py-1.5 text-xs focus:outline-none w-full max-w-xs">
                    <button class="bg-white text-slate-900 px-4 py-1.5 rounded text-xs font-bold hover:bg-gray-100 transition-colors">Subscribe</button>
                </div>
            </div>
        </section>

    </main>
</div>

</x-layouts::app>
