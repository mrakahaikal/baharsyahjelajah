<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use App\Models\Tour;
use App\Models\UmrahPackage;
use App\Models\Vehicle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Reviewer')
                    ->description('Kelola identitas reviewer, foto profil, asal negara, dan nama.')
                    ->icon('lucide-user')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('reviewer_country')
                                ->label('Negara Asal')
                                ->placeholder('Contoh: Indonesia')
                                ->maxLength(100)
                                ->prefixIcon('lucide-globe')
                                ->helperText('Negara asal pelanggan yang mengulas.'),
                            TextInput::make('reviewer_flag')
                                ->label('Kode Negara')
                                ->placeholder('Contoh: ID')
                                ->maxLength(10)
                                ->prefixIcon('lucide-flag')
                                ->helperText('Kode negara dua huruf (misal: ID untuk Indonesia, MY untuk Malaysia).'),
                        ]),
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                TextInput::make('reviewer_name')
                                    ->label('Nama Reviewer')
                                    ->placeholder('Masukkan nama lengkap reviewer...')
                                    ->required($locale === 'id')
                                    ->maxLength(255)
                                    ->prefixIcon('lucide-type')
                                    ->helperText('Nama lengkap pelanggan.'),
                            ]),
                        FileUpload::make('photo')
                            ->label('Foto Profil Reviewer')
                            ->image()
                            ->directory('testimonials/photos')
                            ->disk('public')
                            ->visibility('public')
                            ->avatar()
                            ->imageEditor()
                            ->helperText('Foto avatar pelanggan (JPG, PNG, WebP maks 2MB).')
                            ->columnSpanFull(),
                    ]),
                Section::make('Ulasan & Penilaian')
                    ->description('Kelola rating, jenis produk yang diulas, isi komentar, dan status publikasi.')
                    ->icon('lucide-star')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('product_type')
                                ->label('Tipe Produk')
                                ->options([
                                    Tour::class => 'Tour Wisata',
                                    Vehicle::class => 'Sewa Kendaraan',
                                    UmrahPackage::class => 'Paket Umrah',
                                ])
                                ->placeholder('Pilih jenis produk')
                                ->prefixIcon('lucide-package')
                                ->native(false)
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn (Set $set) => $set('product_id', null)),
                            Select::make('product_id')
                                ->label('Produk')
                                ->placeholder(fn (Get $get): string => filled($get('product_type'))
                                    ? 'Cari dan pilih nama produk...'
                                    : 'Pilih tipe produk terlebih dahulu')
                                ->prefixIcon('lucide-box')
                                ->native(false)
                                ->searchable()
                                ->preload()
                                ->disabled(fn (Get $get): bool => blank($get('product_type')))
                                ->required()
                                ->options(function (Get $get): array {
                                    $productType = $get('product_type');

                                    return match ($productType) {
                                        Tour::class => Tour::query()
                                            ->latest()
                                            ->get()
                                            ->mapWithKeys(fn (Tour $tour) => [$tour->id => $tour->name])
                                            ->all(),
                                        Vehicle::class => Vehicle::query()
                                            ->latest()
                                            ->get()
                                            ->mapWithKeys(fn (Vehicle $vehicle) => [$vehicle->id => $vehicle->name])
                                            ->all(),
                                        UmrahPackage::class => UmrahPackage::query()
                                            ->latest()
                                            ->get()
                                            ->mapWithKeys(fn (UmrahPackage $package) => [$package->id => $package->name])
                                            ->all(),
                                        default => [],
                                    };
                                })
                                ->getOptionLabelUsing(function (Get $get, mixed $value): ?string {
                                    $productType = $get('product_type');
                                    if (! $productType || ! class_exists($productType) || ! $value) {
                                        return null;
                                    }

                                    $query = method_exists($productType, 'withTrashed')
                                        ? $productType::withTrashed()
                                        : $productType::query();

                                    return $query->find($value)?->name;
                                })
                                ->helperText('Pilih nama produk dari daftar yang tersedia.'),
                            Select::make('rating')
                                ->label('Rating Penilaian')
                                ->options([
                                    1 => '1 Bintang (Sangat Buruk)',
                                    2 => '2 Bintang (Buruk)',
                                    3 => '3 Bintang (Cukup)',
                                    4 => '4 Bintang (Baik)',
                                    5 => '5 Bintang (Sangat Baik)',
                                ])
                                ->placeholder('Pilih rating')
                                ->prefixIcon('lucide-star')
                                ->native(false)
                                ->required(),
                        ]),
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                TextInput::make('content')
                                    ->label('Isi Ulasan')
                                    ->placeholder('Tuliskan cerita pengalaman dan ulasan pelanggan di sini...')
                                    ->required($locale === 'id')
                                    ->maxLength(1000)
                                    ->prefixIcon('lucide-message-square')
                                    ->helperText('Konten teks ulasan pelanggan.')
                                    ->columnSpanFull(),
                            ]),
                        Grid::make(2)->schema([
                            Toggle::make('is_featured')
                                ->label('Tampilkan di Halaman Utama (Unggulan)')
                                ->default(false),
                            Toggle::make('is_active')
                                ->label('Status Aktif')
                                ->default(true),
                        ]),
                    ]),
            ]);
    }
}
