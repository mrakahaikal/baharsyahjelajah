<?php

namespace App\Filament\Resources\UmrahPackages\RelationManagers;

use App\Enums\UmrahRoomType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class PricesRelationManager extends RelationManager
{
    protected static string $relationship = 'prices';

    protected static ?string $title = 'Harga Tipe Kamar';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kelola Harga Tipe Kamar')
                    ->description('Tentukan tarif nominal per jamaah berdasarkan pilihan kapasitas tipe kamar.')
                    ->icon('lucide-banknote')
                    ->schema([
                        Select::make('room_type')
                            ->label('Tipe Kamar / Kapasitas')
                            ->options(UmrahRoomType::class)
                            ->unique(
                                table: 'umrah_package_prices',
                                column: 'room_type',
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule): Unique => $rule->where(
                                    'umrah_package_id',
                                    $this->getOwnerRecord()->getKey(),
                                ),
                            )
                            ->required()
                            ->placeholder('Pilih tipe kamar')
                            ->helperText('Pilihan jenis kamar (Double, Triple, atau Quad).')
                            ->prefixIcon('lucide-bed')
                            ->native(false),
                        TextInput::make('price_idr')
                            ->label('Harga per Jamaah (IDR)')
                            ->numeric()
                            ->minValue(1)
                            ->prefix('Rp')
                            ->required()
                            ->placeholder('Contoh: 32000000')
                            ->helperText('Tarif nominal per orang untuk tipe kamar ini.')
                            ->prefixIcon('lucide-banknote'),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('room_type')
            ->defaultSort('price_idr')
            ->columns([
                TextColumn::make('room_type')
                    ->label('Tipe Kamar')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => UmrahRoomType::tryFrom($state)?->getLabel() ?? $state),
                TextColumn::make('price_idr')
                    ->label('Harga per Jamaah')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Harga')
                    ->icon('lucide-plus'),
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
