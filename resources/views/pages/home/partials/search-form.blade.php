<!-- Quick Search Form -->
<div x-data="{ activeTab: 'tour' }">
    <!-- Tabs -->
    <div class="flex gap-1 mb-4">
        <button @click="activeTab = 'tour'"
                :class="activeTab === 'tour' ? 'bg-primary text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                class="px-3 py-1.5 text-xs font-medium rounded-full transition-all flex items-center gap-1.5">
            <x-lucide-map-pin class="w-3 h-3" />
            {{ __('frontend.search.tabs.tour') }}
        </button>
        <button @click="activeTab = 'transport'"
                :class="activeTab === 'transport' ? 'bg-primary text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                class="px-3 py-1.5 text-xs font-medium rounded-full transition-all flex items-center gap-1.5">
            <x-lucide-car class="w-3 h-3" />
            {{ __('frontend.search.tabs.transport') }}
        </button>
        <button @click="activeTab = 'umroh'"
                :class="activeTab === 'umroh' ? 'bg-primary text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                class="px-3 py-1.5 text-xs font-medium rounded-full transition-all flex items-center gap-1.5">
            <x-lucide-moon class="w-3 h-3" />
            {{ __('frontend.search.tabs.umroh') }}
        </button>
    </div>

    <!-- Tour Form -->
    <div x-show="activeTab === 'tour'" class="flex flex-col gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ __('frontend.search.fields.destination') }}</label>
            <div class="relative">
                <x-lucide-map-pin class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
                <input type="text" placeholder="{{ __('frontend.search.fields.destination_placeholder') }}"
                       class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/30 transition-colors">
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ __('frontend.search.fields.region') }}</label>
            <select class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/30 transition-colors appearance-none">
                <option>{{ __('frontend.search.fields.all_regions') }}</option>
                <option>Jakarta-Bandung</option>
                <option>Mesir</option>
                <option>Internasional</option>
            </select>
        </div>
        <button class="w-full bg-primary hover:bg-primary/90 text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
            <x-lucide-search class="w-4 h-4" />
            {{ __('frontend.search.buttons.search_tour') }}
        </button>
    </div>

    <!-- Transport Form -->
    <div x-show="activeTab === 'transport'" x-cloak class="flex flex-col gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ __('frontend.search.fields.capacity') }}</label>
            <div class="relative">
                <x-lucide-users class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
                <input type="number" min="1" placeholder="{{ __('frontend.search.fields.pax_placeholder') }}"
                       class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/30 transition-colors">
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ __('frontend.search.fields.vehicle_type') }}</label>
            <select class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/30 transition-colors appearance-none">
                <option>{{ __('frontend.search.fields.all_vehicles') }}</option>
                <option>Ekonomis</option>
                <option>Premium</option>
                <option>Bus / Van</option>
            </select>
        </div>
        <button class="w-full bg-primary hover:bg-primary/90 text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
            <x-lucide-search class="w-4 h-4" />
            {{ __('frontend.search.buttons.search_transport') }}
        </button>
    </div>

    <!-- Umroh Form -->
    <div x-show="activeTab === 'umroh'" x-cloak class="flex flex-col gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ __('frontend.search.fields.departure_month') }}</label>
            <div class="relative">
                <x-lucide-calendar class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
                <select class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/30 transition-colors appearance-none">
                    <option>{{ __('frontend.search.fields.select_month') }}</option>
                    <option>Ramadhan 2026</option>
                    <option>Syawal 2026</option>
                    <option>Dzulhijjah 2026</option>
                </select>
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ __('frontend.search.fields.package_type') }}</label>
            <select class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/30 transition-colors appearance-none">
                <option>{{ __('frontend.search.fields.all_packages') }}</option>
                <option>All-in (Regular)</option>
                <option>DIY Umroh</option>
                <option>Umroh Request</option>
            </select>
        </div>
        <button class="w-full bg-primary hover:bg-primary/90 text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
            <x-lucide-search class="w-4 h-4" />
            {{ __('frontend.search.buttons.search_umroh') }}
        </button>
    </div>
</div>
