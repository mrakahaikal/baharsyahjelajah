<?php

namespace App\Filament\Resources\Tours\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class IncludesRelationManager extends RelationManager
{
    protected static string $relationship = 'includes';

    protected static ?string $title = 'Fasilitas (Inclusion & Exclusion)';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('type')
                                    ->label('Tipe Fasilitas')
                                    ->options([
                                        'include' => 'Termasuk (Inclusion)',
                                        'exclude' => 'Tidak Termasuk (Exclusion)',
                                    ])
                                    ->required()
                                    ->default('include'),
                                TextInput::make('sort_order')
                                    ->label('Urutan')
                                    ->placeholder('0')
                                    ->numeric()
                                    ->default(0),
                            ]),
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                TextInput::make('item')
                                    ->label('Nama Fasilitas')
                                    ->placeholder('Misal: Tiket Pesawat Pulang Pergi')
                                    ->required($locale === 'id')
                                    ->maxLength(255),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('item')
            ->defaultSort('sort_order', 'asc')
            ->columns([
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'include' => 'Termasuk',
                        'exclude' => 'Tidak Termasuk',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'include' => 'success',
                        'exclude' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('item')
                    ->label('Nama Fasilitas')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Filter Tipe')
                    ->options([
                        'include' => 'Termasuk',
                        'exclude' => 'Tidak Termasuk',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Fasilitas')
                    ->icon(Heroicon::OutlinedPlus),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
