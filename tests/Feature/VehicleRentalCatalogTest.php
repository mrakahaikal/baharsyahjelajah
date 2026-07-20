<?php

use App\Filament\Resources\VehicleRentalAreas\VehicleRentalAreaResource;
use App\Filament\Resources\VehicleRentalTerms\VehicleRentalTermResource;
use App\Models\Faq;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleRentalArea;
use App\Models\VehicleRentalRate;
use App\Models\VehicleRentalTerm;
use Database\Seeders\FaqSeeder;
use Database\Seeders\VehicleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('seeds the client vehicle matrix and localized rental policies', function () {
    $this->seed(VehicleSeeder::class);
    $this->seed(FaqSeeder::class);

    expect(Vehicle::query()->active()->whereNotNull('catalog_code')->count())->toBe(18)
        ->and(VehicleRentalArea::query()->count())->toBe(5)
        ->and(VehicleRentalRate::query()->count())->toBe(48)
        ->and(VehicleRentalTerm::query()->count())->toBe(8);

    $bandung = VehicleRentalArea::query()->where('slug', 'bandung')->firstOrFail();
    $innovaSixSeat = Vehicle::query()->where('catalog_code', 'innova-reborn-6-seat')->firstOrFail();
    $regularInnova = Vehicle::query()->where('catalog_code', 'innova-reborn')->firstOrFail();

    expect($innovaSixSeat->rentalRates()->forArea($bandung)->value('price_per_day_idr'))->toBe(1495000)
        ->and($regularInnova->rentalRates()->forArea($bandung)->value('price_per_day_idr'))->toBe(1092500)
        ->and(VehicleRentalArea::query()->where('slug', 'malang')->value('minimum_rental_days'))->toBe(5)
        ->and(VehicleRentalArea::query()->where('slug', 'bromo-banyuwangi')->value('minimum_rental_days'))->toBe(6);

    $included = VehicleRentalTerm::query()->where('code', 'included')->firstOrFail();

    expect($included->getTranslation('title', 'en'))->toBe('Included in the Rate')
        ->and($included->getTranslation('content', 'ms'))->toContain('Kenderaan bersih')
        ->and(Faq::query()->where('category', 'vehicle')->where('sort_order', 2)->firstOrFail()->answer)
        ->toContain('Malang minimum 5 hari');
});

it('rejects overlapping active rates for the same vehicle and area', function () {
    $vehicle = Vehicle::factory()->create();
    $area = VehicleRentalArea::factory()->create();

    VehicleRentalRate::factory()->for($vehicle)->for($area, 'area')->create([
        'valid_from' => '2026-01-01',
        'valid_until' => '2026-12-31',
    ]);

    expect(fn () => VehicleRentalRate::factory()->for($vehicle)->for($area, 'area')->create([
        'valid_from' => '2026-06-01',
        'valid_until' => '2027-05-31',
    ]))->toThrow(ValidationException::class, 'bertumpang tindih');
});

it('allows administrators to manage rental areas and terms', function () {
    actingAs(User::factory()->create(['email' => 'transport-catalog-admin@baharsyahjelajah.com']));

    get(VehicleRentalAreaResource::getUrl('index'))->assertSuccessful();
    get(VehicleRentalAreaResource::getUrl('create'))->assertSuccessful();
    get(VehicleRentalTermResource::getUrl('index'))->assertSuccessful();
    get(VehicleRentalTermResource::getUrl('create'))->assertSuccessful();
});
