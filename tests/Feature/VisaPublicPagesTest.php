<?php

use App\Enums\VisaItemType;
use App\Livewire\VisaCatalog;
use App\Livewire\VisaInquiry;
use App\Models\Country;
use App\Models\VisaService;
use App\Models\VisaServiceItem;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

function visaCountry(array $attributes = []): Country
{
    return Country::factory()->create([
        'name' => ['id' => 'Mesir', 'en' => 'Egypt', 'ms' => 'Mesir'],
        'slug' => 'mesir',
        'iso_alpha_2' => 'EG',
        'iso_alpha_3' => 'EGY',
        ...$attributes,
    ]);
}

function visaService(Country $country, array $attributes = []): VisaService
{
    return VisaService::factory()->for($country)->create([
        'name' => ['id' => 'Visa Kunjungan Mesir', 'en' => 'Egypt Visit Visa', 'ms' => 'Visa Lawatan Mesir'],
        'slug' => 'visa-kunjungan-mesir',
        'visa_type' => ['id' => 'Kunjungan', 'en' => 'Visit', 'ms' => 'Lawatan'],
        'summary' => [
            'id' => 'Pendampingan pengurusan visa untuk kunjungan ke Mesir.',
            'en' => 'Visa application assistance for a visit to Egypt.',
            'ms' => 'Bantuan permohonan visa untuk lawatan ke Mesir.',
        ],
        'description' => [
            'id' => '<p>Tim membantu pemeriksaan dokumen sebelum pengajuan.</p>',
            'en' => '<p>Our team reviews your documents before submission.</p>',
            'ms' => '<p>Pasukan kami menyemak dokumen sebelum penyerahan.</p>',
        ],
        'price_idr' => 1_500_000,
        ...$attributes,
    ]);
}

it('only lists services belonging to active countries and filters the catalog by country', function () {
    $egypt = visaCountry();
    $saudiArabia = visaCountry([
        'name' => ['id' => 'Arab Saudi', 'en' => 'Saudi Arabia', 'ms' => 'Arab Saudi'],
        'slug' => 'arab-saudi',
        'iso_alpha_2' => 'SA',
        'iso_alpha_3' => 'SAU',
    ]);
    $egyptService = visaService($egypt);
    $saudiService = visaService($saudiArabia, [
        'name' => ['id' => 'Visa Wisata Saudi', 'en' => 'Saudi Tourist Visa', 'ms' => 'Visa Pelancong Saudi'],
        'slug' => 'visa-wisata-saudi',
    ]);
    visaService($egypt, ['slug' => 'layanan-nonaktif', 'is_active' => false]);

    $inactiveCountry = visaCountry(['slug' => 'negara-nonaktif', 'iso_alpha_2' => 'XX', 'iso_alpha_3' => 'XXX', 'is_active' => false]);
    visaService($inactiveCountry, ['slug' => 'visa-negara-nonaktif']);

    $deletedCountry = visaCountry(['slug' => 'negara-terhapus', 'iso_alpha_2' => 'YY', 'iso_alpha_3' => 'YYY']);
    visaService($deletedCountry, ['slug' => 'visa-negara-terhapus']);
    $deletedCountry->delete();

    get('/id/visa')
        ->assertOk()
        ->assertSee($egyptService->name)
        ->assertSee($saudiService->name)
        ->assertDontSee('layanan-nonaktif')
        ->assertDontSee('visa-negara-nonaktif')
        ->assertDontSee('visa-negara-terhapus');

    Livewire::test(VisaCatalog::class)
        ->call('selectCountry', 'mesir')
        ->assertSet('country', 'mesir')
        ->assertSee($egyptService->name)
        ->assertDontSee($saudiService->name);
});

