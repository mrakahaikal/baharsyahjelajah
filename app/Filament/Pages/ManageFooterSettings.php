<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Settings\FooterSettings;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class ManageFooterSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = 'lucide-layout';

    protected static ?string $navigationLabel = 'Footer';

    protected static ?string $title = 'Footer';

    protected static ?int $navigationSort = 4;

    protected static string $settings = FooterSettings::class;

    protected ?string $heading = 'Footer';

    protected ?string $subheading = 'Kelola CTA, deskripsi brand, dan tautan yang tampil di footer website.';

    protected static ?string $cluster = SettingsCluster::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Brand & CTA')
                    ->description('Konten utama footer yang terlihat sebelum daftar tautan.')
                    ->icon('lucide-megaphone')
                    ->schema([
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale): array => [
                                Textarea::make('brand_description')
                                    ->label('Deskripsi Brand')
                                    ->rows(3)
                                    ->placeholder('Tuliskan deskripsi singkat mengenai brand/perusahaan...')
                                    ->helperText('Deskripsi singkat tentang perusahaan yang tampil di footer.')
                                    ->required($locale === 'id'),
                                TextInput::make('cta_title')
                                    ->label('Judul Panggilan Aksi (CTA Title)')
                                    ->placeholder('Contoh: Siap Menjelajahi Dunia Bersama Kami?')
                                    ->helperText('Judul ajakan bertindak utama di atas footer.')
                                    ->prefixIcon('lucide-type')
                                    ->required($locale === 'id'),
                                Textarea::make('cta_subtitle')
                                    ->label('Subjudul Panggilan Aksi (CTA Subtitle)')
                                    ->rows(2)
                                    ->placeholder('Tuliskan subjudul ajakan bertindak...')
                                    ->helperText('Kalimat penjelas di bawah judul ajakan bertindak.')
                                    ->required($locale === 'id'),
                                TextInput::make('cta_button_label')
                                    ->label('Label Tombol CTA')
                                    ->placeholder('Contoh: Hubungi Kami Sekarang')
                                    ->helperText('Teks yang tampil pada tombol tindakan.')
                                    ->prefixIcon('lucide-square-play')
                                    ->required($locale === 'id'),
                            ]),
                        TextInput::make('cta_button_route')
                            ->label('Route Tombol CTA')
                            ->placeholder('Contoh: contact.index')
                            ->helperText('Gunakan nama route Laravel, misalnya contact.index atau tour.index.')
                            ->prefixIcon('lucide-link-2')
                            ->required(),
                    ]),

                Section::make('Kolom Tautan')
                    ->description('Susun klasifikasi navigasi footer. Destinasi dapat diisi otomatis dari tour aktif.')
                    ->icon('lucide-list')
                    ->schema([
                        Repeater::make('link_groups')
                            ->label('Grup Tautan Navigasi')
                            ->addActionLabel('Tambah Grup Tautan')
                            ->cloneable()
                            ->itemLabel(fn (array $state): ?string => $state['title']['id'] ?? $state['title']['en'] ?? 'Grup Baru')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('title.id')
                                        ->label('Judul Grup (ID)')
                                        ->placeholder('Contoh: Perusahaan')
                                        ->required(),
                                    TextInput::make('title.en')
                                        ->label('Judul Grup (EN)')
                                        ->placeholder('Contoh: Company'),
                                    TextInput::make('title.ms')
                                        ->label('Judul Grup (MS)')
                                        ->placeholder('Contoh: Syarikat'),
                                ]),
                                Select::make('source')
                                    ->label('Sumber Tautan')
                                    ->options([
                                        'manual' => 'Manual',
                                        'destinations' => 'Destinasi Aktif',
                                    ])
                                    ->default('manual')
                                    ->required()
                                    ->helperText('Pilih apakah tautan diinput manual atau otomatis memuat Destinasi Aktif.')
                                    ->prefixIcon('lucide-help-circle')
                                    ->native(false)
                                    ->live(),
                                Repeater::make('links')
                                    ->label('Tautan')
                                    ->addActionLabel('Tambah Tautan')
                                    ->cloneable()
                                    ->itemLabel(fn (array $state): ?string => $state['label']['id'] ?? $state['label']['en'] ?? 'Tautan Baru')
                                    ->visible(fn (Get $get): bool => $get('source') !== 'destinations')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            TextInput::make('label.id')
                                                ->label('Label Tautan (ID)')
                                                ->placeholder('Contoh: Tentang Kami')
                                                ->required(),
                                            TextInput::make('label.en')
                                                ->label('Label Tautan (EN)')
                                                ->placeholder('Contoh: About Us'),
                                            TextInput::make('label.ms')
                                                ->label('Label Tautan (MS)')
                                                ->placeholder('Contoh: Tentang Kami'),
                                        ]),
                                        Grid::make(2)->schema([
                                            TextInput::make('route')
                                                ->label('Nama Route')
                                                ->placeholder('Contoh: about')
                                                ->helperText('Nama route Laravel internal. Kosongkan jika menggunakan URL luar.')
                                                ->prefixIcon('lucide-link-2'),
                                            TextInput::make('url')
                                                ->label('URL Manual')
                                                ->url()
                                                ->placeholder('Contoh: https://external-link.com')
                                                ->helperText('Gunakan untuk link eksternal lengkap (dengan http/https).')
                                                ->prefixIcon('lucide-external-link'),
                                        ]),
                                    ])
                                    ->columns(1)
                                    ->reorderable()
                                    ->collapsible(),
                            ])
                            ->columns(1)
                            ->reorderable()
                            ->collapsible(),
                        TextInput::make('destination_limit')
                            ->label('Jumlah Maksimum Destinasi Tampil')
                            ->placeholder('Contoh: 6')
                            ->helperText('Batas jumlah destinasi yang ditampilkan jika sumber dipilih "Destinasi Aktif" (maksimal 12).')
                            ->prefixIcon('lucide-list-ordered')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12)
                            ->required(),
                    ]),

                Section::make('Media Sosial, Kontak & Legal')
                    ->description('Konten pendukung serta tautan kecil pada bagian bawah footer.')
                    ->icon('lucide-info')
                    ->schema([
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale): array => [
                                TextInput::make('social_title')
                                    ->label('Judul Media Sosial')
                                    ->placeholder('Contoh: Ikuti Media Sosial Kami')
                                    ->helperText('Judul untuk area tautan sosial media.')
                                    ->prefixIcon('lucide-type')
                                    ->required($locale === 'id'),
                                Textarea::make('social_description')
                                    ->label('Deskripsi Media Sosial')
                                    ->rows(2)
                                    ->placeholder('Tuliskan deskripsi ajakan untuk mengikuti media sosial...')
                                    ->helperText('Kalimat deskripsi singkat di bawah judul media sosial.')
                                    ->required($locale === 'id'),
                                TextInput::make('contact_title')
                                    ->label('Judul Kontak')
                                    ->placeholder('Contoh: Hubungi Kami')
                                    ->helperText('Judul untuk area informasi kontak di footer.')
                                    ->prefixIcon('lucide-type')
                                    ->required($locale === 'id'),
                                TextInput::make('destinations_all_label')
                                    ->label('Label Semua Destinasi')
                                    ->placeholder('Contoh: Lihat Semua Destinasi')
                                    ->helperText('Teks tautan untuk mengarahkan pengguna ke halaman seluruh destinasi.')
                                    ->prefixIcon('lucide-type')
                                    ->required($locale === 'id'),
                                TextInput::make('copyright_text')
                                    ->label('Copyright')
                                    ->placeholder('Contoh: © 2026 Baharsyah Jelajah. All rights reserved.')
                                    ->helperText('Teks hak cipta yang muncul di bagian paling bawah halaman.')
                                    ->prefixIcon('lucide-copyright')
                                    ->required($locale === 'id'),
                            ]),
                        Repeater::make('legal_links')
                            ->label('Daftar Tautan Hukum & Legal')
                            ->addActionLabel('Tambah Tautan Legal')
                            ->cloneable()
                            ->itemLabel(fn (array $state): ?string => $state['label']['id'] ?? $state['label']['en'] ?? 'Tautan Legal Baru')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('label.id')
                                        ->label('Label Tautan (ID)')
                                        ->placeholder('Contoh: Kebijakan Privasi')
                                        ->required(),
                                    TextInput::make('label.en')
                                        ->label('Label Tautan (EN)')
                                        ->placeholder('Contoh: Privacy Policy'),
                                    TextInput::make('label.ms')
                                        ->label('Label Tautan (MS)')
                                        ->placeholder('Contoh: Dasar Privasi'),
                                ]),
                                Grid::make(3)->schema([
                                    TextInput::make('route')
                                        ->label('Nama Route')
                                        ->placeholder('Contoh: legal.privacy')
                                        ->prefixIcon('lucide-link-2'),
                                    TextInput::make('fragment')
                                        ->label('Fragment URL (#)')
                                        ->placeholder('faq')
                                        ->prefixIcon('lucide-hash'),
                                    TextInput::make('url')
                                        ->label('URL Manual')
                                        ->url()
                                        ->placeholder('Contoh: https://external-link.com')
                                        ->prefixIcon('lucide-external-link'),
                                ]),
                            ])
                            ->columns(1)
                            ->reorderable()
                            ->collapsible(),
                    ]),
            ])
            ->columns(1);
    }
}
