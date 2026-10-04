<?php

namespace App\Filament\Resources\Tours\Schemas;

use App\Filament\Resources\Tours\Schemas\Partials\TourPackagesTab;
use App\Filament\Support\CurrencyOptions;
use App\Filament\Support\FilamentLocale;
use App\Filament\Support\Forms\Components\TranslatableNameSlug;
use App\Filament\Support\Forms\Presets\StatusTogglePreset;
use App\Models\Country;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class TourForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('TourFormTabs')
                    ->tabs([
                        Tab::make('Informasi Utama & Konten')
                            ->icon('lucide-info')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'lg' => 3,
                                ])->schema([
                                    Grid::make(1)
                                        ->schema([
                                            Section::make('Informasi Dasar & Klasifikasi')
                                                ->description('Atur kategori utama, jenis destinasi, mata uang dasar, dan detail penulisan nama serta deskripsi tur.')
                                                ->icon('lucide-file-text')
                                                ->schema([
                                                    Grid::make(3)
                                                        ->schema([
                                                            Select::make('tour_category_id')
                                                                ->label('Kategori Tur')
                                                                ->relationship('category', 'name')
                                                                ->searchable()
                                                                ->preload()
                                                                ->required()
                                                                ->placeholder('Pilih kategori tur')
                                                                ->helperText('Pilih kategori yang paling sesuai dengan destinasi tur ini.')
                                                                ->prefixIcon('lucide-tag')
                                                                ->native(false),
                                                            Select::make('tour_type')
                                                                ->label('Tipe Destinasi')
                                                                ->options([
                                                                    'domestic' => 'Domestik',
                                                                    'international' => 'Internasional',
                                                                ])
                                                                ->default('domestic')
                                                                ->required()
                                                                ->placeholder('Pilih tipe destinasi')
                                                                ->helperText('Destinasi domestik atau internasional.')
                                                                ->prefixIcon('lucide-globe')
                                                                ->native(false),
                                                            Select::make('countries')
                                                                ->label('Negara Destinasi')
                                                                ->relationship('countries', 'name')
                                                                ->getOptionLabelFromRecordUsing(fn (Country $record): string => $record->name)
                                                                ->multiple()
                                                                ->searchable()
                                                                ->preload()
                                                                ->native(false)
                                                                ->helperText('Pilih satu atau lebih negara tujuan tur ini.')
                                                                ->prefixIcon('lucide-flag'),
                                                            Select::make('currency')
                                                                ->label('Mata Uang Utama')
                                                                ->options(CurrencyOptions::selectOptions())
                                                                ->default('IDR')
                                                                ->required()
                                                                ->placeholder('Pilih mata uang')
                                                                ->helperText('Mata uang default tur.')
                                                                ->prefixIcon('lucide-coins')
                                                                ->native(false),
                                                        ]),
                                                    Translate::make()
                                                        ->locales(FilamentLocale::locales())
                                                        ->schema(fn (string $locale): array => [
                                                            TranslatableNameSlug::make(
                                                                locale: $locale,
                                                                nameLabel: 'Nama Tur',
                                                                slugLabel: 'Slug URL',
                                                                namePlaceholder: 'Contoh: Pesona Keindahan Raja Ampat',
                                                                slugPlaceholder: 'pesona-keindahan-raja-ampat',
                                                                nameHelperText: 'Nama tur unik yang akan ditampilkan pada website.',
                                                                slugHelperText: 'Tautan URL halaman tur. Terisi otomatis dari nama tur.',
                                                            ),
                                                            Textarea::make('short_description')
                                                                ->label('Deskripsi Singkat')
                                                                ->rows(3)
                                                                ->required($locale === FilamentLocale::primary())
                                                                ->columnSpanFull()
                                                                ->placeholder('Masukkan ringkasan singkat daya tarik tur ini...')
                                                                ->helperText('Ringkasan 2-3 kalimat yang tampil di kartu katalog tur.'),
                                                            RichEditor::make('description')
                                                                ->label('Deskripsi Lengkap')
                                                                ->required($locale === FilamentLocale::primary())
                                                                ->columnSpanFull()
                                                                ->placeholder('Tuliskan detail lengkap perjalanan, daya tarik utama, dan informasi umum tur...')
                                                                ->helperText('Deskripsi terperinci mengenai tur ini.'),
                                                        ]),
                                                ]),
                                        ])
                                        ->columnSpan([
                                            'default' => 1,
                                            'lg' => 2,
                                        ]),

                                    Grid::make(1)
                                        ->schema([
                                            Section::make('Status & Publikasi')
                                                ->description('Atur status visibilitas dan penayangan paket tur pada website.')
                                                ->icon('lucide-settings')
                                                ->schema([
                                                    StatusTogglePreset::active(
                                                        label: 'Aktif (Dapat Dipesan & Publik)',
                                                        helperText: 'Tur aktif dan dapat diakses oleh calon wisatawan.'
                                                    ),
                                                    StatusTogglePreset::featured(
                                                        label: 'Tampilkan Sebagai Tur Unggulan',
                                                        helperText: 'Menampilkan tur ini pada section rekomendasi utama di homepage.'
                                                    ),
                                                ]),
                                        ])
                                        ->columnSpan([
                                            'default' => 1,
                                            'lg' => 1,
                                        ]),
                                ]),
                            ]),

                        TourPackagesTab::make(),
                    ])
                    ->persistTabInQueryString('tab')
                    ->columnSpanFull(),
            ]);
    }
}

