<?php

namespace App\Filament\Resources\UmrahPackages\RelationManagers;

use App\Enums\UmrahItemType;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class IncludesRelationManager extends RelationManager
{
    protected static string $relationship = 'includes';

    protected static ?string $title = 'Fasilitas & Persyaratan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kelola Fasilitas & Persyaratan')
                    ->description('Tentukan item fasilitas yang termasuk, tidak termasuk, persyaratan, atau catatan khusus untuk paket umrah ini.')
                    ->icon('lucide-clipboard-check')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('type')
                                ->label('Kategori Item')
                                ->options(UmrahItemType::class)
                                ->required()
                                ->default('include')
                                ->placeholder('Pilih kategori')
                                ->helperText('Kategori dari item (Termasuk, Tidak Termasuk, Persyaratan, atau Catatan).')
                                ->prefixIcon('lucide-help-circle')
                                ->native(false),
                            TextInput::make('sort_order')
                                ->label('Urutan Tampilan')
                                ->numeric()
                                ->default(0)
                                ->placeholder('Contoh: 0')
                                ->helperText('Nomor urutan tampilan pada halaman informasi paket.')
                                ->prefixIcon('lucide-sort-asc'),
                        ]),
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale) => [
                                TextInput::make('item')
                                    ->label('Nama Fasilitas / Item')
                                    ->placeholder('Masukkan nama item (contoh: Tiket Pesawat Pulang Pergi)')
                                    ->required($locale === 'id')
                                    ->maxLength(255)
                                    ->helperText('Rincian item fasilitas atau persyaratan.')
                                    ->prefixIcon('lucide-check-square'),
                            ])
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(1);
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
                    ->formatStateUsing(fn (string $state): string => UmrahItemType::tryFrom($state)?->getLabel() ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'include' => 'success',
                        'exclude' => 'danger',
                        'requirement' => 'warning',
                        'note' => 'info',
                        default => 'gray',
                    }),
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
                    ->label('Kategori')
                    ->options(UmrahItemType::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Fasilitas')
                    ->icon('lucide-plus'),
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
