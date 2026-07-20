<?php

use App\Livewire\VehicleBookingForm;
use App\Models\Vehicle;
use App\Models\VehicleRentalArea;
use App\Models\VehicleRentalRate;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $settings = app(GeneralSettings::class);
    $settings->whatsapp_number = '+6281234567890';
    $settings->save();
});

function createVehicleRentalRate(Vehicle $vehicle, int $price = 750000, int $minimumDays = 1, string $slug = 'jakarta'): VehicleRentalRate
{
    $area = VehicleRentalArea::factory()->create([
        'slug' => $slug,
        'minimum_rental_days' => $minimumDays,
    ]);

    return VehicleRentalRate::factory()
        ->for($vehicle)
        ->for($area, 'area')
        ->create([
            'price_per_day_idr' => $price,
            'valid_from' => today()->startOfYear(),
            'valid_until' => today()->endOfYear(),
        ]);
}

it('calculates daily rental on the server and redirects a valid request to whatsapp', function () {
    $vehicle = Vehicle::factory()->create([
        'capacity_pax' => 8,
        'price_per_day_idr' => 750000,
        'price_per_trip_idr' => 1200000,
    ]);
    createVehicleRentalRate($vehicle);
    $pickupDate = today()->addMonth();

    Livewire::test(VehicleBookingForm::class, [
        'vehicle' => $vehicle,
        'initialArea' => 'jakarta',
        'initialPax' => 4,
    ])
        ->set('customerName', 'Raka Haikal')
        ->set('whatsappNumber', '081234567890')
        ->set('email', 'raka@example.com')
        ->set('pickupDate', $pickupDate->toDateString())
        ->set('pickupTime', '08:30')
        ->set('rentalDays', '3')
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

it('enforces the regional minimum rental duration', function () {
    $vehicle = Vehicle::factory()->create([
        'capacity_pax' => 8,
    ]);
    createVehicleRentalRate($vehicle, minimumDays: 5, slug: 'malang');
    $pickupDate = today()->addMonth();

    Livewire::test(VehicleBookingForm::class, [
        'vehicle' => $vehicle,
        'initialArea' => 'malang',
    ])
        ->assertSet('rentalDays', '5')
        ->set('pickupDate', $pickupDate->toDateString())
        ->set('rentalDays', '4')
        ->call('submit')
        ->assertHasErrors(['rentalDays' => 'min']);
});

it('validates dates, route, contact details, and passenger capacity', function () {
    $vehicle = Vehicle::factory()->create(['capacity_pax' => 6]);
    createVehicleRentalRate($vehicle, minimumDays: 5, slug: 'malang');

    Livewire::test(VehicleBookingForm::class, ['vehicle' => $vehicle])
        ->set('customerName', '')
        ->set('whatsappNumber', 'invalid phone')
        ->set('email', 'invalid-email')
        ->set('pickupDate', today()->subDay()->toDateString())
        ->set('pickupTime', 'invalid')
        ->set('area', 'malang')
        ->set('rentalDays', '2')
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
            'rentalDays' => 'min',
            'pickupLocation' => 'required',
            'destination' => 'required',
            'pax' => 'max',
        ]);
});

it('keeps the request form open when whatsapp is unavailable', function () {
    $vehicle = Vehicle::factory()->create();
    createVehicleRentalRate($vehicle);
    $settings = app(GeneralSettings::class);
    $settings->whatsapp_number = '';
    $settings->save();
    $pickupDate = today()->addMonth();

    Livewire::test(VehicleBookingForm::class, ['vehicle' => $vehicle])
        ->set('customerName', 'Raka Haikal')
        ->set('whatsappNumber', '081234567890')
        ->set('pickupDate', $pickupDate->toDateString())
        ->set('pickupTime', '08:30')
        ->set('rentalDays', '2')
        ->set('pickupLocation', 'Bandara')
        ->set('destination', 'Hotel')
        ->call('submit')
        ->assertHasErrors(['service']);
});
