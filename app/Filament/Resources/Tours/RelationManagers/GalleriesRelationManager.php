<?php

namespace App\Filament\Resources\Tours\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GalleriesRelationManager extends RelationManager
{
    protected static string $relationship = 'galleries';

    protected static ?string $title = 'Galeri Foto';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Foto')
                            ->image()
                            ->directory('tours/galleries')
                            ->required()
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('caption')
                                    ->label('Keterangan / Caption')
                                    ->placeholder('Misal: Pemandangan matahari terbit di Bromo')
                                    ->maxLength(255),
                                TextInput::make('sort_order')
                                    ->label('Urutan')
                                    ->placeholder('0')
                                    ->numeric()
                                    ->default(0),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('caption')
            ->defaultSort('sort_order', 'asc')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Foto')
                    ->square()
                    ->size(80),
                TextColumn::make('caption')
                    ->label('Keterangan')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Foto')
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