it('renders a localized visa detail page with grouped items and structured data', function () {
    $service = visaService(visaCountry());
    VisaServiceItem::factory()->for($service)->create([
        'type' => VisaItemType::Requirement,
        'content' => ['id' => 'Paspor asli', 'en' => 'Original passport', 'ms' => 'Pasport asal'],
        'details' => ['id' => 'Berlaku enam bulan.', 'en' => 'Valid for six months.', 'ms' => 'Sah selama enam bulan.'],
    ]);

    get('/en/visa/'.$service->slug)
        ->assertOk()
        ->assertSee('Egypt Visit Visa')
        ->assertSee('Original passport')
        ->assertSee('Document requirements')
        ->assertSee('<link rel="canonical" href="'.route('visa.show', ['locale' => 'en', 'visaService' => $service->slug]).'">', false)
        ->assertSee('<link rel="alternate" hreflang="id" href="'.route('visa.show', ['locale' => 'id', 'visaService' => $service->slug]).'">', false)
        ->assertSee('"@type":"Service"', false)
        ->assertSee('"@type":"Offer"', false)
        ->assertSee('"price":1500000', false);
});

it('omits an offer from structured data when a service has no fixed price', function () {
    $service = visaService(visaCountry(), ['price_idr' => null]);

    get('/id/visa/'.$service->slug)
        ->assertOk()
        ->assertSee('Hubungi admin')
        ->assertDontSee('"@type":"Offer"', false)
        ->assertDontSee('"price":0', false);
});

it('redirects numeric service URLs to the canonical slug', function () {
    $service = visaService(visaCountry());

    get('/id/visa/'.$service->id)
        ->assertRedirect(route('visa.show', ['locale' => 'id', 'visaService' => $service->slug]))
        ->assertStatus(301);
});

it('returns not found for services that are not publicly available', function (string $state) {
    $country = visaCountry();
    $service = visaService($country);

    match ($state) {
        'inactive service' => $service->update(['is_active' => false]),
        'inactive country' => $country->update(['is_active' => false]),
        'deleted country' => $country->delete(),
    };

    get('/id/visa/'.$service->slug)->assertNotFound();
})->with(['inactive service', 'inactive country', 'deleted country']);

it('validates and redirects visa inquiries to whatsapp without storing applicant data', function () {
    app()->setLocale('id');
    $service = visaService(visaCountry(), ['price_idr' => null]);
    $settings = app(GeneralSettings::class);
    $settings->whatsapp_number = '+62 812-3456-7890';
    $settings->save();

    $component = Livewire::test(VisaInquiry::class, ['service' => $service])
        ->set('customerName', '')
        ->set('applicants', '0')
        ->call('submit')
        ->assertHasErrors(['customerName' => 'required', 'applicants' => 'min'])
        ->set('customerName', 'Budi Santoso')
        ->set('applicants', '2')
        ->set('notes', 'Perjalanan keluarga')
        ->call('submit')
        ->assertHasNoErrors();

    $intro = __('visa.inquiry.whatsapp_intro', ['service' => $service->name]);
    $details = implode("\n", [
        __('visa.inquiry.whatsapp_heading'),
        __('visa.inquiry.whatsapp_service', ['value' => $service->name]),
        __('visa.inquiry.whatsapp_country', ['value' => $service->country->name]),
        __('visa.inquiry.whatsapp_type', ['value' => $service->visa_type]),
        __('visa.inquiry.whatsapp_name', ['value' => 'Budi Santoso']),
        __('visa.inquiry.whatsapp_applicants', ['value' => 2]),
        __('visa.inquiry.whatsapp_departure', ['value' => __('visa.inquiry.not_determined')]),
        __('visa.inquiry.whatsapp_price', ['value' => __('visa.price_on_request')]),
        __('visa.inquiry.whatsapp_notes', ['value' => 'Perjalanan keluarga']),
    ]);

    $component->assertRedirect('https://wa.me/6281234567890?text='.rawurlencode($intro."\n\n".$details));
});

it('keeps the inquiry form on the page when whatsapp is unavailable', function () {
    $service = visaService(visaCountry());
    $settings = app(GeneralSettings::class);
    $settings->whatsapp_number = '';
    $settings->save();

    Livewire::test(VisaInquiry::class, ['service' => $service])
        ->set('customerName', 'Budi Santoso')
        ->set('applicants', '1')
        ->call('submit')
        ->assertHasErrors('service')
        ->assertNoRedirect();
});
