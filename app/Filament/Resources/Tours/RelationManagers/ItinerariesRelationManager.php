<?php

namespace App\Filament\Resources\Tours\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class ItinerariesRelationManager extends RelationManager
{
    protected static string $relationship = 'itineraries';

    protected static ?string $title = 'Itinerary / Jadwal Perjalanan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('day_number')
                            ->label('Hari Ke-')
                            ->placeholder('1')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->columnSpanFull(),
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                TextInput::make('title')
                                    ->label('Judul Aktivitas')
                                    ->placeholder('Misal: Penjemputan di Bandara & Check-in Hotel')
                                    ->required($locale === 'id')
                                    ->maxLength(255),
                                RichEditor::make('description')
                                    ->label('Deskripsi Kegiatan')
                                    ->placeholder('Tuliskan detail aktivitas untuk hari ini...')
                                    ->required($locale === 'id')
                                    ->columnSpanFull(),
                                TextInput::make('meals_included')
                                    ->label('Makan (Included)')
                                    ->placeholder('Misal: Makan Pagi, Makan Siang, Makan Malam')
                                    ->maxLength(255),
                            ]),
                        TextInput::make('accommodation')
                            ->label('Akomodasi / Hotel')
                            ->placeholder('Misal: Grand Hyatt Bali atau Setaraf')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('day_number', 'asc')
            ->columns([
                TextColumn::make('day_number')
                    ->label('Hari')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn ($state) => "Hari {$state}")
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('title')
                    ->label('Aktivitas')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('accommodation')
                    ->label('Hotel')
                    ->searchable()
                    ->placeholder('-'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Itinerary')
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
