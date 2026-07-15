@php
    $locale = app()->getLocale();
    $settings = app(\App\Settings\GeneralSettings::class);
    $address = data_get($settings->address, $locale)
        ?? data_get($settings->address, 'id')
        ?? data_get($settings->address, 'en');
    $officeHours = data_get($settings->office_hours, $locale)
        ?? data_get($settings->office_hours, 'id')
        ?? data_get($settings->office_hours, 'en');
    $phoneNumber = preg_replace('/\D+/', '', $settings->whatsapp_number);
    $whatsappUrl = $phoneNumber ? 'https://wa.me/'.$phoneNumber : null;
    $directWhatsappUrl = $whatsappUrl
        ? $whatsappUrl.'?text='.rawurlencode(__('frontend.contact_page.sidebar.whatsapp_message'))
        : null;
    $mapEmbedUrl = trim($settings->map_embed_url ?? '');
    $mapSearchUrl = 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($address ?: config('app.name'));
    $tourContext = request('tour') ? (string) str(request('tour'))->replace('-', ' ')->title() : '';
    $selectedTab = request('type') === 'b2b' ? 'b2b' : 'trip';
    $tripMessage = __('frontend.contact_page.form.trip.message');
    $b2bMessage = __('frontend.contact_page.form.b2b.message');
    $seoTitle = __('frontend.contact_page.seo_title', ['brand' => config('app.name')]);
    $seoDescription = __('frontend.contact_page.seo_description');
@endphp

