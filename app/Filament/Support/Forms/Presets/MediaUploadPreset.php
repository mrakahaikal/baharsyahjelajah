<?php

namespace App\Filament\Support\Forms\Presets;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class MediaUploadPreset
{
    /**
     * @var array<int, string>
     */
    public const DEFAULT_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public const DEFAULT_MAX_SIZE_KB = 5120;

    public static function spatieImage(
        string $name,
        string $collection,
        ?string $label = null,
        int $maxSizeKb = self::DEFAULT_MAX_SIZE_KB,
        ?string $helperText = null
    ): SpatieMediaLibraryFileUpload {
        $component = SpatieMediaLibraryFileUpload::make($name)
            ->collection($collection)
            ->image()
            ->disk('public')
            ->visibility('public')
            ->imageEditor()
            ->acceptedFileTypes(self::DEFAULT_IMAGE_TYPES)
            ->maxSize($maxSizeKb);

        if ($label !== null) {
            $component->label($label);
        }

        if ($helperText !== null) {
            $component->helperText($helperText);
        }

        return $component;
    }

    public static function spatieGallery(
        string $name,
        string $collection,
        ?string $label = null,
        int $maxSizeKb = self::DEFAULT_MAX_SIZE_KB,
        ?string $helperText = null
    ): SpatieMediaLibraryFileUpload {
        return self::spatieImage($name, $collection, $label, $maxSizeKb, $helperText)
            ->multiple()
            ->reorderable()
            ->appendFiles();
    }

    public static function publicImage(
        string $name,
        string $directory,
        ?string $label = null,
        int $maxSizeKb = self::DEFAULT_MAX_SIZE_KB,
        ?string $helperText = null
    ): FileUpload {
        $component = FileUpload::make($name)
            ->directory($directory)
            ->image()
            ->disk('public')
            ->visibility('public')
            ->imageEditor()
            ->acceptedFileTypes(self::DEFAULT_IMAGE_TYPES)
            ->maxSize($maxSizeKb);

        if ($label !== null) {
            $component->label($label);
        }

        if ($helperText !== null) {
            $component->helperText($helperText);
        }

        return $component;
    }
}
