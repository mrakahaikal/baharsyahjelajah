<?php

use App\Filament\Support\FilamentLocale;
use App\Filament\Support\Forms\Components\TranslatableNameSlug;
use App\Filament\Support\Forms\Presets\MediaUploadPreset;
use App\Filament\Support\Forms\Presets\StatusTogglePreset;
use App\Filament\Support\Tables\Presets\StatusColumnPreset;
use App\Filament\Support\Tables\Presets\TimestampColumnPreset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;

it('provides expected panel locales', function () {
    expect(FilamentLocale::supported())->toBe(['id', 'en', 'ms'])
        ->and(FilamentLocale::primary())->toBe('id');
});

it('creates status toggle presets correctly', function () {
    $activeToggle = StatusTogglePreset::active('Status Aktif');
    expect($activeToggle)->toBeInstanceOf(Toggle::class)
        ->and($activeToggle->getDefaultState())->toBeTrue();

    $featuredToggle = StatusTogglePreset::featured('Unggulan');
    expect($featuredToggle)->toBeInstanceOf(Toggle::class)
        ->and($featuredToggle->getDefaultState())->toBeFalse();

    $group = StatusTogglePreset::group();
    expect($group)->toBeInstanceOf(Grid::class)
        ->and(count($group->getDefaultChildComponents()))->toBe(2);
});

it('creates status column and filter presets correctly', function () {
    $activeColumn = StatusColumnPreset::active('Aktif');
    expect($activeColumn)->toBeInstanceOf(IconColumn::class);

    $featuredColumn = StatusColumnPreset::featured('Unggulan');
    expect($featuredColumn)->toBeInstanceOf(IconColumn::class);

    $activeFilter = StatusColumnPreset::filterActive('Status Aktif');
    expect($activeFilter)->toBeInstanceOf(TernaryFilter::class);

    $featuredFilter = StatusColumnPreset::filterFeatured('Destinasi Unggulan');
    expect($featuredFilter)->toBeInstanceOf(TernaryFilter::class);
});

it('creates timestamp column presets correctly', function () {
    $createdAt = TimestampColumnPreset::createdAt();
    expect($createdAt)->toBeInstanceOf(TextColumn::class)
        ->and($createdAt->getName())->toBe('created_at')
        ->and($createdAt->isSortable())->toBeTrue();

    $updatedAt = TimestampColumnPreset::updatedAt();
    expect($updatedAt)->toBeInstanceOf(TextColumn::class)
        ->and($updatedAt->getName())->toBe('updated_at')
        ->and($updatedAt->isSortable())->toBeTrue();
});

it('creates media upload presets correctly', function () {
    $spatieImage = MediaUploadPreset::spatieImage('image', 'banner_images');
    expect($spatieImage)->toBeInstanceOf(SpatieMediaLibraryFileUpload::class)
        ->and($spatieImage->getDiskName())->toBe('public')
        ->and($spatieImage->getVisibility())->toBe('public')
        ->and($spatieImage->hasImageEditor())->toBeTrue();

    $spatieGallery = MediaUploadPreset::spatieGallery('gallery', 'tour_gallery');
    expect($spatieGallery)->toBeInstanceOf(SpatieMediaLibraryFileUpload::class)
        ->and($spatieGallery->isMultiple())->toBeTrue();

    $publicImage = MediaUploadPreset::publicImage('photo', 'testimonials/photos');
    expect($publicImage)->toBeInstanceOf(FileUpload::class)
        ->and($publicImage->getDiskName())->toBe('public')
        ->and($publicImage->getVisibility())->toBe('public')
        ->and($publicImage->hasImageEditor())->toBeTrue();
});

it('creates translatable name and slug grid components', function () {
    $primaryGrid = TranslatableNameSlug::make('id', 'Judul Artikel', 'Slug URL');
    expect($primaryGrid)->toBeInstanceOf(Grid::class);

    $components = $primaryGrid->getDefaultChildComponents();
    expect(count($components))->toBe(2);

    /** @var TextInput $nameInput */
    $nameInput = $components[0];
    /** @var TextInput $slugInput */
    $slugInput = $components[1];

    expect($nameInput->isRequired())->toBeTrue()
        ->and($slugInput->isRequired())->toBeTrue()
        ->and($nameInput->isLive())->toBeTrue();

    $secondaryGrid = TranslatableNameSlug::make('en', 'Judul Artikel', 'Slug URL');
    $secondaryComponents = $secondaryGrid->getDefaultChildComponents();
    /** @var TextInput $secondaryNameInput */
    $secondaryNameInput = $secondaryComponents[0];
    /** @var TextInput $secondarySlugInput */
    $secondarySlugInput = $secondaryComponents[1];

    expect($secondaryNameInput->isRequired())->toBeFalse()
        ->and($secondarySlugInput->isRequired())->toBeFalse();

    $nonTranslatableInput = TranslatableNameSlug::forNonTranslatableSlug('id', 'Nama Destinasi');
    expect($nonTranslatableInput)->toBeInstanceOf(TextInput::class)
        ->and($nonTranslatableInput->isRequired())->toBeTrue();
});
