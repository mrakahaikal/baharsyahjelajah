<?php

namespace App\Filament\Resources\Faqs\Tables;

use App\Enums\FaqCategory;
use App\Enums\FaqContext;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FaqsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question')
            ->defaultSort('sort_order', 'asc')
            ->columns([
                TextColumn::make('question')
                    ->label('Pertanyaan')
                    ->searchable()
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge(),
                TextColumn::make('contexts')
                    ->label('Konteks Penayangan')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => FaqContext::tryFrom($state)?->getLabel() ?? $state),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(FaqCategory::class)
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Ubah')
                    ->icon('lucide-pencil')
                    ->color('primary'),
                DeleteAction::make()
                    ->label('Hapus')
                    ->icon('lucide-trash')
                    ->color('danger'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateHeading('Belum Ada FAQ')
            ->emptyStateDescription('Buat FAQ baru untuk memandu pengguna dengan pertanyaan yang sering diajukan.')
            ->emptyStateIcon('lucide-help-circle')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Tambah FAQ')
                    ->icon('lucide-plus'),
            ]);
    }
}
