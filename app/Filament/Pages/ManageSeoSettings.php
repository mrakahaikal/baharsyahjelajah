<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Settings\SeoSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class ManageSeoSettings extends SettingsPage
{
    protected static string|null|BackedEnum $navigationIcon = 'lucide-search';

    protected static ?string $navigationLabel = 'SEO & Analytics';

    protected static ?string $title = 'SEO & Analytics';

    protected static ?int $navigationSort = 2;

    protected static string $settings = SeoSettings::class;

    protected ?string $heading = 'SEO & Analytics';

    protected ?string $subheading = 'Kelola pengaturan Open Graph dan kode pelacakan analitik.';

    protected static ?string $cluster = SettingsCluster::class;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Open Graph (Sosial Media)')
                ->description('Atur bagaimana website Anda tampil saat dibagikan ke media sosial.')
                ->icon('lucide-share-2')
                ->schema([
                    Translate::make()
                        ->locales(['id', 'en', 'ms'])
                        ->schema(fn (string $locale) => [
                            TextInput::make('og_title')
                                ->label('Judul Open Graph (OG Title)')
                                ->placeholder('Contoh: Agen Perjalanan Wisata Baharsyah Jelajah')
                                ->helperText('Judul halaman yang akan muncul di pratinjau saat tautan dibagikan.')
                                ->prefixIcon('lucide-type')
                                ->required($locale === 'id'),
                            Textarea::make('og_description')
                                ->label('Deskripsi Open Graph (OG Description)')
                                ->placeholder('Tuliskan deskripsi pratinjau yang menarik...')
                                ->helperText('Ringkasan singkat isi halaman untuk pratinjau sharing media sosial.')
                                ->rows(3),
                        ]),
                    FileUpload::make('og_image')
                        ->label('Gambar Pratinjau (OG Image)')
                        ->image()
                        ->directory('seo')
                        ->imageEditor()
                        ->disk('public')
                        ->visibility('public')
                        ->helperText('Gambar thumbnail yang tampil di media sosial. Ukuran rekomendasi: 1200 x 630 piksel (Rasio 1.91:1), format JPG/PNG/WebP, maks 5MB.'),
                ]),

            Section::make('Pelacakan & Analitik')
                ->description('Integrasikan website dengan layanan pihak ketiga untuk statistik.')
                ->icon('lucide-line-chart')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('google_analytics_id')
                            ->label('Google Analytics 4 (GA4) Measurement ID')
                            ->placeholder('Contoh: G-XXXXXXXXXX')
                            ->helperText('ID pengukuran Google Analytics Anda untuk melacak statistik pengunjung website.')
                            ->prefixIcon('lucide-activity'),
                        TextInput::make('meta_pixel_id')
                            ->label('Meta Pixel ID (Facebook Pixel)')
                            ->placeholder('Contoh: 123456789012345')
                            ->helperText('ID Pixel Meta untuk optimasi iklan facebook dan analitik konversi.')
                            ->prefixIcon('lucide-code-2'),
                    ]),
                ]),
        ])
            ->columns(1);
    }
}
