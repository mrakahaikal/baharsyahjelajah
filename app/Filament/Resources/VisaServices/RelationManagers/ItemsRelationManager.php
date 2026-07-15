<?php

namespace App\Filament\Resources\VisaServices\RelationManagers;

use App\Enums\VisaItemType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Persyaratan & Ketentuan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Item')
                    ->description('Kelola berkas persyaratan, ketentuan, cakupan layanan, dan catatan penting.')
                    ->icon('lucide-clipboard-check')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('type')
                                ->label('Kategori')
                                ->options(VisaItemType::class)
                                ->default(VisaItemType::Requirement->value)
                                ->live()
                                ->afterStateUpdated(fn (Set $set, ?string $state): mixed => $set('is_mandatory', $state === VisaItemType::Requirement->value))
                                ->native(false)
                                ->required(),
                            Toggle::make('is_mandatory')
                                ->label('Berkas Wajib')
                                ->default(true)
                                ->visible(fn (Get $get): bool => $get('type') === VisaItemType::Requirement->value),
                            TextInput::make('sort_order')
                                ->label('Urutan')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),
                        ]),
                        Translate::make()
                            ->locales(['id', 'en', 'ms'])
                            ->schema(fn (string $locale): array => [
                                TextInput::make('content')
                                    ->label('Nama Item')
                                    ->required($locale === 'id')
                                    ->maxLength(255),
                                Textarea::make('details')
                                    ->label('Keterangan Tambahan')
                                    ->rows(3)
                                    ->maxLength(1000),
                            ])
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('type')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => ($state instanceof VisaItemType ? $state : VisaItemType::tryFrom((string) $state))?->getLabel() ?? '-'),
                TextColumn::make('content')
                    ->label('Item')
                    ->searchable()
                    ->weight('bold')
                    ->wrap(),
                TextColumn::make('details')
                    ->label('Keterangan')
                    ->placeholder('-')
                    ->limit(60)
                    ->toggleable(),
                IconColumn::make('is_mandatory')
                    ->label('Wajib')
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Kategori')
                    ->options(VisaItemType::class)
                    ->native(false),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Item')->icon('lucide-plus'),
            ])
            ->recordActions([
                EditAction::make()->label('Ubah'),
                DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Persyaratan atau Ketentuan')
            ->emptyStateDescription('Tambahkan dokumen yang dibutuhkan dan ketentuan layanan Visa.')
            ->emptyStateIcon('lucide-clipboard-check');
    }
}
