<?php

namespace App\Filament\Resources\WhatsappTemplates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WhatsappTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pengaturan Template')
                    ->description('Kelola tipe produk, bahasa, isi pesan template WhatsApp, beserta variabel dinamis.')
                    ->icon('lucide-message-square')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('product_type')
                                ->label('Tipe Produk')
                                ->options([
                                    'tour' => 'Tour Wisata',
                                    'umrah' => 'Paket Umrah',
                                    'vehicle' => 'Sewa Kendaraan',
                                    'visa' => 'Layanan Visa',
                                ])
                                ->placeholder('Pilih tipe produk')
                                ->prefixIcon('lucide-package')
                                ->native(false)
                                ->required(),
                            Select::make('locale')
                                ->label('Bahasa')
                                ->options([
                                    'id' => 'Indonesia',
                                    'en' => 'English',
                                    'ms' => 'Melayu',
                                ])
                                ->placeholder('Pilih bahasa')
                                ->prefixIcon('lucide-globe')
                                ->native(false)
                                ->required(),
                        ]),
                        Textarea::make('template')
                            ->label('Isi Template Pesan')
                            ->placeholder('Halo, saya ingin memesan {product_name} untuk {pax} orang...')
                            ->helperText('Gunakan placeholder yang sesuai, seperti {product_name}, {country}, {pax}, {price}, atau {date}.')
                            ->rows(8)
                            ->required()
                            ->columnSpanFull(),
                        TagsInput::make('variables')
                            ->label('Daftar Variabel Penampung')
                            ->placeholder('Tambah variabel baru lalu tekan enter...')
                            ->helperText('Tambahkan semua variabel yang digunakan dalam template, misal: {product_name}')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
