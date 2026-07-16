<?php

use App\Models\Country;
use App\Models\VisaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders every travel service tab on the homepage', function () {
    Livewire::test('partials.home.search-panel', ['locale' => 'id'])
        ->assertSuccessful()
        ->assertSee('Tour')
        ->assertSee('Umrah')
        ->assertSee('Visa')
        ->assertSee('Transport')
        ->assertSee('Cari tour');
});

it('redirects tour and umrah searches with supported filters', function () {
    Livewire::test('partials.home.search-panel', ['locale' => 'id'])
        ->set('tourDestination', 'Tanjung Puting')
        ->set('tourType', 'domestic')
        ->call('search')
        ->assertRedirect(route('tour.index', [
            'locale' => 'id',
            'destination' => 'Tanjung Puting',
            'type' => 'domestic',
        ]));

    Livewire::test('partials.home.search-panel', ['locale' => 'en'])
        ->call('selectService', 'umrah')
        ->set('umrahType', 'ramadan')
        ->call('search')
        ->assertRedirect(route('umroh.index', [
            'locale' => 'en',
            'type' => 'ramadan',
        ]));
});

it('offers only countries with publicly available visa services', function () {
    $availableCountry = Country::factory()->create([
        'name' => ['id' => 'Arab Saudi', 'en' => 'Saudi Arabia', 'ms' => 'Arab Saudi'],
        'slug' => 'arab-saudi',
    ]);
    VisaService::factory()->for($availableCountry)->create();

    $unavailableCountry = Country::factory()->create([
        'name' => ['id' => 'Negara Tanpa Layanan', 'en' => 'Unavailable Country', 'ms' => 'Negara Tanpa Perkhidmatan'],
        'slug' => 'tanpa-layanan',
    ]);

    Livewire::test('partials.home.search-panel', ['locale' => 'id'])
        ->call('selectService', 'visa')
        ->assertSee('Arab Saudi')
        ->assertDontSee($unavailableCountry->name)
        ->set('visaCountry', $availableCountry->slug)
        ->call('search')
        ->assertRedirect(route('visa.index', [
            'locale' => 'id',
            'country' => 'arab-saudi',
        ]));
});

it('validates transport capacity and redirects valid searches', function () {
    Livewire::test('partials.home.search-panel', ['locale' => 'id'])
        ->call('selectService', 'transport')
        ->set('transportPax', '0')
        ->call('search')
        ->assertHasErrors(['transportPax' => 'min'])
        ->set('transportPax', '8')
        ->set('transportRate', 'daily')
        ->call('search')
        ->assertHasNoErrors()
        ->assertRedirect(route('transport.index', [
            'locale' => 'id',
            'pax' => '8',
            'rate' => 'daily',
        ]));
});

it('falls back to tour when an unsupported service is submitted', function () {
    Livewire::test('partials.home.search-panel', ['locale' => 'id'])
        ->set('activeService', 'unsupported')
        ->call('search')
        ->assertSet('activeService', 'tour')
        ->assertRedirect(route('tour.index', ['locale' => 'id']));
});
