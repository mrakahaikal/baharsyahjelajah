<?php

namespace App\Filament\Resources\WhatsappTemplates;

use App\Filament\Resources\WhatsappTemplates\Pages\ManageWhatsappTemplates;
use App\Models\WhatsappTemplate;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
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
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WhatsappTemplateResource extends Resource
{
    protected static ?string $model = WhatsappTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleOvalLeft;

    protected static string|null|\UnitEnum $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Template WhatsApp';

    protected static ?string $modelLabel = 'Template WhatsApp';

    protected static ?string $pluralModelLabel = 'Template WhatsApp';

    protected static ?string $recordTitleAttribute = 'product_type';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pengaturan Template')
                    ->icon(Heroicon::OutlinedChatBubbleOvalLeft)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('product_type')
                                ->label('Tipe Produk')
                                ->options([
                                    'tour'    => 'Tour Wisata',
                                    'umrah'   => 'Paket Umrah',
                                    'vehicle' => 'Sewa Kendaraan',
                                ])
                                ->required(),
                            Select::make('locale')
                                ->label('Bahasa')
                                ->options([
                                    'id' => 'Indonesia',
                                    'en' => 'English',
                                    'ms' => 'Melayu',
                                ])
                                ->required(),
                        ]),
                        Textarea::make('template')
                            ->label('Isi Template')
                            ->placeholder("Halo, saya ingin memesan {product_name} untuk {pax} orang...")
                            ->helperText('Gunakan placeholder seperti {product_name}, {pax}, {total_price}, {date} di dalam template.')
                            ->rows(8)
                            ->required()
                            ->columnSpanFull(),
                        TagsInput::make('variables')
                            ->label('Daftar Variabel')
                            ->placeholder('Tambah variabel...')
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
                        'tour'    => 'Tour Wisata',
                        'umrah'   => 'Paket Umrah',
                        'vehicle' => 'Sewa Kendaraan',
                        default   => $state ?? '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'tour'    => 'success',
                        'umrah'   => 'warning',
                        'vehicle' => 'info',
                        default   => 'gray',
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
                    ->label('Template')
                    ->limit(80)
                    ->wrap(),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_type')
                    ->label('Tipe Produk')
                    ->options([
                        'tour'    => 'Tour Wisata',
                        'umrah'   => 'Paket Umrah',
                        'vehicle' => 'Sewa Kendaraan',
                    ]),
                SelectFilter::make('locale')
                    ->label('Bahasa')
                    ->options([
                        'id' => 'Indonesia',
                        'en' => 'English',
                        'ms' => 'Melayu',
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
            'index' => ManageWhatsappTemplates::route('/'),
        ];
    }
}
