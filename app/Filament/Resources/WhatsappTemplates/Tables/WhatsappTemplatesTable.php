<?php

namespace App\Filament\Resources\WhatsappTemplates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WhatsappTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_type')
            ->columns([
                TextColumn::make('product_type')
                    ->label('Tipe Produk')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'tour' => 'Tour Wisata',
                        'umrah' => 'Paket Umrah',
                        'vehicle' => 'Sewa Kendaraan',
                        'visa' => 'Layanan Visa',
                        default => $state ?? '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'tour' => 'success',
                        'umrah' => 'warning',
                        'vehicle' => 'info',
                        'visa' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('locale')
                    ->label('Bahasa')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'id' => 'Indonesia',
                        'en' => 'English',
                        'ms' => 'Melayu',
                        default => $state ?? '-',
                    }),
                TextColumn::make('template')
                    ->label('Isi Template')
                    ->limit(80)
                    ->wrap(),
                TextColumn::make('updated_at')
                    ->label('Tanggal Pembaruan')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_type')
                    ->label('Tipe Produk')
                    ->options([
                        'tour' => 'Tour Wisata',
                        'umrah' => 'Paket Umrah',
                        'vehicle' => 'Sewa Kendaraan',
                        'visa' => 'Layanan Visa',
                    ])
                    ->native(false),
                SelectFilter::make('locale')
                    ->label('Bahasa')
                    ->options([
                        'id' => 'Indonesia',
                        'en' => 'English',
                        'ms' => 'Melayu',
                    ])
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Ubah')
                    ->icon('lucide-pencil')
                    ->slideOver()
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
            ->emptyStateHeading('Belum Ada Template WhatsApp')
            ->emptyStateDescription('Templat pesan otomatis WhatsApp belum terdaftar. Silakan tambahkan templat pertama Anda.')
            ->emptyStateIcon('lucide-message-square')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Tambah Template')
                    ->icon('lucide-plus')
                    ->slideOver(),
            ]);
    }
}
