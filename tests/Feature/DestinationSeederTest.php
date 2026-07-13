<?php

use App\Models\Destination;
use Database\Seeders\DestinationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds five localized destinations without assigning them to another model', function () {
    $this->seed(DestinationSeeder::class);
    $this->seed(DestinationSeeder::class);

    $destinations = Destination::query()->orderBy('id')->get();
    $tanjungPuting = $destinations->firstWhere('slug', 'taman-nasional-tanjung-puting');

    expect($destinations)->toHaveCount(5)
        ->and($destinations->pluck('slug')->unique())->toHaveCount(5)
        ->and($destinations->every(
            fn (Destination $destination): bool => filled($destination->location)
                && filled($destination->map_url)
                && $destination->tourPackages->isEmpty()
                && $destination->itineraries->isEmpty()
                && collect(['id', 'en', 'ms'])->every(
                    fn (string $locale): bool => filled($destination->getTranslationWithoutFallback('name', $locale))
                        && filled($destination->getTranslationWithoutFallback('description', $locale)),
                ),
        ))->toBeTrue()
        ->and($tanjungPuting)->toBeInstanceOf(Destination::class)
        ->and($tanjungPuting?->getTranslation('name', 'en'))->toBe('Tanjung Puting National Park')
        ->and($tanjungPuting?->getTranslation('name', 'ms'))->toBe('Taman Negara Tanjung Puting');
});
