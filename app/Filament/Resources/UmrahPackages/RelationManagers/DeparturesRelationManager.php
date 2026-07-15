<?php

namespace App\Filament\Resources\UmrahPackages\RelationManagers;

use App\Enums\UmrahDepartureStatus;
use App\Enums\UmrahRoomType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DeparturesRelationManager extends RelationManager
{
    protected static string $relationship = 'departures';

    protected static ?string $title = 'Jadwal Keberangkatan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pengaturan Jadwal & Kuota')
                    ->description('Atur tanggal keberangkatan, tanggal kepulangan, kapasitas kuota jamaah, dan status keberangkatan.')
                    ->icon('lucide-calendar-days')
                    ->schema([
                        Grid::make(2)->schema([
                            DatePicker::make('departure_date')
                                ->label('Tanggal Berangkat')
                                ->native(false)
                                ->required()
                                ->placeholder('Pilih tanggal berangkat')
                                ->helperText('Tanggal keberangkatan perjalanan umrah.')
                                ->prefixIcon('lucide-plane-takeoff'),
                            DatePicker::make('return_date')
                                ->label('Tanggal Kembali')
                                ->native(false)
                                ->after('departure_date')
                                ->required()
                                ->placeholder('Pilih tanggal kembali')
                                ->helperText('Tanggal kepulangan kembali ke tanah air.')
                                ->prefixIcon('lucide-plane-landing'),
                        ]),
                        Grid::make(3)->schema([
                            TextInput::make('quota_total')
                                ->label('Kuota Total')
                                ->numeric()
                                ->minValue(1)
                                ->suffix('jamaah')
                                ->live(onBlur: true)
                                ->required()
                                ->placeholder('Contoh: 45')
                                ->helperText('Kapasitas kursi maksimal untuk keberangkatan ini.')
                                ->prefixIcon('lucide-users'),
                            TextInput::make('quota_booked')
                                ->label('Jumlah Terdaftar')
                                ->numeric()
                                ->default(0)
                                ->rules(fn (Get $get): array => ['integer', 'min:0', 'max:'.max(0, (int) $get('quota_total'))])
                                ->suffix('jamaah')
                                ->required()
                                ->placeholder('Contoh: 0')
                                ->helperText('Jumlah jamaah yang sudah terdaftar dan memesan.')
                                ->prefixIcon('lucide-user-check'),
                            Select::make('status')
                                ->label('Status Kuota Keberangkatan')
                                ->options(UmrahDepartureStatus::class)
                                ->helperText('Status kuota dihitung otomatis. Pilih Ditutup untuk menutup jadwal manual.')
                                ->default(UmrahDepartureStatus::Open->value)
                                ->required()
                                ->placeholder('Pilih status')
                                ->prefixIcon('lucide-info')
                                ->native(false),
                        ]),
                    ]),
                Section::make('Override Harga Khusus Keberangkatan')
                    ->description('Atur harga khusus untuk tipe kamar tertentu pada tanggal keberangkatan ini. Kosongkan jika ingin mengikuti harga standar paket.')
                    ->icon('lucide-banknote')
                    ->schema([
                        Repeater::make('prices')
                            ->relationship()
                            ->hiddenLabel()
                            ->schema([
                                Select::make('umrah_package_price_id')
                                    ->label('Tipe Kamar')
                                    ->options(fn (): array => $this->getOwnerRecord()
                                        ->prices()
                                        ->get()
                                        ->mapWithKeys(fn ($price): array => [
                                            $price->getKey() => UmrahRoomType::tryFrom($price->room_type)?->getLabel() ?? $price->room_type,
                                        ])
                                        ->all())
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->required()
                                    ->placeholder('Pilih tipe kamar')
                                    ->prefixIcon('lucide-bed')
                                    ->native(false),
                                TextInput::make('price_idr')
                                    ->label('Harga Override')
                                    ->numeric()
                                    ->minValue(1)
                                    ->prefix('Rp')
                                    ->required()
                                    ->placeholder('Contoh: 30000000')
                                    ->helperText('Tarif khusus yang berlaku pada jadwal ini.')
                                    ->prefixIcon('lucide-banknote'),
                            ])
                            ->columns(2)
                            ->addActionLabel('Tambah Override Harga')
                            ->defaultItems(0)
                            ->cloneable()
                            ->itemLabel(fn (array $state): ?string => isset($state['price_idr']) ? 'Override: Rp '.number_format($state['price_idr'], 0, ',', '.') : 'Override Baru'),
                    ]),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('departure_date')
            ->defaultSort('departure_date')
            ->columns([
                TextColumn::make('departure_date')->label('Berangkat')->date('d M Y')->sortable(),
                TextColumn::make('return_date')->label('Kembali')->date('d M Y')->sortable(),
                TextColumn::make('quota')
                    ->label('Kuota Terisi')
                    ->state(fn ($record): string => $record->quota_booked.'/'.$record->quota_total)
                    ->description(fn ($record): string => $record->quota_sisa.' kursi tersisa')
                    ->alignCenter(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => UmrahDepartureStatus::tryFrom($state)?->getLabel() ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'success',
                        'nearly_full' => 'warning',
                        'full' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('prices_count')
                    ->label('Override')
                    ->counts('prices')
                    ->suffix(' harga')
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(UmrahDepartureStatus::class),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Jadwal')->icon('lucide-plus'),
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
}
