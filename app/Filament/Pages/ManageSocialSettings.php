<?php

namespace App\Filament\Pages;

use App\Settings\SocialSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageSocialSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShare;

    protected static string|null|\UnitEnum $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Media Sosial';

    protected static ?string $title = 'Media Sosial';

    protected static ?int $navigationSort = 3;

    protected static string $settings = SocialSettings::class;

    protected ?string $heading = 'Media Sosial';

    protected ?string $subheading = 'Hubungkan website dengan akun media sosial resmi Anda.';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tautan Media Sosial')
                    ->description('Masukkan URL lengkap profil media sosial Anda.')
                    ->icon(Heroicon::OutlinedGlobeAlt)
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('instagram')
                                ->label('Instagram')
                                ->placeholder('https://instagram.com/username')
                                ->url()
                                ->prefixIcon('heroicon-o-camera'),
                            TextInput::make('facebook')
                                ->label('Facebook')
                                ->placeholder('https://facebook.com/username')
                                ->url()
                                ->prefixIcon('heroicon-o-user-group'),
                            TextInput::make('tiktok')
                                ->label('TikTok')
                                ->placeholder('https://tiktok.com/@username')
                                ->url()
                                ->prefixIcon('heroicon-o-musical-note'),
                            TextInput::make('youtube')
                                ->label('YouTube')
                                ->placeholder('https://youtube.com/@channel')
                                ->url()
                                ->prefixIcon('heroicon-o-video-camera'),
                        ]),
                    ]),
            ]);
    }
}
