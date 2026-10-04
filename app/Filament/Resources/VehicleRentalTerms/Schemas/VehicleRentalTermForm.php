<?php

namespace App\Filament\Resources\VehicleRentalTerms\Schemas;

use App\Enums\VehicleCategory;
use App\Enums\VehicleRentalTermType;
use App\Filament\Support\FilamentLocale;
use App\Filament\Support\Forms\Presets\StatusTogglePreset;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class VehicleRentalTermForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ketentuan Sewa')
                    ->description('Kelola bagian ketentuan yang ditampilkan pada katalog dan detail kendaraan.')
                    ->icon('lucide-clipboard-check')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('code')->label('Kode')->required()->unique(ignoreRecord: true)->maxLength(100),
                            Select::make('type')->label('Jenis')->options(VehicleRentalTermType::class)->required()->native(false),
                            Select::make('vehicle_category')->label('Khusus Kategori')->options(VehicleCategory::class)->placeholder('Semua kendaraan')->native(false),
                        ]),
                        Translate::make()
                            ->locales(FilamentLocale::locales())
                            ->schema(fn (string $locale): array => [
                                TextInput::make('title')->label('Judul')->required($locale === FilamentLocale::primary())->maxLength(255),
                                RichEditor::make('content')->label('Isi Ketentuan')->required($locale === FilamentLocale::primary())->columnSpanFull(),
                            ]),
                        Grid::make(2)->schema([
                            TextInput::make('sort_order')->label('Urutan')->numeric()->minValue(0)->default(0),
                            StatusTogglePreset::active(label: 'Tampilkan'),
                        ]),
                    ]),
            ]);
    }
}

