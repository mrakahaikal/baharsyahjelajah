<?php

namespace App\Filament\Resources\UmrahPackages\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
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
                Section::make()->schema([
                    Grid::make(2)->schema([
                        DatePicker::make('departure_date')
                            ->label('Tanggal Berangkat')
                            ->native(false)
                            ->required(),
                        DatePicker::make('return_date')
                            ->label('Tanggal Kembali')
                            ->native(false)
                            ->required(),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('quota_total')
                            ->label('Kuota Total')
                            ->numeric()
                            ->suffix('orang')
                            ->required(),
                        TextInput::make('quota_booked')
                            ->label('Sudah Terdaftar')
                            ->numeric()
                            ->suffix('orang')
                            ->default(0),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'open' => 'Tersedia',
                                'nearly_full' => 'Hampir Penuh',
                                'full' => 'Penuh',
                                'closed' => 'Ditutup',
                            ])
                            ->default('open')
                            ->required(),
                    ]),
                    TextInput::make('price_override_idr')
                        ->label('Override Harga (opsional)')
                        ->helperText('Kosongkan jika mengikuti harga paket.')
                        ->numeric()
                        ->prefix('Rp'),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('departure_date')
            ->defaultSort('departure_date', 'asc')
            ->columns([
                TextColumn::make('departure_date')
                    ->label('Berangkat')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('return_date')
                    ->label('Kembali')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('quota_total')
                    ->label('Kuota')
                    ->formatStateUsing(fn ($record): string => "{$record->quota_booked}/{$record->quota_total}")
                    ->alignCenter(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'Tersedia',
                        'nearly_full' => 'Hampir Penuh',
                        'full' => 'Penuh',
                        'closed' => 'Ditutup',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'success',
                        'nearly_full' => 'warning',
                        'full' => 'danger',
                        'closed' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('price_override_idr')
                    ->label('Override Harga')
                    ->money('IDR', divideBy: 1)
                    ->placeholder('Ikut harga paket')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Tersedia',
                        'nearly_full' => 'Hampir Penuh',
                        'full' => 'Penuh',
                        'closed' => 'Ditutup',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Jadwal')
                    ->icon(Heroicon::OutlinedPlus),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Ubah'),
                DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ]);
    }
}
