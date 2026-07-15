<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Settings\SocialSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ManageSocialSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = 'lucide-share-2';

    protected static ?string $navigationLabel = 'Media Sosial';

    protected static ?string $title = 'Media Sosial';

    protected static ?int $navigationSort = 3;

    protected static string $settings = SocialSettings::class;

    protected ?string $heading = 'Media Sosial';

    protected ?string $subheading = 'Hubungkan website dengan akun media sosial resmi Anda.';

    protected static ?string $cluster = SettingsCluster::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tautan Media Sosial Resmi')
                    ->description('Kelola alamat tautan lengkap profil akun media sosial resmi perusahaan Anda.')
                    ->icon('lucide-share-2')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('instagram')
                                ->label('Instagram URL')
                                ->placeholder('Contoh: https://instagram.com/username')
                                ->url()
                                ->helperText('Tautan URL lengkap profil Instagram.')
                                ->prefixIcon('lucide-instagram'),
                            TextInput::make('facebook')
                                ->label('Facebook URL')
                                ->placeholder('Contoh: https://facebook.com/username')
                                ->url()
                                ->helperText('Tautan URL lengkap profil Facebook.')
                                ->prefixIcon('lucide-facebook'),
                            TextInput::make('tiktok')
                                ->label('TikTok URL')
                                ->placeholder('Contoh: https://tiktok.com/@username')
                                ->url()
                                ->helperText('Tautan URL lengkap profil TikTok.')
                                ->prefixIcon('lucide-music'),
                            TextInput::make('youtube')
                                ->label('YouTube URL')
                                ->placeholder('Contoh: https://youtube.com/@channel')
                                ->url()
                                ->helperText('Tautan URL lengkap channel YouTube.')
                                ->prefixIcon('lucide-youtube'),
                        ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
