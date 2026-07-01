<?php

namespace App\Filament\Pages;

use App\Settings\FooterSettings;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class ManageFooterSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static string|null|\UnitEnum $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Footer';

    protected static ?string $title = 'Footer';

    protected static ?int $navigationSort = 4;

    protected static string $settings = FooterSettings::class;

    protected ?string $heading = 'Footer';

    protected ?string $subheading = 'Kelola CTA, deskripsi brand, dan tautan yang tampil di footer website.';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Brand & CTA')
                    ->description('Konten utama footer yang terlihat sebelum daftar tautan.')
                    ->icon(Heroicon::OutlinedMegaphone)
                    ->schema([
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale): array => [
                                Textarea::make('brand_description')
                                    ->label('Deskripsi Brand')
                                    ->rows(3)
                                    ->required($locale === 'id'),
                                TextInput::make('cta_title')
                                    ->label('Judul CTA')
                                    ->required($locale === 'id'),
                                Textarea::make('cta_subtitle')
                                    ->label('Subjudul CTA')
                                    ->rows(2)
                                    ->required($locale === 'id'),
                                TextInput::make('cta_button_label')
                                    ->label('Label Tombol CTA')
                                    ->required($locale === 'id'),
                            ]),
                        TextInput::make('cta_button_route')
                            ->label('Route Tombol CTA')
                            ->helperText('Gunakan nama route Laravel, misalnya contact.index atau tour.index.')
                            ->required(),
                    ]),

                Section::make('Kolom Tautan')
                    ->description('Kelola grup tautan yang tampil di bagian tengah footer.')
                    ->icon(Heroicon::OutlinedListBullet)
                    ->schema([
                        Repeater::make('link_groups')
                            ->label('Grup Tautan')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('title.id')
                                        ->label('Judul ID')
                                        ->required(),
                                    TextInput::make('title.en')
                                        ->label('Judul EN'),
                                    TextInput::make('title.ms')
                                        ->label('Judul MS'),
                                ]),
                                Repeater::make('links')
                                    ->label('Tautan')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            TextInput::make('label.id')
                                                ->label('Label ID')
                                                ->required(),
                                            TextInput::make('label.en')
                                                ->label('Label EN'),
                                            TextInput::make('label.ms')
                                                ->label('Label MS'),
                                        ]),
                                        Grid::make(2)->schema([
                                            TextInput::make('route')
                                                ->label('Route')
                                                ->helperText('Kosongkan jika memakai URL manual.'),
                                            TextInput::make('url')
                                                ->label('URL Manual')
                                                ->url()
                                                ->helperText('Gunakan untuk link eksternal.'),
                                        ]),
                                    ])
                                    ->columns(1)
                                    ->reorderable()
                                    ->collapsible(),
                            ])
                            ->columns(1)
                            ->reorderable()
                            ->collapsible(),
                    ]),

                Section::make('Newsletter & Legal')
                    ->description('Konten pendukung dan tautan kecil di bar bawah footer.')
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->schema([
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale): array => [
                                TextInput::make('subscribe_title')
                                    ->label('Judul Newsletter')
                                    ->required($locale === 'id'),
                                Textarea::make('subscribe_subtitle')
                                    ->label('Subjudul Newsletter')
                                    ->rows(2)
                                    ->required($locale === 'id'),
                                TextInput::make('copyright_text')
                                    ->label('Copyright')
                                    ->required($locale === 'id'),
                            ]),
                        Repeater::make('legal_links')
                            ->label('Tautan Legal')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('label.id')
                                        ->label('Label ID')
                                        ->required(),
                                    TextInput::make('label.en')
                                        ->label('Label EN'),
                                    TextInput::make('label.ms')
                                        ->label('Label MS'),
                                ]),
                                Grid::make(3)->schema([
                                    TextInput::make('route')
                                        ->label('Route'),
                                    TextInput::make('fragment')
                                        ->label('Fragment')
                                        ->placeholder('faq'),
                                    TextInput::make('url')
                                        ->label('URL Manual')
                                        ->url(),
                                ]),
                            ])
                            ->columns(1)
                            ->reorderable()
                            ->collapsible(),
                    ]),
            ]);
    }
}
