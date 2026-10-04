<?php

namespace App\Filament\Support\Forms\Components;

use App\Filament\Support\FilamentLocale;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

class TranslatableNameSlug
{
    /**
     * Reusable schema for models where both name and slug are translatable JSON attributes.
     */
    public static function make(
        string $locale,
        string $nameLabel = 'Nama',
        string $slugLabel = 'Slug URL',
        string $namePlaceholder = 'Masukkan nama...',
        string $slugPlaceholder = 'slug-url',
        string $nameField = 'name',
        string $slugField = 'slug',
        ?string $nameHelperText = null,
        ?string $slugHelperText = null,
        int $maxLength = 255
    ): Grid {
        $isPrimary = $locale === FilamentLocale::primary();

        return Grid::make(2)
            ->schema([
                TextInput::make($nameField)
                    ->label($nameLabel)
                    ->placeholder($namePlaceholder)
                    ->required($isPrimary)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Set $set) => $set(
                        "{$slugField}.{$locale}",
                        Str::slug($state ?? '')
                    ))
                    ->maxLength($maxLength)
                    ->prefixIcon('lucide-type')
                    ->helperText($nameHelperText),
                TextInput::make($slugField)
                    ->label($slugLabel)
                    ->placeholder($slugPlaceholder)
                    ->required($isPrimary)
                    ->maxLength($maxLength)
                    ->prefixIcon('lucide-link-2')
                    ->helperText($slugHelperText),
            ]);
    }

    /**
     * Reusable name input for models where the name is translatable,
     * but the slug is a single non-translatable root field.
     */
    public static function forNonTranslatableSlug(
        string $locale,
        string $nameLabel = 'Nama',
        string $namePlaceholder = 'Masukkan nama...',
        string $nameField = 'name',
        string $slugField = 'slug',
        ?string $nameHelperText = null,
        int $maxLength = 255
    ): TextInput {
        $isPrimary = $locale === FilamentLocale::primary();

        return TextInput::make($nameField)
            ->label($nameLabel)
            ->placeholder($namePlaceholder)
            ->required($isPrimary)
            ->live(onBlur: true)
            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) use ($isPrimary, $slugField): void {
                if ($isPrimary && blank($get($slugField))) {
                    $set($slugField, Str::slug($state ?? ''));
                }
            })
            ->maxLength($maxLength)
            ->prefixIcon('lucide-type')
            ->helperText($nameHelperText);
    }
}
