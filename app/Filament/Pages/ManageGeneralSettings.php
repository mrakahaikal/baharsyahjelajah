<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Settings\GeneralSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class ManageGeneralSettings extends SettingsPage
{
    protected static string|null|BackedEnum $navigationIcon = 'lucide-settings';

    protected static ?string $navigationLabel = 'Umum & Kontak';

    protected static ?string $title = 'Pengaturan Umum';

    protected static ?int $navigationSort = 1;

    protected static string $settings = GeneralSettings::class;

    protected ?string $heading = 'Pengaturan Umum';

    protected ?string $subheading = 'Kelola identitas website, informasi kontak, dan preferensi default.';

    protected static ?string $cluster = SettingsCluster::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Website')
                    ->description('Informasi utama yang muncul di judul dan meta website.')
                    ->icon('lucide-globe')
                    ->schema([
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                TextInput::make('site_name')
                                    ->label('Nama Website')
                                    ->placeholder('Misal: Baharsyah Jelajah')
                                    ->required($locale === 'id')
                                    ->helperText('Nama utama website yang akan muncul di bilah judul browser.')
                                    ->prefixIcon('lucide-type'),
                                Textarea::make('meta_description')
                                    ->label('Deskripsi Meta (SEO)')
                                    ->placeholder('Tuliskan deskripsi singkat website untuk SEO...')
                                    ->rows(3)
                                    ->required($locale === 'id')
                                    ->helperText('Deskripsi ringkas situs Anda untuk hasil pencarian mesin pencari.'),
                            ]),
                    ]),

                Section::make('Informasi Kontak & Alamat')
                    ->description('Detail kontak yang dapat dihubungi oleh pelanggan.')
                    ->icon('lucide-phone')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('whatsapp_number')
                                ->label('Nomor WhatsApp Operasional')
                                ->placeholder('6281234567890')
                                ->prefix('+')
                                ->helperText('Gunakan format kode negara (62) tanpa tanda + atau spasi.')
                                ->prefixIcon('lucide-message-square')
                                ->required(),
                            TextInput::make('email')
                                ->label('Email Hubungan Pelanggan')
                                ->placeholder('info@baharsyahjelajah.com')
                                ->email()
                                ->prefixIcon('lucide-mail')
                                ->helperText('Alamat email resmi untuk komunikasi dengan pelanggan.')
                                ->required(),
                            TextInput::make('map_embed_url')
                                ->label('Google Maps Embed URL (Iframe)')
                                ->placeholder('https://www.google.com/maps/embed?...')
                                ->url()
                                ->helperText('Gunakan URL iframe embed dari Google Maps.')
                                ->prefixIcon('lucide-map-pin')
                                ->columnSpanFull(),
                        ]),
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                Textarea::make('address')
                                    ->label('Alamat Kantor')
                                    ->placeholder('Tuliskan alamat lengkap kantor pusat...')
                                    ->helperText('Alamat fisik kantor yang akan ditampilkan pada footer website.')
                                    ->rows(3),
                                Textarea::make('office_hours')
                                    ->label('Jam Respons / Operasional')
                                    ->placeholder('Senin - Sabtu, 09.00 - 18.00 WIB')
                                    ->helperText('Waktu operasional pelayanan admin.')
                                    ->rows(2),
                            ]),
                    ]),

                Section::make('Preferensi Default')
                    ->description('Pengaturan standar untuk aplikasi dan perhitungan harga.')
                    ->icon('lucide-sliders')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('default_currency')
                                ->label('Mata Uang Utama Default')
                                ->options(fn (): array => collect(config('currencies.supported'))
                                    ->mapWithKeys(fn (array $metadata, string $code): array => [
                                        $code => "{$code} ({$metadata['symbol']}) - {$metadata['name']}",
                                    ])
                                    ->all())
                                ->required()
                                ->placeholder('Pilih mata uang utama')
                                ->helperText('Mata uang utama yang digunakan secara default di seluruh sistem.')
                                ->prefixIcon('lucide-coins')
                                ->native(false),
                            TextInput::make('default_pax')
                                ->label('Jumlah Peserta Bawaan (Pax)')
                                ->placeholder('2')
                                ->numeric()
                                ->minValue(1)
                                ->helperText('Jumlah peserta default saat perhitungan harga pertama kali.')
                                ->prefixIcon('lucide-users'),
                        ]),
                    ]),
            ])
            ->columns(1);
    }
}
