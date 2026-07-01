<?php

use App\Filament\Resources\UmrahPackages\UmrahPackageResource;
use App\Filament\Resources\Vehicles\VehicleResource;
use App\Models\TourGallery;

it('hides non mvp service resources from filament navigation', function () {
    expect(UmrahPackageResource::shouldRegisterNavigation())->toBeFalse()
        ->and(VehicleResource::shouldRegisterNavigation())->toBeFalse();
});

it('keeps tour gallery captions readable for legacy and localized values', function () {
    app()->setLocale('en');

    expect((new TourGallery(['caption' => 'Legacy caption']))->localized_caption)
        ->toBe('Legacy caption')
        ->and((new TourGallery(['caption' => [
            'id' => 'Caption Indonesia',
            'en' => 'English caption',
            'ms' => 'Caption Melayu',
        ]]))->localized_caption)
        ->toBe('English caption');
});
