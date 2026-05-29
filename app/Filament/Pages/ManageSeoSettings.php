<?php

namespace App\Filament\Pages;

use App\Settings\SeoSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class ManageSeoSettings extends SettingsPage
{
    protected static string|null|BackedEnum $navigationIcon = Heroicon::OutlinedChartBar;
    protected static string|null|\UnitEnum $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'SEO & Analytics';
    protected static ?string $title = 'SEO & Analytics';
    protected static ?int    $navigationSort  = 2;
    protected static string  $settings = SeoSettings::class;

    protected ?string $heading = 'SEO & Analytics';

    protected ?string $subheading = 'Kelola pengaturan Open Graph dan kode pelacakan analitik.';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Open Graph (Sosial Media)')
                ->description('Atur bagaimana website Anda tampil saat dibagikan ke media sosial.')
                ->icon(Heroicon::OutlinedShare)
                ->schema([
                    Translate::make()
                        ->locales(['id', 'en', 'ms'])
                        ->schema(fn (string $locale) => [
                            TextInput::make('og_title')
                                ->label('Judul OG')
                                ->placeholder('Judul yang muncul saat dibagikan...')
                                ->required($locale === 'id'),
                            Textarea::make('og_description')
                                ->label('Deskripsi OG')
                                ->placeholder('Deskripsi yang muncul saat dibagikan...')
                                ->rows(3),
                        ]),
                    FileUpload::make('og_image')
                        ->label('Gambar OG (Thumbnail)')
                        ->image()
                        ->directory('seo')
                        ->imageEditor()
                        ->helperText('Rekomendasi ukuran: 1200 × 630 pixels.'),
                ]),

            Section::make('Pelacakan & Analitik')
                ->description('Integrasikan website dengan layanan pihak ketiga untuk statistik.')
                ->icon(Heroicon::OutlinedPresentationChartLine)
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('google_analytics_id')
                            ->label('Google Analytics ID')
                            ->placeholder('G-XXXXXXXXXX')
                            ->helperText('Masukkan ID properti Google Analytics 4.'),
                        TextInput::make('meta_pixel_id')
                            ->label('Meta Pixel ID')
                            ->placeholder('XXXXXXXXXXXXXXXXXX')
                            ->helperText('Masukkan ID Pixel dari Facebook/Meta Events Manager.'),
                    ]),
                ]),
        ]);
    }
}
