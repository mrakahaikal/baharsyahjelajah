<x-layouts::app>
    @php
        $locale = app()->getLocale();
        $settings = app(\App\Settings\GeneralSettings::class);
        $address = data_get($settings->address, $locale)
            ?? data_get($settings->address, 'id')
            ?? data_get($settings->address, 'en');
        $whatsappUrl = $settings->whatsapp_number ? 'https://wa.me/'.$settings->whatsapp_number : null;
        $tourContext = request('tour');
    @endphp

    <section class="bg-slate-50 border-b border-slate-100">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 pb-16 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8">
            <div>
                <nav class="mb-5 flex text-sm text-slate-500" aria-label="Breadcrumb">
                    <ol class="flex items-center gap-2">
                        <li>
                            <a href="{{ route('home', ['locale' => $locale]) }}" class="rounded-md transition-colors hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                {{ $locale === 'id' ? 'Beranda' : ($locale === 'ms' ? 'Utama' : 'Home') }}
                            </a>
                        </li>
                        <li aria-hidden="true">/</li>
                        <li class="font-medium text-slate-900" aria-current="page">Kontak</li>
                    </ol>
                </nav>

                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Kontak</p>
                <h1 class="mt-3 max-w-3xl text-3xl font-extrabold tracking-tight text-slate-900 text-balance sm:text-4xl lg:text-5xl">
                    Hubungi Tim Baharsyah Jelajah
                </h1>
                <p class="mt-5 max-w-2xl text-sm leading-7 text-slate-500">
                    Konsultasikan kebutuhan tour, itinerary, atau layanan perjalanan Anda melalui kanal resmi kami.
                </p>

                @if($tourContext)
                    <div class="mt-6 rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm leading-6 text-blue-900">
                        <span class="font-semibold">Konteks konsultasi:</span>
                        {{ str($tourContext)->replace('-', ' ')->title() }}
                    </div>
                @endif
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @if($whatsappUrl)
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="group rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs transition-[border-color,box-shadow,transform] duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        <x-lucide-message-circle class="h-6 w-6 text-blue-600" aria-hidden="true" />
                        <h2 class="mt-5 text-base font-bold text-slate-900 transition-colors group-hover:text-blue-600">WhatsApp</h2>
                        <p class="mt-2 break-words text-sm text-slate-500">{{ $settings->whatsapp_number }}</p>
                    </a>
                @endif

                <a href="mailto:{{ $settings->email }}" class="group rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs transition-[border-color,box-shadow,transform] duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                    <x-lucide-mail class="h-6 w-6 text-blue-600" aria-hidden="true" />
                    <h2 class="mt-5 text-base font-bold text-slate-900 transition-colors group-hover:text-blue-600">Email</h2>
                    <p class="mt-2 break-words text-sm text-slate-500">{{ $settings->email }}</p>
                </a>

                @if($address)
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs sm:col-span-2">
                        <x-lucide-map-pin class="h-6 w-6 text-blue-600" aria-hidden="true" />
                        <h2 class="mt-5 text-base font-bold text-slate-900">Alamat</h2>
                        <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-500">{{ $address }}</p>
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-layouts::app>
