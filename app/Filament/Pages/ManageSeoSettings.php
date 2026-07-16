<?php

namespace App\Filament\Pages;

use App\Enums\StaticSeoPage;
use App\Filament\Clusters\Settings\SettingsCluster;
use App\Settings\SeoSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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

    protected ?string $subheading = 'Kelola metadata halaman, Open Graph, dan kode pelacakan analitik.';

    protected static ?string $cluster = SettingsCluster::class;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Default SEO & Open Graph')
                ->description('Fallback metadata yang digunakan ketika suatu halaman tidak memiliki pengaturan khusus.')
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
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(5120)
                        ->directory('seo')
                        ->imageEditor()
                        ->disk('public')
                        ->visibility('public')
                        ->helperText('Gambar thumbnail yang tampil di media sosial. Ukuran rekomendasi: 1200 x 630 piksel (Rasio 1.91:1), format JPG/PNG/WebP, maks 5MB.'),
                ]),

            Section::make('SEO per Halaman')
                ->description('Override metadata halaman statis dan katalog. Field yang dikosongkan akan menggunakan teks bawaan halaman atau pengaturan global.')
                ->icon('lucide-files')
                ->schema([
                    Tabs::make('Halaman')
                        ->tabs(collect(StaticSeoPage::cases())
                            ->map(fn (StaticSeoPage $page): Tab => $this->pageTab($page))
                            ->all()),
                ])
                ->columnSpanFull(),

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

    private function pageTab(StaticSeoPage $page): Tab
    {
        $statePath = "pages.{$page->value}";

        return Tab::make($page->label())
            ->schema([
                Translate::make()
                    ->locales(['id', 'en', 'ms'])
                    ->schema(fn (string $locale): array => [
                        TextInput::make("{$statePath}.title")
                            ->label('Meta Title')
                            ->maxLength(70)
                            ->helperText('Disarankan 50–60 karakter. Kosongkan untuk memakai judul bawaan halaman.')
                            ->prefixIcon('lucide-heading'),
                        Textarea::make("{$statePath}.description")
                            ->label('Meta Description')
                            ->maxLength(160)
                            ->rows(3)
                            ->helperText('Disarankan maksimal 160 karakter. Kosongkan untuk memakai deskripsi bawaan halaman.'),
                    ]),
                Section::make('Override Open Graph')
                    ->description('Opsional. Jika kosong, judul dan deskripsi Open Graph mengikuti metadata di atas.')
                    ->icon('lucide-share-2')
                    ->collapsed()
                    ->collapsible()
                    ->schema([
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale): array => [
                                TextInput::make("{$statePath}.og_title")
                                    ->label('OG Title')
                                    ->maxLength(95)
                                    ->prefixIcon('lucide-type'),
                                Textarea::make("{$statePath}.og_description")
                                    ->label('OG Description')
                                    ->maxLength(200)
                                    ->rows(3),
                            ]),
                        FileUpload::make("{$statePath}.og_image")
                            ->label('OG Image Halaman')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120)
                            ->directory('seo/pages')
                            ->imageEditor()
                            ->disk('public')
                            ->visibility('public')
                            ->helperText('Gunakan gambar 1200 × 630 piksel. Jika kosong, gambar OG global akan digunakan.'),
                    ]),
            ]);
    }
}
