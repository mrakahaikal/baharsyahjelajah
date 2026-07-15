<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Cover')
                    ->square()
                    ->size(50),
                TextColumn::make('title')
                    ->label('Judul Artikel')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'published' => 'Dipublikasikan',
                        'archived' => 'Diarsipkan',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'archived' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('published_at')
                    ->label('Tanggal Publikasi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Dipublikasikan',
                        'archived' => 'Diarsipkan',
                    ])
                    ->native(false),
                SelectFilter::make('post_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat')
                    ->icon('lucide-eye'),
                EditAction::make()
                    ->label('Ubah')
                    ->icon('lucide-pencil')
                    ->color('primary'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Artikel Blog')
            ->emptyStateDescription('Tulis artikel baru untuk mulai membagikan info atau tips perjalanan di blog.')
            ->emptyStateIcon('lucide-newspaper')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Tulis Artikel Baru')
                    ->icon('lucide-plus'),
            ]);
    }
}
