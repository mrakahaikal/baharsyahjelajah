<?php

use App\Livewire\UmrahPackageInquiry;
use App\Models\UmrahDeparturePrice;
use App\Models\UmrahPackage;
use App\Models\UmrahPackagePrice;
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
