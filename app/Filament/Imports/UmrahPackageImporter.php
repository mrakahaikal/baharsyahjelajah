<?php

namespace App\Filament\Imports;

use App\Models\Destination;
use App\Models\UmrahPackage;
use App\Models\UmrahPackagePrice;
use App\Models\UmrahPackagePrivatePrice;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class UmrahPackageImporter extends Importer
{
    protected static ?string $model = UmrahPackage::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name_id')
                ->label('Nama Paket (ID)')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('name_en')
                ->label('Nama Paket (EN)'),
            ImportColumn::make('name_ms')
                ->label('Nama Paket (MS)'),

            ImportColumn::make('slug_id')
                ->label('Slug URL (ID)'),
            ImportColumn::make('slug_en')
                ->label('Slug URL (EN)'),
            ImportColumn::make('slug_ms')
                ->label('Slug URL (MS)'),

            ImportColumn::make('description_id')
                ->label('Deskripsi (ID)'),
            ImportColumn::make('description_en')
                ->label('Deskripsi (EN)'),
            ImportColumn::make('description_ms')
                ->label('Deskripsi (MS)'),

            ImportColumn::make('package_type')
                ->label('Tipe Paket')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('duration_days')
                ->label('Durasi Perjalanan (Hari)')
                ->requiredMapping()
                ->castStateUsing(fn ($state) => self::cleanIntegerState($state))
                ->rules(['required', 'integer', 'min:1']),
            ImportColumn::make('price_idr')
                ->label('Harga Dasar Fallback (IDR)')
                ->requiredMapping()
                ->castStateUsing(fn ($state) => self::cleanIntegerState($state))
                ->rules(['required', 'integer', 'min:0']),

            ImportColumn::make('airline')
                ->label('Maskapai'),
            ImportColumn::make('hotel_makkah')
                ->label('Hotel Makkah'),
            ImportColumn::make('hotel_makkah_stars')
                ->label('Bintang Hotel Makkah')
                ->castStateUsing(fn ($state) => self::cleanIntegerState($state))
                ->rules(['nullable', 'integer', 'in:3,4,5']),
            ImportColumn::make('hotel_madinah')
                ->label('Hotel Madinah'),
            ImportColumn::make('hotel_madinah_stars')
                ->label('Bintang Hotel Madinah')
                ->castStateUsing(fn ($state) => self::cleanIntegerState($state))
                ->rules(['nullable', 'integer', 'in:3,4,5']),

            ImportColumn::make('visa_included')
                ->label('Termasuk Visa')
                ->boolean()
                ->castStateUsing(fn ($state) => self::cleanBooleanState($state))
                ->rules(['boolean']),
            ImportColumn::make('handling_included')
                ->label('Termasuk Handling')
                ->boolean()
                ->castStateUsing(fn ($state) => self::cleanBooleanState($state))
                ->rules(['boolean']),
            ImportColumn::make('is_active')
                ->label('Aktif')
                ->boolean()
                ->castStateUsing(fn ($state) => self::cleanBooleanState($state))
                ->rules(['boolean']),
            ImportColumn::make('is_featured')
                ->label('Unggulan')
                ->boolean()
                ->castStateUsing(fn ($state) => self::cleanBooleanState($state))
                ->rules(['boolean']),

            ImportColumn::make('destinations')
                ->label('Destinasi (Koma-terpisah)'),
            ImportColumn::make('room_prices')
                ->label('Daftar Harga Kamar (Tipe:Harga;...)'),
            ImportColumn::make('private_prices')
                ->label('Daftar Harga Istimewa (Malam:Pax:Harga;...)'),
        ];
    }

    public function resolveRecord(): ?UmrahPackage
    {
        $nameId = $this->data['name_id'] ?? null;
        if (empty($nameId)) {
            return null;
        }

        $nameEn = $this->data['name_en'] ?? $nameId;
        $nameMs = $this->data['name_ms'] ?? $nameId;

        $slugId = $this->data['slug_id'] ?? Str::slug($nameId);
        $slugEn = $this->data['slug_en'] ?? Str::slug($nameEn);
        $slugMs = $this->data['slug_ms'] ?? Str::slug($nameMs);

        $descId = $this->data['description_id'] ?? '';
        $descEn = $this->data['description_en'] ?? $descId;
        $descMs = $this->data['description_ms'] ?? $descId;

        $packageType = strtolower($this->data['package_type'] ?? 'regular');
        if (in_array($packageType, ['umrah istimewa', 'istimewa', 'bespoke', 'private'])) {
            $packageType = 'private';
        }

        $package = UmrahPackage::query()->where('slug->id', $slugId)->first() ?? new UmrahPackage;

        $package->fill([
            'name' => [
                'id' => $nameId,
                'en' => $nameEn,
                'ms' => $nameMs,
            ],
            'slug' => [
                'id' => $slugId,
                'en' => $slugEn,
                'ms' => $slugMs,
            ],
            'description' => [
                'id' => $descId,
                'en' => $descEn,
                'ms' => $descMs,
            ],
            'package_type' => $packageType,
            'duration_days' => (int) ($this->data['duration_days'] ?? 9),
            'price_idr' => $this->parseInteger($this->data['price_idr'] ?? 0),
            'airline' => $this->data['airline'] ?? null,
            'hotel_makkah' => $this->data['hotel_makkah'] ?? null,
            'hotel_makkah_stars' => isset($this->data['hotel_makkah_stars']) ? (int) $this->data['hotel_makkah_stars'] : null,
            'hotel_madinah' => $this->data['hotel_madinah'] ?? null,
            'hotel_madinah_stars' => isset($this->data['hotel_madinah_stars']) ? (int) $this->data['hotel_madinah_stars'] : null,
            'visa_included' => filter_var($this->data['visa_included'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'handling_included' => filter_var($this->data['handling_included'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'is_active' => filter_var($this->data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'is_featured' => filter_var($this->data['is_featured'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ]);

        return $package;
    }

    public function fillRecord(): void
    {
        // Override untuk mencegah mapping otomatis dari Filament yang bertabrakan dengan kolom translatable/virtual
    }

    protected function afterSave(): void
    {
        $package = $this->record;

        // Sinkronisasi Destinasi
        $destinationsStr = $this->data['destinations'] ?? null;
        if (! empty($destinationsStr)) {
            $destinationNames = array_map('trim', explode(',', $destinationsStr));
            $destinationIds = [];
            foreach ($destinationNames as $name) {
                $destination = Destination::query()
                    ->where('name->id', 'like', "%{$name}%")
                    ->orWhere('name->en', 'like', "%{$name}%")
                    ->orWhere('name->ms', 'like', "%{$name}%")
                    ->orWhere('slug->id', Str::slug($name))
                    ->first();

                if ($destination) {
                    $destinationIds[] = $destination->id;
                }
            }
            $package->destinations()->sync($destinationIds);
        }

        // Hapus harga lama (idempotent)
        $package->prices()->delete();
        $package->privatePrices()->delete();

        // Impor Room Prices (Untuk Paket Reguler/Plus/VIP)
        $roomPricesStr = $this->data['room_prices'] ?? null;
        if (! empty($roomPricesStr)) {
            $roomPrices = array_map('trim', explode(';', $roomPricesStr));
            foreach ($roomPrices as $item) {
                $parts = array_map('trim', explode(':', $item));
                if (count($parts) === 2) {
                    $roomType = strtolower($parts[0]);
                    $priceIdr = $this->parseInteger($parts[1]);
                    if ($roomType && $priceIdr > 0) {
                        UmrahPackagePrice::query()->create([
                            'umrah_package_id' => $package->id,
                            'room_type' => $roomType,
                            'price_idr' => $priceIdr,
                        ]);
                    }
                }
            }
        }

        // Impor Private Prices (Untuk Paket Istimewa)
        $privatePricesStr = $this->data['private_prices'] ?? null;
        if (! empty($privatePricesStr)) {
            $privatePrices = array_map('trim', explode(';', $privatePricesStr));
            foreach ($privatePrices as $item) {
                $parts = array_map('trim', explode(':', $item));
                if (count($parts) === 3) {
                    $durationNights = $this->parseInteger($parts[0]);
                    $pax = $this->parseInteger($parts[1]);
                    $priceIdr = $this->parseInteger($parts[2]);
                    if ($durationNights > 0 && $pax > 0 && $priceIdr > 0) {
                        UmrahPackagePrivatePrice::query()->create([
                            'umrah_package_id' => $package->id,
                            'duration_nights' => $durationNights,
                            'pax' => $pax,
                            'price_idr' => $priceIdr,
                        ]);
                    }
                }
            }
        }
    }

    private function parseInteger(mixed $value, ?int $default = 0): ?int
    {
        if (is_null($value) || $value === '') {
            return $default;
        }

        $isNegative = str_contains((string) $value, '-');
        $clean = preg_replace('/[^0-9]/', '', (string) $value);
        if ($clean === '') {
            return $default;
        }

        return $isNegative ? -((int) $clean) : (int) $clean;
    }

    public static function cleanIntegerState(mixed $state): ?int
    {
        if (is_null($state) || $state === '') {
            return null;
        }

        $isNegative = str_contains((string) $state, '-');
        $clean = preg_replace('/[^0-9]/', '', (string) $state);
        if ($clean === '') {
            return null;
        }

        return $isNegative ? -((int) $clean) : (int) $clean;
    }

    public static function cleanBooleanState(mixed $state): bool
    {
        if (is_null($state) || $state === '') {
            return false;
        }

        return filter_var($state, FILTER_VALIDATE_BOOLEAN);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your umrah package import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
