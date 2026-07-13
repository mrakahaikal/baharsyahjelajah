<?php

use App\Filament\Resources\UmrahPackages\UmrahPackageResource;
use App\Filament\Resources\Vehicles\VehicleResource;
use App\Models\TourPackage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

it('hides non mvp service resources from filament navigation', function () {
    expect(UmrahPackageResource::shouldRegisterNavigation())->toBeFalse()
        ->and(VehicleResource::shouldRegisterNavigation())->toBeFalse();
});

it('keeps tour media captions readable for scalar and localized values', function () {
    app()->setLocale('en');

    $package = new TourPackage;
    $legacyMedia = (new Media)->setCustomProperty('caption', 'Legacy caption');
    $localizedMedia = (new Media)->setCustomProperty('caption', [
        'id' => 'Caption Indonesia',
        'en' => 'English caption',
        'ms' => 'Caption Melayu',
    ]);

    expect($package->localizedMediaCaption($legacyMedia))
        ->toBe('Legacy caption')
        ->and($package->localizedMediaCaption($localizedMedia))
        ->toBe('English caption');
});
