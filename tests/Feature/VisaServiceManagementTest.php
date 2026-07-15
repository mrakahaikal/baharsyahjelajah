<?php

use App\Enums\VisaEntryType;
use App\Enums\VisaItemType;
use App\Filament\Resources\Countries\CountryResource;
use App\Filament\Resources\Countries\Pages\CreateCountry;
use App\Filament\Resources\Countries\Pages\EditCountry;
use App\Filament\Resources\VisaServices\Pages\CreateVisaService;
use App\Filament\Resources\VisaServices\Pages\EditVisaService;
use App\Filament\Resources\VisaServices\Pages\ListVisaServices;
use App\Filament\Resources\VisaServices\Pages\ViewVisaService;
use App\Filament\Resources\VisaServices\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\VisaServices\VisaServiceResource;
use App\Models\Country;
use App\Models\User;
use App\Models\VisaService;
use App\Models\VisaServiceItem;
use Database\Seeders\CountrySeeder;
use Database\Seeders\VisaServiceSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('registers country and visa service management in filament', function () {
    expect(CountryResource::shouldRegisterNavigation())->toBeTrue()
        ->and(CountryResource::getNavigationGroup())->toBe('Layanan Visa')
        ->and(VisaServiceResource::shouldRegisterNavigation())->toBeTrue()
        ->and(VisaServiceResource::getNavigationGroup())->toBe('Layanan Visa');
});

it('creates a translated destination country through filament', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CreateCountry::class)
        ->fillForm([
            'name' => ['id' => 'Mesir', 'en' => 'Egypt', 'ms' => 'Mesir'],
            'slug' => 'mesir',
            'iso_alpha_2' => 'eg',
            'iso_alpha_3' => 'egy',
            'is_active' => true,
            'sort_order' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified()
        ->assertRedirect();

    $country = Country::query()->sole();

    expect($country->getTranslation('name', 'en'))->toBe('Egypt')
        ->and($country->iso_alpha_2)->toBe('EG')
        ->and($country->iso_alpha_3)->toBe('EGY')
        ->and($country->is_active)->toBeTrue();
});

it('creates a visa service for an active managed country', function () {
    $this->actingAs(User::factory()->create());
    $country = Country::factory()->create([
        'name' => ['id' => 'Arab Saudi', 'en' => 'Saudi Arabia', 'ms' => 'Arab Saudi'],
        'iso_alpha_2' => 'SA',
        'iso_alpha_3' => 'SAU',
    ]);

    Livewire::test(CreateVisaService::class)
        ->fillForm([
            'country_id' => $country->id,
            'name' => ['id' => 'Visa Kunjungan Saudi', 'en' => 'Saudi Visit Visa', 'ms' => 'Visa Lawatan Saudi'],
            'slug' => 'visa-kunjungan-saudi',
            'visa_type' => ['id' => 'Kunjungan', 'en' => 'Visit', 'ms' => 'Lawatan'],
            'summary' => ['id' => 'Layanan untuk pemegang paspor Indonesia.'],
            'description' => ['id' => '<p>Pendampingan dokumen Visa.</p>'],
            'entry_type' => VisaEntryType::Single->value,
            'processing_days_min' => 5,
            'processing_days_max' => 10,
            'validity_days' => 90,
            'maximum_stay_days' => 30,
            'price_idr' => null,
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified()
        ->assertRedirect();

    $service = VisaService::query()->sole();

    expect($service->country->is($country))->toBeTrue()
        ->and($service->entry_type)->toBe(VisaEntryType::Single)
        ->and($service->getTranslation('name', 'en'))->toBe('Saudi Visit Visa')
        ->and($service->formatted_price)->toBeNull()
        ->and($service->is_featured)->toBeTrue();
});

it('validates country, translated identity, and processing range', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CreateVisaService::class)
        ->fillForm([
            'country_id' => null,
            'name' => ['id' => null],
            'slug' => null,
            'visa_type' => ['id' => null],
            'processing_days_min' => 10,
            'processing_days_max' => 5,
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 0,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'country_id' => 'required',
            'name.id' => 'required',
            'slug' => 'required',
            'visa_type.id' => 'required',
            'processing_days_max' => 'gte',
        ])
        ->assertNotNotified();
});

it('keeps an inactive or deleted country available on its existing visa service', function () {
    $this->actingAs(User::factory()->create());
    $country = Country::factory()->create(['is_active' => false]);
    $service = VisaService::factory()->for($country)->create();

    $country->delete();

    expect($service->fresh()->country->is($country))->toBeTrue();

    Livewire::test(EditVisaService::class, ['record' => $service->getRouteKey()])
        ->assertSet('data.country_id', $country->id)
        ->assertHasNoFormErrors();
});

it('restricts country force deletion and cascades visa service items', function () {
    $this->actingAs(User::factory()->create());
    $country = Country::factory()->create();
    $service = VisaService::factory()->for($country)->create();
    $item = VisaServiceItem::factory()->for($service)->create();

    expect(fn () => $country->forceDelete())->toThrow(QueryException::class);

    Livewire::test(EditCountry::class, ['record' => $country->getRouteKey()])
        ->assertSuccessful();

    $service->forceDelete();

    expect(VisaServiceItem::query()->whereKey($item)->exists())->toBeFalse();
});

it('lists and displays visa services with their managed items', function () {
    $this->actingAs(User::factory()->create());
    app()->setLocale('id');

    $country = Country::factory()->create(['name' => ['id' => 'Mesir']]);
    $service = VisaService::factory()->for($country)->create([
        'name' => ['id' => 'Visa Turis Mesir'],
        'price_idr' => 2_500_000,
    ]);
    $item = VisaServiceItem::factory()->for($service)->create([
        'type' => VisaItemType::Requirement,
        'content' => ['id' => 'Paspor Indonesia'],
        'sort_order' => 1,
    ]);

    Livewire::test(ListVisaServices::class)
        ->assertCanSeeTableRecords([$service])
        ->assertSee('Visa Turis Mesir')
        ->assertSee('Mesir')
        ->assertSee('2.500.000,00')
        ->filterTable('country', $country->id)
        ->assertCanSeeTableRecords([$service]);

    Livewire::test(ViewVisaService::class, ['record' => $service->getRouteKey()])
        ->assertSee('Visa Turis Mesir')
        ->assertSee('Paspor Indonesia');

    Livewire::test(ItemsRelationManager::class, [
        'ownerRecord' => $service,
        'pageClass' => EditVisaService::class,
    ])->assertCanSeeTableRecords([$item]);
});

it('seeds visa catalog idempotently', function () {
    $this->seed(CountrySeeder::class);
    $this->seed(VisaServiceSeeder::class);
    $this->seed(CountrySeeder::class);
    $this->seed(VisaServiceSeeder::class);

    expect(Country::query()->count())->toBe(2)
        ->and(VisaService::query()->count())->toBe(2)
        ->and(VisaServiceItem::query()->count())->toBe(6);
});
