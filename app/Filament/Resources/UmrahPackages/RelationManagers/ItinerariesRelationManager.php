<?php

namespace App\Filament\Resources\UmrahPackages\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class ItinerariesRelationManager extends RelationManager
{
    protected static string $relationship = 'itineraries';

    protected static ?string $title = 'Itinerary';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kelola Agenda Harian (Itinerary)')
                    ->description('Atur agenda kegiatan harian, urutan hari, lokasi, dan penjelasan detail perjalanan umrah.')
                    ->icon('lucide-map')
                    ->schema([
                        TextInput::make('day_number')
                            ->label('Hari Ke-')
                            ->numeric()
                            ->minValue(1)
                            ->unique(
                                table: 'umrah_package_itineraries',
                                column: 'day_number',
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule): Unique => $rule->where(
                                    'umrah_package_id',
                                    $this->getOwnerRecord()->getKey(),
                                ),
                            )
                            ->required()
                            ->placeholder('Contoh: 1')
                            ->helperText('Nomor urutan hari agenda kegiatan.')
                            ->prefixIcon('lucide-hash'),
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale): array => [
                                TextInput::make('title')
                                    ->label('Judul Agenda Kegiatan')
                                    ->required($locale === 'id')
                                    ->maxLength(255)
                                    ->placeholder('Masukkan judul kegiatan (contoh: Ziarah Kota Madinah)')
                                    ->helperText('Nama agenda atau kegiatan utama hari ini.')
                                    ->prefixIcon('lucide-type'),
                                TextInput::make('location')
                                    ->label('Lokasi Kegiatan')
                                    ->maxLength(255)
                                    ->placeholder('Masukkan lokasi (contoh: Masjid Nabawi, Madinah)')
                                    ->helperText('Tempat pelaksanaan aktivitas hari ini.')
                                    ->prefixIcon('lucide-map-pin'),
                                RichEditor::make('description')
                                    ->label('Rincian Kegiatan')
                                    ->columnSpanFull()
                                    ->placeholder('Tuliskan jadwal lengkap kegiatan, waktu kumpul, dan rincian perjalanan hari ini...')
                                    ->helperText('Rincian detail kegiatan harian.'),
                            ])
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('day_number')
            ->columns([
                TextColumn::make('day_number')
                    ->label('Hari')
                    ->prefix('Hari ke-')
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Kegiatan')
                    ->searchable()
                    ->weight('bold')
                    ->wrap(),
                TextColumn::make('location')
                    ->label('Lokasi')
                    ->placeholder('-')
                    ->wrap(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Itinerary')
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
