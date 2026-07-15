<?php

use App\Enums\UmrahPackageType;
use App\Enums\UmrahRoomType;
use App\Filament\Resources\UmrahPackages\Pages\CreateUmrahPackage;
use App\Filament\Resources\UmrahPackages\Pages\ListUmrahPackages;
use App\Filament\Resources\UmrahPackages\Pages\ViewUmrahPackage;
use App\Filament\Resources\UmrahPackages\UmrahPackageResource;
use App\Models\UmrahPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('registers umrah package management in filament navigation', function () {
    expect(UmrahPackageResource::shouldRegisterNavigation())->toBeTrue()
        ->and(UmrahPackageResource::getNavigationGroup())->toBe('Layanan Umrah')
        ->and(UmrahPackageResource::getNavigationLabel())->toBe('Paket Umrah');
});

it('creates an umrah package with translated public information', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CreateUmrahPackage::class)
        ->fillForm([
            'name' => ['id' => 'Umrah Reguler Awal Musim'],
            'slug' => ['id' => 'umrah-reguler-awal-musim'],
            'description' => ['id' => '<p>Perjalanan umrah selama sembilan hari.</p>'],
            'package_type' => UmrahPackageType::Regular->value,
            'duration_days' => 9,
            'price_idr' => 28_500_000,
            'airline' => 'Saudia',
            'hotel_makkah' => 'Hotel Makkah',
            'hotel_makkah_stars' => 4,
            'hotel_madinah' => 'Hotel Madinah',
            'hotel_madinah_stars' => 4,
            'visa_included' => true,
            'handling_included' => true,
            'is_active' => true,
            'is_featured' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified()
        ->assertRedirect();

    $package = UmrahPackage::query()->sole();

    expect($package->getTranslation('name', 'id'))->toBe('Umrah Reguler Awal Musim')
        ->and($package->getTranslation('slug', 'id'))->toBe('umrah-reguler-awal-musim')
        ->and($package->package_type)->toBe(UmrahPackageType::Regular->value)
        ->and($package->is_featured)->toBeTrue();
});

it('lists operational package pricing and the next departure', function () {
    $this->actingAs(User::factory()->create());
    app()->setLocale('id');

    $package = UmrahPackage::create([
        'name' => ['id' => 'Umrah Plus Turki'],
        'slug' => ['id' => 'umrah-plus-turki'],
        'package_type' => UmrahPackageType::Plus->value,
        'duration_days' => 12,
        'price_idr' => 35_000_000,
        'is_active' => true,
        'is_featured' => true,
    ]);

    $package->prices()->create([
        'room_type' => UmrahRoomType::Quad->value,
        'price_idr' => 32_500_000,
    ]);

    $package->departures()->create([
        'departure_date' => today()->addMonth(),
        'return_date' => today()->addMonth()->addDays(12),
        'quota_total' => 40,
        'quota_booked' => 8,
        'status' => 'open',
    ]);

    Livewire::test(ListUmrahPackages::class)
        ->assertCanSeeTableRecords([$package])
        ->assertSee('Umrah Plus Turki')
        ->assertSee('32.500.000,00')
        ->assertSee(today()->addMonth()->translatedFormat('d M Y'));
});

it('shows package pricing and accommodation in the infolist', function () {
    $this->actingAs(User::factory()->create());
    app()->setLocale('id');

    $package = UmrahPackage::create([
        'name' => ['id' => 'Umrah Ramadan'],
        'slug' => ['id' => 'umrah-ramadan'],
        'description' => ['id' => '<p>Paket akhir Ramadan.</p>'],
        'package_type' => UmrahPackageType::Ramadan->value,
        'duration_days' => 15,
        'price_idr' => 45_000_000,
        'airline' => 'Garuda Indonesia',
        'hotel_makkah' => 'Makkah Towers',
        'is_active' => true,
    ]);

    $package->prices()->create([
        'room_type' => UmrahRoomType::Double->value,
        'price_idr' => 49_500_000,
    ]);

    Livewire::test(ViewUmrahPackage::class, ['record' => $package->getRouteKey()])
        ->assertSee('Umrah Ramadan')
        ->assertSee('Ramadan')
        ->assertSee('Double (2 orang)')
        ->assertSee('49.500.000,00')
        ->assertSee('Garuda Indonesia')
        ->assertSee('Makkah Towers');
});
