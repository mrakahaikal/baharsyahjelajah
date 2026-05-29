<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class ManageGeneralSettings extends SettingsPage
{
    protected static string|null|BackedEnum $navigationIcon  = Heroicon::OutlinedCog6Tooth;
    protected static string|null|\UnitEnum $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Umum & Kontak';
    protected static ?string $title = 'Pengaturan Umum';
    protected static ?int    $navigationSort  = 1;

    protected static string $settings = GeneralSettings::class;

    protected ?string $heading = 'Pengaturan Umum';

    protected ?string $subheading = 'Kelola identitas website, informasi kontak, dan preferensi default.';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Website')
                    ->description('Informasi utama yang muncul di judul dan meta website.')
                    ->icon(Heroicon::OutlinedGlobeAlt)
                    ->schema([
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                TextInput::make('site_name')
                                    ->label('Nama Website')
                                    ->placeholder('Misal: Baharsyah Jelajah')
                                    ->required($locale === 'id'),
                                Textarea::make('meta_description')
                                    ->label('Deskripsi Meta')
                                    ->placeholder('Tuliskan deskripsi singkat website untuk SEO...')
                                    ->rows(3)
                                    ->required($locale === 'id'),
                            ]),
                    ]),

                Section::make('Informasi Kontak & Alamat')
                    ->description('Detail kontak yang dapat dihubungi oleh pelanggan.')
                    ->icon(Heroicon::OutlinedPhone)
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('whatsapp_number')
                                ->label('Nomor WhatsApp')
                                ->placeholder('6281234567890')
                                ->prefix('+')
                                ->helperText('Gunakan format kode negara (62) tanpa tanda + atau spasi.')
                                ->required(),
                            TextInput::make('email')
                                ->label('Email Operasional')
                                ->placeholder('info@baharsyahjelajah.com')
                                ->email()
                                ->required(),
                        ]),
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                Textarea::make('address')
                                    ->label('Alamat Kantor')
                                    ->placeholder('Tuliskan alamat lengkap kantor pusat...')
                                    ->rows(3),
                            ]),
                    ]),

                Section::make('Preferensi Default')
                    ->description('Pengaturan standar untuk aplikasi dan perhitungan harga.')
                    ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('default_currency')
                                ->label('Mata Uang Utama')
                                ->options([
                                    'IDR' => '🇮🇩 IDR (Indonesian Rupiah)',
                                    'MYR' => '🇲🇾 MYR (Malaysian Ringgit)',
                                    'SGD' => '🇸🇬 SGD (Singapore Dollar)',
                                ])
                                ->required(),
                            TextInput::make('default_pax')
                                ->label('Jumlah Peserta Standar')
                                ->placeholder('2')
                                ->numeric()
                                ->minValue(1)
                                ->helperText('Jumlah peserta default saat perhitungan harga pertama kali.'),
                        ]),
                    ]),
            ]);
    }
}
