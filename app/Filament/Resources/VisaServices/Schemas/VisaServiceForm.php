<?php

namespace App\Filament\Resources\VisaServices\Schemas;

use App\Enums\VisaEntryType;
use App\Filament\Support\CurrencyOptions;
use App\Filament\Support\FilamentLocale;
use App\Filament\Support\Forms\Components\TranslatableNameSlug;
use App\Filament\Support\Forms\Presets\MediaUploadPreset;
use App\Filament\Support\Forms\Presets\StatusTogglePreset;
use App\Models\Country;
use App\Models\VisaService;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class VisaServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Informasi Layanan')
                        ->description('Negara tujuan, jenis Visa, dan informasi publik.')
                        ->icon('lucide-info')
                        ->schema([
                            Section::make('Identitas Layanan Visa')
                                ->description('Setiap layanan mewakili satu jenis Visa untuk satu negara tujuan.')
                                ->schema([
                                    Select::make('country_id')
                                        ->label('Negara Tujuan')
                                        ->relationship(
                                            name: 'country',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn (Builder $query, ?VisaService $record): Builder => $query
                                                ->where(fn (Builder $countries): Builder => $countries
                                                    ->where(fn (Builder $availableCountries): Builder => $availableCountries
                                                        ->active()
                                                        ->whereNull((new Country)->getQualifiedDeletedAtColumn()))
                                                    ->when($record?->country_id, fn (Builder $countries, int $countryId): Builder => $countries->orWhere('id', $countryId)))
                                                ->orderBy('sort_order'),
                                        )
                                        ->getOptionLabelFromRecordUsing(fn (Country $record): string => $record->name.' ('.$record->iso_alpha_2.')')
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->required()
                                        ->prefixIcon('lucide-globe-2'),
                                    TextInput::make('slug')
                                        ->label('Slug URL')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->dehydrateStateUsing(fn (?string $state): string => Str::slug($state ?? ''))
                                        ->maxLength(255)
                                        ->prefixIcon('lucide-link-2'),
                                    Translate::make()
                                        ->locales(FilamentLocale::locales())
                                        ->schema(fn (string $locale): array => [
                                            Grid::make(2)->schema([
                                                TranslatableNameSlug::forNonTranslatableSlug(
                                                    locale: $locale,
                                                    nameLabel: 'Nama Layanan',
                                                    namePlaceholder: 'Contoh: Visa Turis Mesir',
                                                ),
                                                TextInput::make('visa_type')
                                                    ->label('Jenis Visa')
                                                    ->required($locale === FilamentLocale::primary())
                                                    ->maxLength(255)
                                                    ->placeholder('Contoh: Visa Kunjungan'),
                                            ]),
                                            Textarea::make('summary')
                                                ->label('Ringkasan')
                                                ->rows(3)
                                                ->maxLength(500)
                                                ->columnSpanFull(),
                                            RichEditor::make('description')
                                                ->label('Deskripsi Lengkap')
                                                ->columnSpanFull(),
                                        ])
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),
                    Step::make('Proses & Harga')
                        ->description('Estimasi proses, masa berlaku, dan harga layanan.')
                        ->icon('lucide-calendar-clock')
                        ->schema([
                            Section::make('Ketentuan & Estimasi Proses')
                                ->schema([
                                    Grid::make(3)->schema([
                                        Select::make('entry_type')
                                            ->label('Tipe Masuk')
                                            ->options(VisaEntryType::class)
                                            ->placeholder('Belum ditentukan')
                                            ->native(false),
                                        TextInput::make('processing_days_min')
                                            ->label('Proses Minimum')
                                            ->numeric()
                                            ->minValue(1)
                                            ->suffix('hari'),
                                        TextInput::make('processing_days_max')
                                            ->label('Proses Maksimum')
                                            ->numeric()
                                            ->minValue(1)
                                            ->gte('processing_days_min')
                                            ->suffix('hari'),
                                        TextInput::make('validity_days')
                                            ->label('Masa Berlaku')
                                            ->numeric()
                                            ->minValue(1)
                                            ->suffix('hari'),
                                        TextInput::make('maximum_stay_days')
                                            ->label('Maksimum Tinggal')
                                            ->numeric()
                                            ->minValue(1)
                                            ->suffix('hari'),
                                    ]),
                                ]),
                            Section::make('Tarif & Mata Uang')
                                ->description('Tentukan mata uang dasar dan nominal tarif layanan visa.')
                                ->schema([
                                    Grid::make(3)->schema([
                                        Select::make('currency')
                                            ->label('Mata Uang')
                                            ->options(CurrencyOptions::detailedOptions())
                                            ->default('IDR')
                                            ->required()
                                            ->live()
                                            ->placeholder('Pilih mata uang')
                                            ->helperText('Mata uang dasar untuk tarif layanan.')
                                            ->prefixIcon('lucide-coins')
                                            ->native(false),
                                        TextInput::make('price')
                                            ->label('Harga Layanan')
                                            ->numeric()
                                            ->minValue(0)
                                            ->prefix(fn (Get $get): string => config("currencies.supported.{$get('currency')}.symbol", 'Rp'))
                                            ->helperText('Kosongkan jika harga perlu dikonsultasikan.')
                                            ->columnSpan(2),
                                    ]),
                                ]),
                        ]),
                    Step::make('Media')
                        ->description('Cover dan galeri layanan Visa.')
                        ->icon('lucide-images')
                        ->schema([
                            Section::make('Media Visual')
                                ->schema([
                                    MediaUploadPreset::spatieImage(
                                        name: 'cover',
                                        collection: VisaService::MEDIA_COLLECTION_COVER,
                                        label: 'Foto Utama',
                                    ),
                                    MediaUploadPreset::spatieGallery(
                                        name: 'gallery',
                                        collection: VisaService::MEDIA_COLLECTION_GALLERY,
                                        label: 'Galeri Foto',
                                    ),
                                ]),
                        ]),
                    Step::make('Publikasi')
                        ->description('Visibilitas dan urutan layanan.')
                        ->icon('lucide-settings')
                        ->schema([
                            Section::make('Pengaturan Publikasi')
                                ->schema([
                                    Grid::make(3)->schema([
                                        TextInput::make('sort_order')
                                            ->label('Urutan Tampilan')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->required(),
                                        StatusTogglePreset::active(label: 'Aktif dan Dapat Diakses'),
                                        StatusTogglePreset::featured(label: 'Layanan Unggulan'),
                                    ]),
                                ]),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    /** @return array<string, string> */
    public static function currencyOptions(): array
    {
        return CurrencyOptions::detailedOptions();
    }
}

