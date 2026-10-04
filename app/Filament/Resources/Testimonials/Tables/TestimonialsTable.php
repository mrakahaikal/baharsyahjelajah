<?php

namespace App\Filament\Resources\Testimonials\Tables;

use App\Models\Testimonial;
use App\Models\Tour;
use App\Models\UmrahPackage;
use App\Models\Vehicle;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TestimonialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('product'))
            ->recordTitleAttribute('reviewer_name')
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular()
                    ->size(40),
                TextColumn::make('reviewer_name')
                    ->label('Nama Reviewer')
                    ->searchable()
                    ->description(fn (Testimonial $record): string => $record->reviewer_country ?? ''),
                TextColumn::make('product_type')
                    ->label('Tipe Produk')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        Tour::class => 'Tour',
                        Vehicle::class => 'Sewa Mobil',
                        UmrahPackage::class => 'Umrah',
                        default => $state ?? '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        Tour::class => 'success',
                        Vehicle::class => 'info',
                        UmrahPackage::class => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('product_id')
                    ->label('Produk')
                    ->state(fn (Testimonial $record): string => $record->product?->name ?? '-')
                    ->wrap(),
                TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state).str_repeat('☆', 5 - $state))
                    ->alignCenter(),
                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_type')
                    ->label('Tipe Produk')
                    ->options([
                        Tour::class => 'Tour Wisata',
                        Vehicle::class => 'Sewa Kendaraan',
                        UmrahPackage::class => 'Paket Umrah',
                    ])
                    ->native(false),
                SelectFilter::make('rating')
                    ->label('Rating')
                    ->options([
                        1 => '1 Bintang',
                        2 => '2 Bintang',
                        3 => '3 Bintang',
                        4 => '4 Bintang',
                        5 => '5 Bintang',
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
            ->emptyStateHeading('Belum Ada Testimoni')
            ->emptyStateDescription('Ulasan dari pelanggan belum terdaftar. Silakan tambahkan testimoni pertama Anda.')
            ->emptyStateIcon('lucide-message-square')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Tambah Testimoni')
                    ->icon('lucide-plus')
                    ->slideOver(),
            ]);
    }
}
