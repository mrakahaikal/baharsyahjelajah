<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class PostInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make('Detail Artikel')
                            ->description('Informasi utama, ringkasan, dan isi artikel blog.')
                            ->icon('lucide-newspaper')
                            ->schema([
                                ImageEntry::make('cover_image')
                                    ->label('Gambar Sampul (Cover)')
                                    ->placeholder('Tidak ada gambar cover')
                                    ->height(300)
                                    ->columnSpanFull(),
                                TextEntry::make('title')
                                    ->label('Judul Artikel')
                                    ->weight(FontWeight::Bold)
                                    ->size(TextSize::Large)
                                    ->columnSpanFull(),
                                TextEntry::make('slug')
                                    ->label('Slug URL')
                                    ->copyable()
                                    ->columnSpanFull(),
                                TextEntry::make('excerpt')
                                    ->label('Ringkasan')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                                TextEntry::make('content')
                                    ->label('Isi Konten')
                                    ->html()
                                    ->prose()
                                    ->columnSpanFull(),
                            ])
                            ->columns(1)
                            ->columnSpan(2),
                        Section::make('Metadata & Publikasi')
                            ->description('Status, kategori, penulis, dan riwayat pembaruan.')
                            ->icon('lucide-info')
                            ->schema([
                                TextEntry::make('category.name')
                                    ->label('Kategori')
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'published' => 'success',
                                        'draft' => 'warning',
                                        'archived' => 'gray',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'published' => 'Dipublikasikan',
                                        'draft' => 'Draft',
                                        'archived' => 'Diarsipkan',
                                        default => $state,
                                    }),
                                TextEntry::make('author.name')
                                    ->label('Penulis')
                                    ->placeholder('Sistem'),
                                TextEntry::make('published_at')
                                    ->label('Tanggal Publikasi')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('Belum dipublikasikan'),
                                TextEntry::make('created_at')
                                    ->label('Tanggal Pembuatan')
                                    ->dateTime('d M Y H:i'),
                                TextEntry::make('updated_at')
                                    ->label('Pembaruan Terakhir')
                                    ->dateTime('d M Y H:i'),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
