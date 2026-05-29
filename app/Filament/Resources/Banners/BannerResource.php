<?php

namespace App\Filament\Resources\Banners;

use App\Filament\Resources\Banners\Pages\ManageBanners;
use App\Models\Banner;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|null|\UnitEnum $navigationGroup = 'Konten Website';

    protected static ?string $navigationLabel = 'Banner';

    protected static ?string $modelLabel = 'Banner';

    protected static ?string $pluralModelLabel = 'Banner';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Konten Banner')
                    ->icon(Heroicon::OutlinedPhoto)
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Gambar Banner')
                            ->image()
                            ->directory('banners')
                            ->visibility('public')
                            ->required()
                            ->columnSpanFull(),
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                TextInput::make('title')
                                    ->label('Judul')
                                    ->placeholder('Judul utama banner...')
                                    ->required($locale === 'id')
                                    ->maxLength(255),
                                TextInput::make('subtitle')
                                    ->label('Subjudul')
                                    ->placeholder('Teks deskripsi banner...')
                                    ->maxLength(500),
                                TextInput::make('cta_label')
                                    ->label('Label Tombol CTA')
                                    ->placeholder('Misal: Pesan Sekarang')
                                    ->maxLength(100),
                            ]),
                    ]),
                Section::make('Tombol CTA & Pengaturan')
                    ->icon(Heroicon::OutlinedCursorArrowRays)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('cta_type')
                                ->label('Tipe CTA')
                                ->options([
                                    'route' => 'Route Laravel',
                                    'url' => 'URL Langsung',
                                    'whatsapp' => 'WhatsApp',
                                ])
                                ->live(),
                            TextInput::make('cta_value')
                                ->label('Nilai CTA')
                                ->placeholder('Misal: tours.index atau https://...')
                                ->maxLength(255),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('sort_order')
                                ->label('Urutan')
                                ->numeric()
                                ->default(0),
                            Toggle::make('is_active')
                                ->label('Aktif')
                                ->default(true),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('sort_order', 'asc')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Gambar')
                    ->height(50),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('cta_type')
                    ->label('Tipe CTA')
                    ->badge(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make()->label('Ubah'),
                DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus Terpilih'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBanners::route('/'),
        ];
    }
}