<x-layouts::app
    :title="$seoTitle"
    :meta-description="$seoDescription"
    :show-floating-whatsapp="false"
    :$canonicalUrl
    :$alternateUrls>
    <section class="bg-slate-950 text-white">
        <div class="mx-auto max-w-7xl px-4 pb-12 pt-5 sm:px-6 sm:pb-14 lg:px-8 lg:pb-16">
            <nav class="mb-10 text-sm text-slate-400" aria-label="Breadcrumb">
                <ol class="flex items-center gap-2">
                    <li>
                        <a href="{{ route('home', ['locale' => $locale]) }}" class="rounded-sm transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
                            {{ __('frontend.contact_page.breadcrumb.home') }}
                        </a>
                    </li>
                    <li aria-hidden="true">/</li>
                    <li class="font-semibold text-white" aria-current="page">{{ __('frontend.contact_page.breadcrumb.current') }}</li>
                </ol>
            </nav>

            <div class="grid gap-10 lg:grid-cols-[minmax(0,1.2fr)_minmax(20rem,0.8fr)] lg:items-end">
                <div>
                    <p class="text-sm font-bold uppercase text-sky-300">{{ __('frontend.contact_page.hero.eyebrow') }}</p>
                    <h1 class="mt-4 max-w-4xl text-balance text-3xl font-extrabold leading-tight sm:text-4xl lg:text-5xl">
                        {{ __('frontend.contact_page.hero.title') }}
                    </h1>
                    <p class="mt-5 max-w-2xl text-pretty text-base leading-8 text-slate-300">
                        {{ __('frontend.contact_page.hero.description') }}
                    </p>
                    <a href="#consultation" class="mt-7 inline-flex min-h-11 touch-manipulation items-center justify-center gap-2 rounded-md bg-white px-5 py-3 text-sm font-bold text-slate-950 transition-[background-color,transform] hover:-translate-y-0.5 hover:bg-sky-100 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
                        {{ __('frontend.contact_page.hero.cta') }}
                        <x-lucide-arrow-down class="h-4 w-4" aria-hidden="true" />
                    </a>
                </div>

                <dl class="grid gap-5 border-l border-white/15 pl-5 sm:grid-cols-2 lg:grid-cols-1">
                    <div>
                        <dt class="text-xs font-bold uppercase text-slate-400">{{ __('frontend.contact_page.hero.availability_label') }}</dt>
                        <dd class="mt-2 text-sm font-semibold leading-6 text-white">{{ $officeHours }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase text-slate-400">{{ __('frontend.contact_page.hero.response_label') }}</dt>
                        <dd class="mt-2 flex items-center gap-2 text-sm font-semibold text-white">
                            <span class="h-2 w-2 rounded-full bg-emerald-400" aria-hidden="true"></span>
                            {{ __('frontend.contact_page.hero.response_value') }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    <section id="consultation" class="scroll-mt-20 bg-white py-12 sm:py-16">
        <div
            class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"
            x-data="contactWorkspace"
            data-initial-tab="{{ $selectedTab }}"
            data-trip-copy="{{ json_encode($tripMessage, JSON_HEX_APOS | JSON_HEX_QUOT) }}"
            data-b2b-copy="{{ json_encode($b2bMessage, JSON_HEX_APOS | JSON_HEX_QUOT) }}">
            <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start xl:gap-14">
                <div class="min-w-0">
                    <div class="max-w-2xl">
                        <p class="text-sm font-bold uppercase text-blue-700">{{ __('frontend.contact_page.workspace.eyebrow') }}</p>
                        <h2 class="mt-3 text-balance text-2xl font-extrabold text-slate-950 sm:text-3xl">
                            {{ __('frontend.contact_page.workspace.title') }}
                        </h2>
                        <p class="mt-4 text-pretty text-sm leading-7 text-slate-600 sm:text-base">
                            {{ __('frontend.contact_page.workspace.description') }}
                        </p>
                    </div>

                    @if($whatsappUrl)
                        <div class="mt-8 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-200 bg-slate-50 p-2">
                                <div class="grid grid-cols-2 gap-2" role="tablist" aria-label="{{ __('frontend.contact_page.workspace.tabs_label') }}">
                                    <button
                                        id="trip-tab"
                                        type="button"
                                        role="tab"
                                        aria-controls="trip-panel"
                                        x-ref="tripTab"
                                        :aria-selected="activeTab === 'trip'"
                                        :tabindex="activeTab === 'trip' ? 0 : -1"
                                        @click="selectTab('trip')"
                                        @keydown.right.prevent="moveTab('next')"
                                        @keydown.down.prevent="moveTab('next')"
                                        @keydown.home.prevent="moveTab('first')"
                                        @keydown.end.prevent="moveTab('last')"
                                        :class="activeTab === 'trip' ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-slate-950'"
                                        class="min-h-11 touch-manipulation rounded-md px-3 py-2.5 text-sm font-bold transition-[background-color,color,box-shadow] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                        {{ __('frontend.contact_page.workspace.tabs.trip') }}
                                    </button>
                                    <button
                                        id="b2b-tab"
                                        type="button"
                                        role="tab"
                                        aria-controls="b2b-panel"
                                        x-ref="b2bTab"
                                        :aria-selected="activeTab === 'b2b'"
                                        :tabindex="activeTab === 'b2b' ? 0 : -1"
                                        @click="selectTab('b2b')"
                                        @keydown.left.prevent="moveTab('previous')"
                                        @keydown.up.prevent="moveTab('previous')"
                                        @keydown.home.prevent="moveTab('first')"
                                        @keydown.end.prevent="moveTab('last')"
                                        :class="activeTab === 'b2b' ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-slate-950'"
                                        class="min-h-11 touch-manipulation rounded-md px-3 py-2.5 text-sm font-bold transition-[background-color,color,box-shadow] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                        {{ __('frontend.contact_page.workspace.tabs.b2b') }}
                                    </button>
                                </div>
                            </div>

                            <div
                                id="trip-panel"
                                role="tabpanel"
                                aria-labelledby="trip-tab"
                                x-show="activeTab === 'trip'"
                                @if($selectedTab !== 'trip') x-cloak @endif
                                class="p-5 sm:p-7">
                                <div class="flex items-start gap-4">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-md bg-emerald-50 text-emerald-700">
                                        <x-lucide-compass class="h-5 w-5" aria-hidden="true" />
                                    </span>
                                    <div class="min-w-0">
                                        <h3 class="text-lg font-extrabold text-slate-950">{{ __('frontend.contact_page.form.trip.title') }}</h3>
                                        <p class="mt-1 text-sm leading-6 text-slate-600">{{ __('frontend.contact_page.form.trip.description') }}</p>
                                    </div>
                                </div>

                                <form action="{{ $whatsappUrl }}" method="get" target="_blank" rel="noopener" @submit="syncTripMessage($event.currentTarget)" class="mt-7">
                                    <input type="hidden" name="text" value="{{ $tripMessage['default'] }}" x-ref="tripMessage">
                                    <p class="text-xs font-medium text-slate-500">{{ __('frontend.contact_page.form.required_hint') }}</p>

                                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                                        <div>
                                            <label for="customer-name" class="text-sm font-bold text-slate-800">
                                                {{ __('frontend.contact_page.form.trip.fields.name.label') }} <span class="text-blue-700" aria-hidden="true">*</span><span class="sr-only">({{ __('frontend.contact_page.form.required') }})</span>
                                            </label>
                                            <input id="customer-name" type="text" name="customer_name" autocomplete="name" required placeholder="{{ __('frontend.contact_page.form.trip.fields.name.placeholder') }}" class="mt-2 min-h-11 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-950 transition-[border-color,box-shadow] placeholder:text-slate-400 focus-visible:border-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                        </div>
                                        <div>
                                            <label for="destination-interest" class="text-sm font-bold text-slate-800">
                                                {{ __('frontend.contact_page.form.trip.fields.destination.label') }} <span class="text-blue-700" aria-hidden="true">*</span><span class="sr-only">({{ __('frontend.contact_page.form.required') }})</span>
                                            </label>
                                            <input id="destination-interest" type="text" name="destination_interest" value="{{ $tourContext }}" autocomplete="off" required placeholder="{{ __('frontend.contact_page.form.trip.fields.destination.placeholder') }}" class="mt-2 min-h-11 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-950 transition-[border-color,box-shadow] placeholder:text-slate-400 focus-visible:border-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                        </div>
                                        <div>
                                            <label for="estimated-date" class="text-sm font-bold text-slate-800">
                                                {{ __('frontend.contact_page.form.trip.fields.date.label') }} <span class="text-blue-700" aria-hidden="true">*</span><span class="sr-only">({{ __('frontend.contact_page.form.required') }})</span>
                                            </label>
                                            <input id="estimated-date" type="date" name="estimated_date" min="{{ now()->toDateString() }}" autocomplete="off" required class="mt-2 min-h-11 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-950 transition-[border-color,box-shadow] focus-visible:border-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                        </div>
                                        <div>
                                            <label for="participants" class="text-sm font-bold text-slate-800">
                                                {{ __('frontend.contact_page.form.trip.fields.participants.label') }} <span class="text-blue-700" aria-hidden="true">*</span><span class="sr-only">({{ __('frontend.contact_page.form.required') }})</span>
                                            </label>
                                            <input id="participants" type="number" name="participants" inputmode="numeric" min="1" autocomplete="off" required placeholder="{{ __('frontend.contact_page.form.trip.fields.participants.placeholder') }}" class="mt-2 min-h-11 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-950 transition-[border-color,box-shadow] placeholder:text-slate-400 focus-visible:border-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label for="travel-notes" class="text-sm font-bold text-slate-800">{{ __('frontend.contact_page.form.trip.fields.notes.label') }}</label>
                                            <textarea id="travel-notes" name="travel_notes" rows="4" autocomplete="off" placeholder="{{ __('frontend.contact_page.form.trip.fields.notes.placeholder') }}" class="mt-2 w-full resize-y rounded-md border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-950 transition-[border-color,box-shadow] placeholder:text-slate-400 focus-visible:border-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"></textarea>
                                        </div>
                                    </div>

                                    <div class="mt-6 flex flex-col gap-4 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                                        <p class="max-w-md text-xs leading-5 text-slate-500">{{ __('frontend.contact_page.form.privacy') }}</p>
                                        <button type="submit" class="inline-flex min-h-11 shrink-0 touch-manipulation items-center justify-center gap-2 rounded-md bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition-[background-color,transform] hover:-translate-y-0.5 hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700">
                                            <x-lucide-send class="h-4 w-4" aria-hidden="true" />
                                            {{ __('frontend.contact_page.form.trip.submit') }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div
                                id="b2b-panel"
                                role="tabpanel"
                                aria-labelledby="b2b-tab"
                                x-show="activeTab === 'b2b'"
                                @if($selectedTab !== 'b2b') x-cloak @endif
                                class="p-5 sm:p-7">
                                <div class="flex items-start gap-4">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-md bg-blue-50 text-blue-700">
                                        <x-lucide-briefcase-business class="h-5 w-5" aria-hidden="true" />
                                    </span>
                                    <div class="min-w-0">
                                        <h3 class="text-lg font-extrabold text-slate-950">{{ __('frontend.contact_page.form.b2b.title') }}</h3>
                                        <p class="mt-1 text-sm leading-6 text-slate-600">{{ __('frontend.contact_page.form.b2b.description') }}</p>
                                    </div>
                                </div>

                                <form action="{{ $whatsappUrl }}" method="get" target="_blank" rel="noopener" @submit="syncB2bMessage($event.currentTarget)" class="mt-7">
                                    <input type="hidden" name="text" value="{{ $b2bMessage['default'] }}" x-ref="b2bMessage">
                                    <p class="text-xs font-medium text-slate-500">{{ __('frontend.contact_page.form.required_hint') }}</p>

                                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                                        <div class="sm:col-span-2">
                                            <label for="organization-name" class="text-sm font-bold text-slate-800">
                                                {{ __('frontend.contact_page.form.b2b.fields.organization.label') }} <span class="text-blue-700" aria-hidden="true">*</span><span class="sr-only">({{ __('frontend.contact_page.form.required') }})</span>
                                            </label>
                                            <input id="organization-name" type="text" name="organization_name" autocomplete="organization" required placeholder="{{ __('frontend.contact_page.form.b2b.fields.organization.placeholder') }}" class="mt-2 min-h-11 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-950 transition-[border-color,box-shadow] placeholder:text-slate-400 focus-visible:border-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                        </div>
                                        <div>
                                            <label for="pic-name" class="text-sm font-bold text-slate-800">
                                                {{ __('frontend.contact_page.form.b2b.fields.pic.label') }} <span class="text-blue-700" aria-hidden="true">*</span><span class="sr-only">({{ __('frontend.contact_page.form.required') }})</span>
                                            </label>
                                            <input id="pic-name" type="text" name="pic_name" autocomplete="name" required placeholder="{{ __('frontend.contact_page.form.b2b.fields.pic.placeholder') }}" class="mt-2 min-h-11 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-950 transition-[border-color,box-shadow] placeholder:text-slate-400 focus-visible:border-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                        </div>
                                        <div>
                                            <label for="business-email" class="text-sm font-bold text-slate-800">{{ __('frontend.contact_page.form.b2b.fields.email.label') }}</label>
                                            <input id="business-email" type="email" name="business_email" autocomplete="email" spellcheck="false" placeholder="{{ __('frontend.contact_page.form.b2b.fields.email.placeholder') }}" class="mt-2 min-h-11 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-950 transition-[border-color,box-shadow] placeholder:text-slate-400 focus-visible:border-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                        </div>
                                        <div>
                                            <label for="partnership-type" class="text-sm font-bold text-slate-800">
                                                {{ __('frontend.contact_page.form.b2b.fields.type.label') }} <span class="text-blue-700" aria-hidden="true">*</span><span class="sr-only">({{ __('frontend.contact_page.form.required') }})</span>
                                            </label>
                                            <select id="partnership-type" name="partnership_type" autocomplete="off" required class="mt-2 min-h-11 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-950 transition-[border-color,box-shadow] focus-visible:border-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                                <option value="">{{ __('frontend.contact_page.form.b2b.fields.type.placeholder') }}</option>
                                                @foreach(__('frontend.contact_page.form.b2b.types') as $type)
                                                    <option value="{{ $type }}">{{ $type }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label for="estimated-volume" class="text-sm font-bold text-slate-800">
                                                {{ __('frontend.contact_page.form.b2b.fields.volume.label') }} <span class="text-blue-700" aria-hidden="true">*</span><span class="sr-only">({{ __('frontend.contact_page.form.required') }})</span>
                                            </label>
                                            <input id="estimated-volume" type="number" name="estimated_volume" inputmode="numeric" min="1" autocomplete="off" required placeholder="{{ __('frontend.contact_page.form.b2b.fields.volume.placeholder') }}" class="mt-2 min-h-11 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-950 transition-[border-color,box-shadow] placeholder:text-slate-400 focus-visible:border-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label for="partnership-needs" class="text-sm font-bold text-slate-800">
                                                {{ __('frontend.contact_page.form.b2b.fields.needs.label') }} <span class="text-blue-700" aria-hidden="true">*</span><span class="sr-only">({{ __('frontend.contact_page.form.required') }})</span>
                                            </label>
                                            <textarea id="partnership-needs" name="partnership_needs" rows="4" autocomplete="off" required placeholder="{{ __('frontend.contact_page.form.b2b.fields.needs.placeholder') }}" class="mt-2 w-full resize-y rounded-md border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-950 transition-[border-color,box-shadow] placeholder:text-slate-400 focus-visible:border-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"></textarea>
                                        </div>
                                    </div>

                                    <div class="mt-6 flex flex-col gap-4 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                                        <p class="max-w-md text-xs leading-5 text-slate-500">{{ __('frontend.contact_page.form.privacy') }}</p>
                                        <button type="submit" class="inline-flex min-h-11 shrink-0 touch-manipulation items-center justify-center gap-2 rounded-md bg-blue-700 px-5 py-3 text-sm font-bold text-white transition-[background-color,transform] hover:-translate-y-0.5 hover:bg-blue-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
                                            <x-lucide-send class="h-4 w-4" aria-hidden="true" />
                                            {{ __('frontend.contact_page.form.b2b.submit') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="mt-8 rounded-lg border border-amber-200 bg-amber-50 p-6">
                            <x-lucide-circle-alert class="h-6 w-6 text-amber-700" aria-hidden="true" />
                            <h3 class="mt-4 text-lg font-extrabold text-slate-950">{{ __('frontend.contact_page.form.fallback_title') }}</h3>
                            <p class="mt-2 max-w-xl text-sm leading-7 text-slate-600">{{ __('frontend.contact_page.form.fallback_description') }}</p>
                            <a href="mailto:{{ $settings->email }}" class="mt-5 inline-flex min-h-11 items-center gap-2 rounded-md bg-slate-950 px-5 py-3 text-sm font-bold text-white transition-colors hover:bg-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-950">
                                <x-lucide-mail class="h-4 w-4" aria-hidden="true" />
                                {{ __('frontend.contact_page.form.fallback_email') }}
                            </a>
                        </div>
                    @endif
                </div>

                <aside class="rounded-lg bg-slate-950 p-6 text-white lg:sticky lg:top-24">
                    <p class="text-xs font-bold uppercase text-sky-300">{{ __('frontend.contact_page.sidebar.title') }}</p>
                    <p class="mt-3 text-sm leading-7 text-slate-300">{{ __('frontend.contact_page.sidebar.description') }}</p>

                    <dl class="mt-6 divide-y divide-white/10 border-y border-white/10">
                        @if($phoneNumber)
                            <div class="py-5">
                                <dt class="flex items-center gap-2 text-xs font-bold uppercase text-slate-400">
                                    <x-lucide-message-circle class="h-4 w-4" aria-hidden="true" />
                                    {{ __('frontend.contact_page.sidebar.whatsapp') }}
                                </dt>
                                <dd class="mt-2 break-words text-sm font-semibold">+{{ $phoneNumber }}</dd>
                            </div>
                        @endif
                        <div class="py-5">
                            <dt class="flex items-center gap-2 text-xs font-bold uppercase text-slate-400">
                                <x-lucide-mail class="h-4 w-4" aria-hidden="true" />
                                {{ __('frontend.contact_page.sidebar.email') }}
                            </dt>
                            <dd class="mt-2 break-words text-sm font-semibold">
                                <a href="mailto:{{ $settings->email }}" class="rounded-sm hover:text-sky-300 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-sky-300">{{ $settings->email }}</a>
                            </dd>
                        </div>
                        <div class="py-5">
                            <dt class="flex items-center gap-2 text-xs font-bold uppercase text-slate-400">
                                <x-lucide-clock-3 class="h-4 w-4" aria-hidden="true" />
                                {{ __('frontend.contact_page.sidebar.hours') }}
                            </dt>
                            <dd class="mt-2 text-sm font-semibold leading-6">{{ $officeHours }}</dd>
                        </div>
                    </dl>

                    <p class="mt-5 text-xs leading-6 text-slate-400">{{ __('frontend.contact_page.sidebar.official_note') }}</p>

                    @if($directWhatsappUrl)
                        <a href="{{ $directWhatsappUrl }}" target="_blank" rel="noopener" class="mt-6 inline-flex min-h-11 w-full touch-manipulation items-center justify-center gap-2 rounded-md bg-emerald-500 px-5 py-3 text-sm font-bold text-slate-950 transition-[background-color,transform] hover:-translate-y-0.5 hover:bg-emerald-400 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-300">
                            <x-lucide-message-circle class="h-4 w-4" aria-hidden="true" />
                            {{ __('frontend.contact_page.sidebar.whatsapp_cta') }}
                        </a>
                    @endif
                </aside>
            </div>
        </div>
    </section>

    <section class="border-t border-slate-200 bg-slate-50 py-12 sm:py-16">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[minmax(18rem,0.7fr)_minmax(0,1.3fr)] lg:items-stretch lg:px-8">
            <div class="flex flex-col justify-center">
                <p class="text-sm font-bold uppercase text-blue-700">{{ __('frontend.contact_page.location.eyebrow') }}</p>
                <h2 class="mt-3 text-balance text-2xl font-extrabold text-slate-950 sm:text-3xl">{{ __('frontend.contact_page.location.title') }}</h2>
                <p class="mt-4 text-sm leading-7 text-slate-600">{{ __('frontend.contact_page.location.description') }}</p>

                <div class="mt-7 border-l-2 border-blue-600 pl-4">
                    <p class="text-xs font-bold uppercase text-slate-500">{{ __('frontend.contact_page.location.address') }}</p>
                    <p class="mt-2 break-words text-sm font-semibold leading-7 text-slate-900">{{ $address }}</p>
                </div>

                <a href="{{ $mapSearchUrl }}" target="_blank" rel="noopener" class="mt-6 inline-flex min-h-11 w-fit touch-manipulation items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-900 transition-[background-color,border-color,color] hover:border-blue-300 hover:bg-blue-50 hover:text-blue-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                    <x-lucide-navigation class="h-4 w-4" aria-hidden="true" />
                    {{ __('frontend.contact_page.location.directions') }}
                </a>
            </div>

            <div class="min-h-80 overflow-hidden rounded-lg border border-slate-200 bg-slate-200">
                @if($mapEmbedUrl)
                    <iframe
                        src="{{ $mapEmbedUrl }}"
                        title="{{ __('frontend.contact_page.location.map_title') }}"
                        class="h-80 w-full border-0 lg:h-full lg:min-h-96"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen>
                    </iframe>
                @else
                    <div class="flex h-full min-h-80 items-center justify-center p-7 text-center lg:min-h-96">
                        <div class="max-w-sm">
                            <span class="mx-auto grid h-12 w-12 place-items-center rounded-md bg-white text-blue-700 shadow-sm">
                                <x-lucide-map-pin class="h-6 w-6" aria-hidden="true" />
                            </span>
                            <h3 class="mt-4 text-lg font-extrabold text-slate-950">{{ __('frontend.contact_page.location.fallback_title') }}</h3>
                            <p class="mt-2 text-sm leading-7 text-slate-600">{{ __('frontend.contact_page.location.fallback_description') }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-layouts::app>
