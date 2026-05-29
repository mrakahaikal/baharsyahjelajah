<?php

namespace App\Filament\Resources\Testimonials;

use App\Filament\Resources\Testimonials\Pages\ManageTestimonials;
use App\Models\Testimonial;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|null|\UnitEnum $navigationGroup = 'Konten Website';

    protected static ?string $navigationLabel = 'Testimoni';

    protected static ?string $modelLabel = 'Testimoni';

    protected static ?string $pluralModelLabel = 'Testimoni';

    protected static ?string $recordTitleAttribute = 'reviewer_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Reviewer')
                    ->icon(Heroicon::OutlinedUser)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('reviewer_country')
                                ->label('Negara')
                                ->placeholder('Misal: Indonesia')
                                ->maxLength(100),
                            TextInput::make('reviewer_flag')
                                ->label('Bendera (Emoji)')
                                ->placeholder('Misal: 🇮🇩')
                                ->maxLength(10),
                        ]),
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                TextInput::make('reviewer_name')
                                    ->label('Nama Reviewer')
                                    ->placeholder('Nama lengkap reviewer...')
                                    ->required($locale === 'id')
                                    ->maxLength(255),
                            ]),
                        FileUpload::make('photo')
                            ->label('Foto Reviewer')
                            ->image()
                            ->directory('testimonials/photos')
                            ->visibility('public')
                            ->avatar()
                            ->columnSpanFull(),
                    ]),
                Section::make('Ulasan')
                    ->icon(Heroicon::OutlinedStar)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('product_type')
                                ->label('Tipe Produk')
                                ->options([
                                    'App\Models\Tour'         => 'Tour Wisata',
                                    'App\Models\Vehicle'      => 'Sewa Kendaraan',
                                    'App\Models\UmrahPackage' => 'Paket Umrah',
                                ])
                                ->required(),
                            TextInput::make('product_id')
                                ->label('ID Produk')
                                ->numeric()
                                ->required(),
                            Select::make('rating')
                                ->label('Rating')
                                ->options([
                                    1 => '★ — Sangat Buruk',
                                    2 => '★★ — Buruk',
                                    3 => '★★★ — Cukup',
                                    4 => '★★★★ — Baik',
                                    5 => '★★★★★ — Sangat Baik',
                                ])
                                ->required(),
                        ]),
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                TextInput::make('content')
                                    ->label('Isi Ulasan')
                                    ->placeholder('Ceritakan pengalaman Anda...')
                                    ->required($locale === 'id')
                                    ->maxLength(1000)
                                    ->columnSpanFull(),
                            ]),
                        Grid::make(2)->schema([
                            Toggle::make('is_featured')
                                ->label('Unggulan')
                                ->default(false),
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
            ->recordTitleAttribute('reviewer_name')
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular()
                    ->size(40),
                TextColumn::make('reviewer_name')
                    ->label('Reviewer')
                    ->searchable()
                    ->description(fn (Testimonial $record): string => $record->reviewer_country ?? ''),
                TextColumn::make('product_type')
                    ->label('Produk')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'App\Models\Tour'         => 'Tour',
                        'App\Models\Vehicle'      => 'Sewa Mobil',
                        'App\Models\UmrahPackage' => 'Umrah',
                        default                   => $state ?? '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'App\Models\Tour'         => 'success',
                        'App\Models\Vehicle'      => 'info',
                        'App\Models\UmrahPackage' => 'warning',
                        default                   => 'gray',
                    }),
                TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state) . str_repeat('☆', 5 - $state))
                    ->alignCenter(),
                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Ditambahkan')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_type')
                    ->label('Tipe Produk')
                    ->options([
                        'App\Models\Tour'         => 'Tour Wisata',
                        'App\Models\Vehicle'      => 'Sewa Kendaraan',
                        'App\Models\UmrahPackage' => 'Paket Umrah',
                    ]),
                SelectFilter::make('rating')
                    ->label('Rating')
                    ->options([
                        1 => '1 Bintang',
                        2 => '2 Bintang',
                        3 => '3 Bintang',
                        4 => '4 Bintang',
                        5 => '5 Bintang',
                    ]),
            ])
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
            'index' => ManageTestimonials::route('/'),
        ];
    }
}
