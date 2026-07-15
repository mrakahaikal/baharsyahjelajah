<?php

namespace App\Filament\Resources\WhatsappTemplates;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Filament\Resources\WhatsappTemplates\Pages\ManageWhatsappTemplates;
use App\Models\WhatsappTemplate;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WhatsappTemplateResource extends Resource
{
    protected static ?string $model = WhatsappTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = 'lucide-message-square';

    protected static ?string $navigationLabel = 'Template WhatsApp';

    protected static ?string $modelLabel = 'Template WhatsApp';

    protected static ?string $pluralModelLabel = 'Template WhatsApp';

    protected static ?string $recordTitleAttribute = 'product_type';

    protected static ?int $navigationSort = 5;

    protected static ?string $cluster = SettingsCluster::class;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pengaturan Template')
                    ->description('Kelola tipe produk, bahasa, isi pesan template WhatsApp, beserta variabel dinamis.')
                    ->icon('lucide-message-square')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('product_type')
                                ->label('Tipe Produk')
                                ->options([
                                    'tour' => 'Tour Wisata',
                                    'umrah' => 'Paket Umrah',
                                    'vehicle' => 'Sewa Kendaraan',
                                    'visa' => 'Layanan Visa',
                                ])
                                ->placeholder('Pilih tipe produk')
                                ->prefixIcon('lucide-package')
                                ->native(false)
                                ->required(),
                            Select::make('locale')
                                ->label('Bahasa')
                                ->options([
                                    'id' => 'Indonesia',
                                    'en' => 'English',
                                    'ms' => 'Melayu',
                                ])
                                ->placeholder('Pilih bahasa')
                                ->prefixIcon('lucide-globe')
                                ->native(false)
                                ->required(),
                        ]),
                        Textarea::make('template')
                            ->label('Isi Template Pesan')
                            ->placeholder('Halo, saya ingin memesan {product_name} untuk {pax} orang...')
                            ->helperText('Gunakan placeholder yang sesuai, seperti {product_name}, {country}, {pax}, {price}, atau {date}.')
                            ->rows(8)
                            ->required()
                            ->columnSpanFull(),
                        TagsInput::make('variables')
                            ->label('Daftar Variabel Penampung')
                            ->placeholder('Tambah variabel baru lalu tekan enter...')
                            ->helperText('Tambahkan semua variabel yang digunakan dalam template, misal: {product_name}')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_type')
            ->columns([
                TextColumn::make('product_type')
                    ->label('Tipe Produk')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'tour' => 'Tour Wisata',
                        'umrah' => 'Paket Umrah',
                        'vehicle' => 'Sewa Kendaraan',
                        'visa' => 'Layanan Visa',
                        default => $state ?? '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'tour' => 'success',
                        'umrah' => 'warning',
                        'vehicle' => 'info',
                        'visa' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('locale')
                    ->label('Bahasa')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'id' => 'Indonesia',
                        'en' => 'English',
                        'ms' => 'Melayu',
                        default => $state ?? '-',
                    }),
                TextColumn::make('template')
                    ->label('Isi Template')
                    ->limit(80)
                    ->wrap(),
                TextColumn::make('updated_at')
                    ->label('Tanggal Pembaruan')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_type')
                    ->label('Tipe Produk')
                    ->options([
                        'tour' => 'Tour Wisata',
                        'umrah' => 'Paket Umrah',
                        'vehicle' => 'Sewa Kendaraan',
                        'visa' => 'Layanan Visa',
                    ])
                    ->native(false),
                SelectFilter::make('locale')
                    ->label('Bahasa')
                    ->options([
                        'id' => 'Indonesia',
                        'en' => 'English',
                        'ms' => 'Melayu',
                    ])
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Ubah')
                    ->icon('lucide-pencil')
                    ->slideOver()
                    ->color('primary'),
                DeleteAction::make()
                    ->label('Hapus')
                    ->icon('lucide-trash')
                    ->color('danger'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Template WhatsApp')
            ->emptyStateDescription('Templat pesan otomatis WhatsApp belum terdaftar. Silakan tambahkan templat pertama Anda.')
            ->emptyStateIcon('lucide-message-square')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Tambah Template')
                    ->icon('lucide-plus')
                    ->slideOver(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageWhatsappTemplates::route('/'),
        ];
    }
}
