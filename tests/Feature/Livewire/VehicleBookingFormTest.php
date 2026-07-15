<?php

use App\Livewire\VehicleBookingForm;
use App\Models\Vehicle;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $settings = app(GeneralSettings::class);
    $settings->whatsapp_number = '+6281234567890';
    $settings->save();
});

it('calculates daily rental on the server and redirects a valid request to whatsapp', function () {
    $vehicle = Vehicle::factory()->create([
        'capacity_pax' => 8,
        'price_per_day_idr' => 750000,
        'price_per_trip_idr' => 1200000,
    ]);
    $pickupDate = today()->addMonth();

    Livewire::test(VehicleBookingForm::class, [
        'vehicle' => $vehicle,
        'initialRate' => 'daily',
        'initialPax' => 4,
    ])
        ->set('customerName', 'Raka Haikal')
        ->set('whatsappNumber', '081234567890')
        ->set('email', 'raka@example.com')
        ->set('pickupDate', $pickupDate->toDateString())
        ->set('pickupTime', '08:30')
        ->set('returnDate', $pickupDate->copy()->addDays(3)->toDateString())
        ->set('pickupLocation', 'Bandara Soekarno-Hatta')
        ->set('destination', 'Bandung')
        ->set('notes', 'Membawa empat koper')
        ->assertSee('Rp 2.250.000')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirectContains('https://wa.me/6281234567890')
        ->assertRedirectContains('Raka%20Haikal')
        ->assertRedirectContains('Rp%202.250.000');

    expect(Vehicle::query()->count())->toBe(1);
});

it('treats the trip price as a starting estimate', function () {
    $vehicle = Vehicle::factory()->create([
        'price_per_day_idr' => 750000,
        'price_per_trip_idr' => 1200000,
    ]);

    Livewire::test(VehicleBookingForm::class, [
        'vehicle' => $vehicle,
        'initialRate' => 'trip',
    ])
        ->assertSet('rate', 'trip')
        ->assertSee('Harga mulai')
        ->assertSee('Rp 1.200.000');
});

it('validates dates, route, contact details, and passenger capacity', function () {
    $vehicle = Vehicle::factory()->create(['capacity_pax' => 6]);

    Livewire::test(VehicleBookingForm::class, ['vehicle' => $vehicle])
        ->set('customerName', '')
        ->set('whatsappNumber', 'invalid phone')
        ->set('email', 'invalid-email')
        ->set('pickupDate', today()->subDay()->toDateString())
        ->set('pickupTime', 'invalid')
        ->set('returnDate', today()->subDays(2)->toDateString())
        ->set('pickupLocation', '')
        ->set('destination', '')
        ->set('pax', '7')
        ->call('submit')
        ->assertHasErrors([
            'customerName' => 'required',
            'whatsappNumber' => 'regex',
            'email' => 'email',
            'pickupDate' => 'after_or_equal',
            'pickupTime' => 'date_format',
            'returnDate' => 'after_or_equal',
            'pickupLocation' => 'required',
            'destination' => 'required',
            'pax' => 'max',
        ]);
});

it('keeps the request form open when whatsapp is unavailable', function () {
    $vehicle = Vehicle::factory()->create();
    $settings = app(GeneralSettings::class);
    $settings->whatsapp_number = '';
    $settings->save();
    $pickupDate = today()->addMonth();

    Livewire::test(VehicleBookingForm::class, ['vehicle' => $vehicle])
        ->set('customerName', 'Raka Haikal')
        ->set('whatsappNumber', '081234567890')
        ->set('pickupDate', $pickupDate->toDateString())
        ->set('pickupTime', '08:30')
        ->set('returnDate', $pickupDate->copy()->addDay()->toDateString())
        ->set('pickupLocation', 'Bandara')
        ->set('destination', 'Hotel')
        ->call('submit')
        ->assertHasErrors(['service']);
});
