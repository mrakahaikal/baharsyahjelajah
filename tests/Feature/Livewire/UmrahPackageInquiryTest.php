<?php

use App\Livewire\UmrahPackageInquiry;
use App\Models\UmrahDeparturePrice;
use App\Models\UmrahPackage;
use App\Models\UmrahPackagePrice;
use App\Models\UmrahPackagePrivatePrice;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createInquiryPackage(): UmrahPackage
{
    $package = UmrahPackage::factory()->create([
        'name' => ['id' => 'Umrah Reguler', 'en' => 'Regular Umrah', 'ms' => 'Umrah Reguler'],
        'slug' => ['id' => 'umrah-reguler', 'en' => 'regular-umrah', 'ms' => 'umrah-reguler'],
    ]);
    $quad = UmrahPackagePrice::factory()->for($package, 'package')->create([
        'room_type' => 'quad',
        'price_idr' => 30_000_000,
    ]);
    UmrahPackagePrice::factory()->for($package, 'package')->create([
        'room_type' => 'double',
        'price_idr' => 35_000_000,
    ]);
    $departure = $package->departures()->create([
        'departure_date' => now()->addMonth(),
        'return_date' => now()->addMonth()->addDays(9),
        'quota_total' => 10,
        'quota_booked' => 4,
        'status' => 'open',
    ]);
    UmrahDeparturePrice::query()->create([
        'umrah_departure_id' => $departure->id,
        'umrah_package_price_id' => $quad->id,
        'price_idr' => 31_000_000,
    ]);

    $settings = app(GeneralSettings::class);
    $settings->default_pax = 2;
    $settings->whatsapp_number = '628123456789';
    $settings->save();

    return $package;
}

it('recalculates the departure room rate and WhatsApp summary', function () {
    $package = createInquiryPackage();
    $double = $package->prices()->where('room_type', 'double')->firstOrFail();

    Livewire::test(UmrahPackageInquiry::class, ['package' => $package])
        ->assertSet('pax', '2')
        ->assertSee('Rp 31.000.000')
        ->assertSee('Rp 62.000.000')
        ->set('selectedPackagePriceId', $double->id)
        ->set('pax', '3')
        ->assertSee('Rp 35.000.000')
        ->assertSee('Rp 105.000.000')
        ->assertSee('wa.me/628123456789', false);
});

it('rejects participant counts above the selected departure quota', function () {
    $package = createInquiryPackage();

    Livewire::test(UmrahPackageInquiry::class, ['package' => $package])
        ->set('pax', '7')
        ->assertSee('Masukkan 1 sampai 6 jamaah.')
        ->assertDontSee('wa.me/628123456789', false);
});

function createPrivateInquiryPackage(): UmrahPackage
{
    $package = UmrahPackage::factory()->create([
        'name' => ['id' => 'Private Umrah', 'en' => 'Private Umrah', 'ms' => 'Umrah Swasta'],
        'slug' => ['id' => 'private-umrah', 'en' => 'private-umrah', 'ms' => 'private-umrah'],
        'package_type' => 'private',
    ]);

    UmrahPackagePrivatePrice::create([
        'umrah_package_id' => $package->id,
        'duration_nights' => 6,
        'pax' => 4,
        'price_idr' => 14_000_000,
    ]);

    UmrahPackagePrivatePrice::create([
        'umrah_package_id' => $package->id,
        'duration_nights' => 6,
        'pax' => 8,
        'price_idr' => 12_000_000,
    ]);

    UmrahPackagePrivatePrice::create([
        'umrah_package_id' => $package->id,
        'duration_nights' => 10,
        'pax' => 4,
        'price_idr' => 16_000_000,
    ]);

    $settings = app(GeneralSettings::class);
    $settings->whatsapp_number = '628123456789';
    $settings->save();

    return $package;
}

it('recalculates private package pricing and WhatsApp summary', function () {
    $package = createPrivateInquiryPackage();

    Livewire::test(UmrahPackageInquiry::class, ['package' => $package])
        ->assertSet('selectedDurationNights', 6)
        ->assertSet('selectedPax', 4)
        ->assertSet('pax', '4')
        ->assertSee('Rp 14.000.000')
        ->assertSee('Rp 56.000.000')
        ->set('selectedDurationNights', 10)
        ->assertSet('selectedPax', 4)
        ->assertSee('Rp 16.000.000')
        ->assertSee('Rp 64.000.000')
        ->set('selectedDurationNights', 6)
        ->set('selectedPax', 8)
        ->assertSee('Rp 12.000.000')
        ->assertSee('Rp 96.000.000')
        ->assertSee('wa.me/628123456789', false);
});
