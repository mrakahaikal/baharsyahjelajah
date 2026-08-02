<?php

namespace App\Filament\Resources\UmrahPackages\RelationManagers;

use App\Enums\UmrahPackageType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;

class PrivatePricesRelationManager extends RelationManager
{
    protected static string $relationship = 'privatePrices';

    protected static ?string $title = 'Harga Paket Istimewa';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->package_type === UmrahPackageType::Private;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kelola Harga Paket Istimewa')
                    ->description('Tentukan tarif nominal per pax berdasarkan pilihan durasi malam dan jumlah peserta.')
                    ->icon('lucide-banknote')
                    ->schema([
                        TextInput::make('duration_nights')
                            ->label('Durasi (Malam)')
                            ->numeric()
                            ->minValue(1)
                            ->suffix('Malam')
                            ->required()
                            ->placeholder('Contoh: 6')
                            ->helperText('Durasi malam perjalanan umrah istimewa.')
                            ->prefixIcon('lucide-moon'),
                        TextInput::make('pax')
                            ->label('Jumlah Peserta (Pax)')
                            ->numeric()
                            ->minValue(1)
                            ->suffix('Pax')
                            ->required()
                            ->placeholder('Contoh: 4')
                            ->helperText('Jumlah minimum peserta untuk tarif ini.')
                            ->prefixIcon('lucide-users')
                            ->unique(
                                table: 'umrah_package_private_prices',
                                column: 'pax',
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule
                                    ->where('umrah_package_id', $this->getOwnerRecord()->getKey())
                                    ->where('duration_nights', (int) $get('duration_nights')),
                            ),
                        TextInput::make('price_idr')
                            ->label('Harga per Jamaah (IDR)')
                            ->numeric()
                            ->minValue(1)
                            ->prefix('Rp')
                            ->required()
                            ->placeholder('Contoh: 14000000')
                            ->helperText('Tarif nominal per orang.')
                            ->prefixIcon('lucide-banknote')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('price_idr')
            ->defaultSort('duration_nights')
            ->columns([
                TextColumn::make('duration_nights')
                    ->label('Durasi')
                    ->suffix(' Malam')
                    ->sortable(),
                TextColumn::make('pax')
                    ->label('Jumlah Peserta')
                    ->suffix(' Pax')
                    ->sortable(),
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
