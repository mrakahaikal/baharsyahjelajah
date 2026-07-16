<?php

use App\Enums\UmrahPackageType;
use App\Models\Country;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

new class extends Component
{
    #[Locked]
    public string $locale;

    public string $activeService = 'tour';

    public string $tourDestination = '';

    public string $tourType = '';

    public string $umrahType = '';

    public string $visaCountry = '';

    public string $transportPax = '';

    public string $transportRate = '';

    public function mount(string $locale): void
    {
        $this->locale = $locale;
    }

    public function selectService(string $service): void
    {
        if (! in_array($service, $this->services(), true)) {
            return;
        }

        $this->activeService = $service;
        $this->resetValidation();
    }

    public function search(): Redirector
    {
        if (! in_array($this->activeService, $this->services(), true)) {
            $this->activeService = 'tour';
        }

        $this->validate($this->rules());

        return match ($this->activeService) {
            'umrah' => redirect()->route('umroh.index', array_filter([
                'locale' => $this->locale,
                'type' => $this->umrahType,
            ])),
            'visa' => redirect()->route('visa.index', array_filter([
                'locale' => $this->locale,
                'country' => $this->visaCountry,
            ])),
            'transport' => redirect()->route('transport.index', array_filter([
                'locale' => $this->locale,
                'pax' => $this->transportPax,
                'rate' => $this->transportRate,
            ])),
            default => redirect()->route('tour.index', array_filter([
                'locale' => $this->locale,
                'destination' => trim($this->tourDestination),
                'type' => $this->tourType,
            ])),
        };
    }

    #[Computed]
    public function countries(): Collection
    {
        return Country::query()
            ->select(['id', 'name', 'slug'])
            ->active()
            ->whereHas('visaServices', fn (Builder $query): Builder => $query->active())
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(): array
    {
        return match ($this->activeService) {
            'umrah' => [
                'umrahType' => ['nullable', Rule::enum(UmrahPackageType::class)],
            ],
            'visa' => [
                'visaCountry' => ['nullable', Rule::in($this->countries->pluck('slug')->all())],
            ],
            'transport' => [
                'transportPax' => ['nullable', 'integer', 'min:1', 'max:100'],
                'transportRate' => ['nullable', Rule::in(['daily', 'trip'])],
            ],
            default => [
                'tourDestination' => ['nullable', 'string', 'max:100'],
                'tourType' => ['nullable', Rule::in(['domestic', 'international'])],
            ],
        };
    }

    /** @return array<int, string> */
    private function services(): array
    {
        return ['tour', 'umrah', 'visa', 'transport'];
    }
};
?>

<section id="search-panel" class="relative z-20 -mt-20 scroll-mt-24" aria-labelledby="search-heading">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-xl shadow-slate-950/10">
            <div class="grid gap-6 px-5 pt-5 sm:px-7 sm:pt-7 lg:grid-cols-[minmax(0,0.72fr)_minmax(0,1.28fr)] lg:items-end">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">{{ __('home.search.eyebrow') }}</p>
                    <h2 id="search-heading" class="mt-2 text-balance text-2xl font-extrabold text-slate-950 sm:text-3xl">
                        {{ __('home.search.title') }}
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('home.search.subtitle') }}</p>
                </div>

                <div class="-mx-5 overflow-x-auto px-5 sm:-mx-7 sm:px-7" role="tablist" aria-label="{{ __('home.search.service_label') }}">
                    <div class="grid min-w-132 grid-cols-4 gap-1 rounded-lg bg-slate-100 p-1">
                        @foreach(['tour', 'umrah', 'visa', 'transport'] as $service)
                            <button
                                type="button"
                                role="tab"
                                wire:click="selectService('{{ $service }}')"
                                @if($activeService === $service) aria-selected="true" @else aria-selected="false" @endif
                                aria-controls="search-{{ $service }}-panel"
                                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md px-3 text-sm font-bold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 {{ $activeService === $service ? 'bg-white text-slate-950 shadow-sm' : 'text-slate-500 hover:bg-white/65 hover:text-slate-800' }}"
                            >
                                @if($service === 'tour')
                                    <x-lucide-map class="h-4 w-4 text-blue-600" aria-hidden="true" />
                                @elseif($service === 'umrah')
                                    <x-lucide-moon-star class="h-4 w-4 text-amber-600" aria-hidden="true" />
                                @elseif($service === 'visa')
                                    <x-lucide-stamp class="h-4 w-4 text-emerald-700" aria-hidden="true" />
                                @else
                                    <x-lucide-car-front class="h-4 w-4 text-rose-600" aria-hidden="true" />
                                @endif
                                {{ __('home.search.tabs.'.$service) }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <form wire:submit="search" class="mt-6 border-t border-slate-200 bg-slate-50/80 p-5 sm:p-7" role="search">
                <div id="search-{{ $activeService }}-panel" role="tabpanel" class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_minmax(0,0.75fr)_auto] lg:items-start">
                    @if($activeService === 'tour')
                        <label class="min-w-0 rounded-lg border border-slate-200 bg-white px-4 py-3 transition-[border-color,box-shadow] focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-600/15">
                            <span class="block text-[11px] font-bold uppercase text-slate-500">{{ __('home.search.tour.destination') }}</span>
                            <span class="mt-1 flex min-w-0 items-center gap-2">
                                <x-lucide-map-pin class="h-4 w-4 shrink-0 text-blue-600" aria-hidden="true" />
                                <input name="destination" type="search" wire:model="tourDestination" autocomplete="off" placeholder="{{ __('home.search.tour.destination_placeholder') }}" class="min-w-0 w-full bg-transparent text-sm font-semibold text-slate-950 outline-none placeholder:font-normal placeholder:text-slate-400">
                            </span>
                        </label>

                        <label class="min-w-0 rounded-lg border border-slate-200 bg-white px-4 py-3 transition-[border-color,box-shadow] focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-600/15">
                            <span class="block text-[11px] font-bold uppercase text-slate-500">{{ __('home.search.tour.type') }}</span>
                            <span class="relative mt-1 flex min-w-0 items-center gap-2">
                                <x-lucide-compass class="h-4 w-4 shrink-0 text-blue-600" aria-hidden="true" />
                                <select name="type" wire:model="tourType" class="min-w-0 w-full appearance-none bg-transparent pr-6 text-sm font-semibold text-slate-950 outline-none">
                                    <option value="">{{ __('home.search.tour.all_types') }}</option>
                                    <option value="domestic">{{ __('home.search.tour.domestic') }}</option>
                                    <option value="international">{{ __('home.search.tour.international') }}</option>
                                </select>
                                <x-lucide-chevron-down class="pointer-events-none absolute right-0 h-4 w-4 text-slate-400" aria-hidden="true" />
                            </span>
                        </label>
                    @elseif($activeService === 'umrah')
                        <label class="min-w-0 rounded-lg border border-slate-200 bg-white px-4 py-3 transition-[border-color,box-shadow] focus-within:border-amber-600 focus-within:ring-2 focus-within:ring-amber-600/15 lg:col-span-2">
                            <span class="block text-[11px] font-bold uppercase text-slate-500">{{ __('home.search.umrah.type') }}</span>
                            <span class="relative mt-1 flex min-w-0 items-center gap-2">
                                <x-lucide-moon-star class="h-4 w-4 shrink-0 text-amber-600" aria-hidden="true" />
                                <select name="type" wire:model="umrahType" class="min-w-0 w-full appearance-none bg-transparent pr-6 text-sm font-semibold text-slate-950 outline-none">
                                    <option value="">{{ __('home.search.umrah.all_types') }}</option>
                                    @foreach(UmrahPackageType::cases() as $packageType)
                                        <option value="{{ $packageType->value }}">{{ __('umrah.types.'.$packageType->value) }}</option>
                                    @endforeach
                                </select>
                                <x-lucide-chevron-down class="pointer-events-none absolute right-0 h-4 w-4 text-slate-400" aria-hidden="true" />
                            </span>
                        </label>
                    @elseif($activeService === 'visa')
                        <label class="min-w-0 rounded-lg border border-slate-200 bg-white px-4 py-3 transition-[border-color,box-shadow] focus-within:border-emerald-700 focus-within:ring-2 focus-within:ring-emerald-700/15 lg:col-span-2">
                            <span class="block text-[11px] font-bold uppercase text-slate-500">{{ __('home.search.visa.country') }}</span>
                            <span class="relative mt-1 flex min-w-0 items-center gap-2">
                                <x-lucide-stamp class="h-4 w-4 shrink-0 text-emerald-700" aria-hidden="true" />
                                <select name="country" wire:model="visaCountry" class="min-w-0 w-full appearance-none bg-transparent pr-6 text-sm font-semibold text-slate-950 outline-none">
                                    <option value="">{{ __('home.search.visa.all_countries') }}</option>
                                    @foreach($this->countries as $country)
                                        <option value="{{ $country->slug }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
                                <x-lucide-chevron-down class="pointer-events-none absolute right-0 h-4 w-4 text-slate-400" aria-hidden="true" />
                            </span>
                        </label>
                    @else
                        <label class="min-w-0 rounded-lg border border-slate-200 bg-white px-4 py-3 transition-[border-color,box-shadow] focus-within:border-rose-600 focus-within:ring-2 focus-within:ring-rose-600/15">
                            <span class="block text-[11px] font-bold uppercase text-slate-500">{{ __('home.search.transport.pax') }}</span>
                            <span class="mt-1 flex min-w-0 items-center gap-2">
                                <x-lucide-users class="h-4 w-4 shrink-0 text-rose-600" aria-hidden="true" />
                                <input name="pax" type="number" wire:model="transportPax" min="1" max="100" inputmode="numeric" placeholder="{{ __('home.search.transport.pax_placeholder') }}" class="min-w-0 w-full bg-transparent text-sm font-semibold text-slate-950 outline-none placeholder:font-normal placeholder:text-slate-400">
                            </span>
                        </label>

                        <label class="min-w-0 rounded-lg border border-slate-200 bg-white px-4 py-3 transition-[border-color,box-shadow] focus-within:border-rose-600 focus-within:ring-2 focus-within:ring-rose-600/15">
                            <span class="block text-[11px] font-bold uppercase text-slate-500">{{ __('home.search.transport.rate') }}</span>
                            <span class="relative mt-1 flex min-w-0 items-center gap-2">
                                <x-lucide-calendar-range class="h-4 w-4 shrink-0 text-rose-600" aria-hidden="true" />
                                <select name="rate" wire:model="transportRate" class="min-w-0 w-full appearance-none bg-transparent pr-6 text-sm font-semibold text-slate-950 outline-none">
                                    <option value="">{{ __('home.search.transport.all_rates') }}</option>
                                    <option value="daily">{{ __('home.search.transport.daily') }}</option>
                                    <option value="trip">{{ __('home.search.transport.trip') }}</option>
                                </select>
                                <x-lucide-chevron-down class="pointer-events-none absolute right-0 h-4 w-4 text-slate-400" aria-hidden="true" />
                            </span>
                        </label>
                    @endif

                    <x-ui::button type="submit" size="lg" variant="secondary" loading-target="search" :loading-text="__('home.search.searching')" class="min-h-14 w-full lg:w-auto">
                        <x-slot:icon><x-lucide-search /></x-slot:icon>
                        {{ __('home.search.submit.'.$activeService) }}
                    </x-ui::button>
                </div>

                @error('tourDestination') <p class="mt-3 text-xs font-semibold text-red-700">{{ $message }}</p> @enderror
                @error('tourType') <p class="mt-3 text-xs font-semibold text-red-700">{{ $message }}</p> @enderror
                @error('umrahType') <p class="mt-3 text-xs font-semibold text-red-700">{{ $message }}</p> @enderror
                @error('visaCountry') <p class="mt-3 text-xs font-semibold text-red-700">{{ $message }}</p> @enderror
                @error('transportPax') <p class="mt-3 text-xs font-semibold text-red-700">{{ $message }}</p> @enderror
                @error('transportRate') <p class="mt-3 text-xs font-semibold text-red-700">{{ $message }}</p> @enderror
            </form>
        </div>
    </div>
</section>
