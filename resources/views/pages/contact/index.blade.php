<x-layouts::app>
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
        $mapEmbedUrl = trim($settings->map_embed_url ?? '');
        $mapSearchUrl = $address
            ? 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($address)
            : 'https://www.google.com/maps/search/?api=1&query=Baharsyah%20Jelajah';
        $tourContext = request('tour') ? (string) str(request('tour'))->replace('-', ' ')->title() : '';
        $defaultInquiryText = "Halo Baharsyah Jelajah,\n\nSaya ingin berkonsultasi tentang kebutuhan perjalanan.\n\nTerima kasih.";
        $defaultB2bText = "Halo Baharsyah Jelajah,\n\nSaya ingin berdiskusi tentang peluang kerja sama B2B.\n\nTerima kasih.";
    @endphp

    <section class="border-b border-slate-100 bg-slate-50">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 pb-14 sm:px-6 lg:grid-cols-[minmax(0,0.92fr)_minmax(0,1.08fr)] lg:items-end lg:px-8 lg:pb-16">
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

                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Kontak Resmi</p>
                <h1 class="mt-3 max-w-3xl text-3xl font-extrabold tracking-tight text-slate-900 text-balance sm:text-4xl lg:text-5xl">
                    Hubungi Tim Baharsyah Jelajah
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-8 text-slate-600">
                    Ceritakan rencana perjalanan, kebutuhan itinerary, atau peluang kerja sama. Tim kami akan membantu merapikan kebutuhan Anda sebelum diskusi berlanjut di WhatsApp.
                </p>

                @if($tourContext)
                    <div class="mt-6 rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm leading-6 text-blue-900">
                        <span class="font-semibold">Konteks konsultasi:</span>
                        {{ $tourContext }}
                    </div>
                @endif
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @if($whatsappUrl)
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="group rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs transition-[border-color,box-shadow,transform] duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                        <x-lucide-message-circle class="h-6 w-6 text-emerald-600" aria-hidden="true" />
                        <h2 class="mt-5 text-base font-bold text-slate-900 transition-colors group-hover:text-emerald-700">WhatsApp Resmi</h2>
                        <p class="mt-2 break-words text-sm text-slate-500">+{{ $phoneNumber }}</p>
                    </a>
                @endif

                <a href="mailto:{{ $settings->email }}" class="group rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs transition-[border-color,box-shadow,transform] duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                    <x-lucide-mail class="h-6 w-6 text-blue-600" aria-hidden="true" />
                    <h2 class="mt-5 text-base font-bold text-slate-900 transition-colors group-hover:text-blue-600">Email Operasional</h2>
                    <p class="mt-2 break-words text-sm text-slate-500">{{ $settings->email }}</p>
                </a>

                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs sm:col-span-2">
                    <x-lucide-clock-3 class="h-6 w-6 text-amber-600" aria-hidden="true" />
                    <h2 class="mt-5 text-base font-bold text-slate-900">Jam Respons</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-500">{{ $officeHours }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white py-14 sm:py-16">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)] lg:px-8">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-sm">
                @if($mapEmbedUrl)
                    <iframe
                        src="{{ $mapEmbedUrl }}"
                        title="Peta lokasi Baharsyah Jelajah"
                        class="h-[360px] w-full border-0 sm:h-[440px] lg:h-full"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen>
                    </iframe>
                @else
                    <div class="flex min-h-[360px] flex-col justify-end bg-[linear-gradient(135deg,#e0f2fe_0%,#f8fafc_45%,#dcfce7_100%)] p-6 sm:min-h-[440px]">
                        <div class="max-w-md rounded-2xl bg-white/90 p-5 shadow-sm ring-1 ring-slate-200 backdrop-blur">
                            <x-lucide-map-pin class="h-7 w-7 text-blue-600" aria-hidden="true" />
                            <h2 class="mt-4 text-lg font-bold text-slate-900">Lokasi Kantor</h2>
                            <p class="mt-2 text-sm leading-7 text-slate-600">
                                Google Map belum diatur di panel admin. Alamat tetap tersedia untuk membantu pengunjung menemukan kanal resmi.
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="grid gap-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
                    <div class="flex items-start gap-4">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-blue-50 text-blue-700">
                            <x-lucide-map-pin class="h-5 w-5" aria-hidden="true" />
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-lg font-bold text-slate-900">Alamat & Area Operasional</h2>
                            <p class="mt-2 break-words text-sm leading-7 text-slate-600">{{ $address }}</p>
                            <a href="{{ $mapSearchUrl }}" target="_blank" rel="noopener" class="mt-4 inline-flex w-fit items-center justify-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-800 transition-[background-color,border-color,color] duration-200 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                <x-lucide-external-link class="h-4 w-4" aria-hidden="true" />
                                Buka di Google Maps
                            </a>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach([
                        ['icon' => 'shield', 'title' => 'Kanal Resmi', 'desc' => 'Nomor WhatsApp dan email di halaman ini menjadi rujukan komunikasi utama.'],
                        ['icon' => 'route', 'title' => 'Itinerary Jelas', 'desc' => 'Kebutuhan rute, tanggal, dan jumlah peserta dirapikan sebelum penawaran.'],
                        ['icon' => 'handshake', 'title' => 'Siap B2B', 'desc' => 'Terbuka untuk komunitas, sekolah, perusahaan, dan mitra perjalanan.'],
                        ['icon' => 'message', 'title' => 'Respons Personal', 'desc' => 'Pesan WhatsApp membawa ringkasan kebutuhan agar percakapan lebih cepat.'],
                    ] as $item)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            @if($item['icon'] === 'shield')
                                <x-lucide-shield-check class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                            @elseif($item['icon'] === 'route')
                                <x-lucide-route class="h-5 w-5 text-blue-600" aria-hidden="true" />
                            @elseif($item['icon'] === 'handshake')
                                <x-lucide-handshake class="h-5 w-5 text-amber-600" aria-hidden="true" />
                            @else
                                <x-lucide-message-square-text class="h-5 w-5 text-slate-700" aria-hidden="true" />
                            @endif
                            <h3 class="mt-4 text-sm font-bold text-slate-900">{{ $item['title'] }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $item['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="border-y border-slate-100 bg-slate-50 py-14 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Mulai Percakapan</p>
                <h2 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 text-balance sm:text-3xl">
                    Pilih jalur yang paling sesuai dengan kebutuhan Anda
                </h2>
                <p class="mt-4 text-sm leading-7 text-slate-600">
                    Form ini tidak menyimpan data di website. Ringkasan kebutuhan akan dibawa ke WhatsApp agar tim dapat memahami konteks sejak pesan pertama.
                </p>
            </div>

            <div class="mt-8 grid gap-6 lg:grid-cols-2">
                <form
                    action="{{ $whatsappUrl ?? '#' }}"
                    method="get"
                    target="_blank"
                    x-data="{
                        field(name) {
                            return this.$el.elements[name]?.value?.trim() || '-';
                        },
                        buildInquiryMessage() {
                            return [
                                'Halo Baharsyah Jelajah,',
                                '',
                                'Saya ingin berkonsultasi tentang perjalanan.',
                                '',
                                'Nama: ' + this.field('customer_name'),
                                'Nomor WhatsApp: ' + this.field('customer_phone'),
                                'Tujuan/minat: ' + this.field('destination_interest'),
                                'Tanggal estimasi: ' + this.field('estimated_date'),
                                'Jumlah peserta: ' + this.field('participants'),
                                'Catatan: ' + this.field('travel_notes'),
                                '',
                                'Mohon arahan itinerary dan estimasi kebutuhan. Terima kasih.',
                            ].join('\n');
                        },
                        syncInquiryMessage() {
                            this.$refs.inquiryMessage.value = this.buildInquiryMessage();
                        },
                    }"
                    x-on:submit="syncInquiryMessage()"
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <input type="hidden" name="text" value="{{ $defaultInquiryText }}" x-ref="inquiryMessage">

                    <div class="flex items-start gap-4">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-emerald-50 text-emerald-700">
                            <x-lucide-compass class="h-5 w-5" aria-hidden="true" />
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-xl font-bold text-slate-900">Inquiry Perjalanan</h2>
                            <p class="mt-2 text-sm leading-7 text-slate-600">Untuk private tour, group tour, itinerary keluarga, atau diskusi destinasi.</p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-800">Nama</span>
                            <input type="text" name="customer_name" autocomplete="name" placeholder="Nama lengkap&hellip;" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 transition-[border-color,box-shadow] duration-200 placeholder:text-slate-400 focus-visible:border-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-800">Nomor WhatsApp</span>
                            <input type="tel" name="customer_phone" inputmode="tel" autocomplete="tel" placeholder="Contoh: 0812&hellip;" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 transition-[border-color,box-shadow] duration-200 placeholder:text-slate-400 focus-visible:border-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="text-sm font-semibold text-slate-800">Tujuan atau Minat Tour</span>
                            <input type="text" name="destination_interest" value="{{ $tourContext }}" autocomplete="off" placeholder="Contoh: Tanjung Puting, Derawan, custom trip&hellip;" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 transition-[border-color,box-shadow] duration-200 placeholder:text-slate-400 focus-visible:border-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-800">Tanggal Estimasi</span>
                            <input type="text" name="estimated_date" autocomplete="off" placeholder="Contoh: Agustus 2026&hellip;" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 transition-[border-color,box-shadow] duration-200 placeholder:text-slate-400 focus-visible:border-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-800">Jumlah Peserta</span>
                            <input type="number" name="participants" inputmode="numeric" min="1" autocomplete="off" placeholder="Contoh: 6&hellip;" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 transition-[border-color,box-shadow] duration-200 placeholder:text-slate-400 focus-visible:border-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="text-sm font-semibold text-slate-800">Catatan Kebutuhan</span>
                            <textarea name="travel_notes" rows="4" autocomplete="off" placeholder="Ceritakan gaya perjalanan, durasi, atau kebutuhan khusus&hellip;" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 transition-[border-color,box-shadow] duration-200 placeholder:text-slate-400 focus-visible:border-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"></textarea>
                        </label>
                    </div>

                    <button type="submit" @disabled(! $whatsappUrl) class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition-[background-color,transform] duration-200 hover:-translate-y-0.5 hover:bg-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:hover:translate-y-0">
                        <x-lucide-send class="h-4 w-4" aria-hidden="true" />
                        Kirim Inquiry via WhatsApp
                    </button>
                </form>

                <form
                    action="{{ $whatsappUrl ?? '#' }}"
                    method="get"
                    target="_blank"
                    x-data="{
                        field(name) {
                            return this.$el.elements[name]?.value?.trim() || '-';
                        },
                        buildB2bMessage() {
                            return [
                                'Halo Baharsyah Jelajah,',
                                '',
                                'Saya ingin berdiskusi tentang peluang kerja sama B2B.',
                                '',
                                'Perusahaan/komunitas: ' + this.field('organization_name'),
                                'Nama PIC: ' + this.field('pic_name'),
                                'Nomor WhatsApp: ' + this.field('pic_phone'),
                                'Email: ' + this.field('business_email'),
                                'Tipe kerja sama: ' + this.field('partnership_type'),
                                'Estimasi volume/peserta: ' + this.field('estimated_volume'),
                                'Kebutuhan: ' + this.field('partnership_needs'),
                                '',
                                'Mohon jadwalkan diskusi lanjutan. Terima kasih.',
                            ].join('\n');
                        },
                        syncB2bMessage() {
                            this.$refs.b2bMessage.value = this.buildB2bMessage();
                        },
                    }"
                    x-on:submit="syncB2bMessage()"
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <input type="hidden" name="text" value="{{ $defaultB2bText }}" x-ref="b2bMessage">

                    <div class="flex items-start gap-4">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-blue-50 text-blue-700">
                            <x-lucide-briefcase-business class="h-5 w-5" aria-hidden="true" />
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-xl font-bold text-slate-900">Permohonan B2B</h2>
                            <p class="mt-2 text-sm leading-7 text-slate-600">Untuk perusahaan, sekolah, komunitas, travel partner, atau program perjalanan berkala.</p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <label class="block sm:col-span-2">
                            <span class="text-sm font-semibold text-slate-800">Perusahaan atau Komunitas</span>
                            <input type="text" name="organization_name" autocomplete="organization" placeholder="Nama organisasi&hellip;" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 transition-[border-color,box-shadow] duration-200 placeholder:text-slate-400 focus-visible:border-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-800">Nama PIC</span>
                            <input type="text" name="pic_name" autocomplete="name" placeholder="Nama PIC&hellip;" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 transition-[border-color,box-shadow] duration-200 placeholder:text-slate-400 focus-visible:border-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-800">Nomor WhatsApp</span>
                            <input type="tel" name="pic_phone" inputmode="tel" autocomplete="tel" placeholder="Contoh: 0812&hellip;" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 transition-[border-color,box-shadow] duration-200 placeholder:text-slate-400 focus-visible:border-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="text-sm font-semibold text-slate-800">Email Kantor</span>
                            <input type="email" name="business_email" autocomplete="email" spellcheck="false" placeholder="nama@perusahaan.com&hellip;" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 transition-[border-color,box-shadow] duration-200 placeholder:text-slate-400 focus-visible:border-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-800">Tipe Kerja Sama</span>
                            <select name="partnership_type" autocomplete="off" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 transition-[border-color,box-shadow] duration-200 focus-visible:border-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                <option value="">Pilih kebutuhan</option>
                                <option value="Corporate outing">Corporate outing</option>
                                <option value="School trip">School trip</option>
                                <option value="Community trip">Community trip</option>
                                <option value="Travel partner">Travel partner</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-800">Volume atau Peserta</span>
                            <input type="text" name="estimated_volume" autocomplete="off" placeholder="Contoh: 30 pax / kuartal&hellip;" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 transition-[border-color,box-shadow] duration-200 placeholder:text-slate-400 focus-visible:border-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="text-sm font-semibold text-slate-800">Kebutuhan Kerja Sama</span>
                            <textarea name="partnership_needs" rows="4" autocomplete="off" placeholder="Ceritakan kebutuhan program, destinasi, durasi, atau skema kerja sama&hellip;" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 transition-[border-color,box-shadow] duration-200 placeholder:text-slate-400 focus-visible:border-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"></textarea>
                        </label>
                    </div>

                    <button type="submit" @disabled(! $whatsappUrl) class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-full bg-blue-700 px-5 py-3 text-sm font-semibold text-white transition-[background-color,transform] duration-200 hover:-translate-y-0.5 hover:bg-blue-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:hover:translate-y-0">
                        <x-lucide-send class="h-4 w-4" aria-hidden="true" />
                        Ajukan Diskusi B2B
                    </button>
                </form>
            </div>
        </div>
    </section>
</x-layouts::app>
