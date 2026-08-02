<?php

namespace App\Services;

use App\Models\Destination;
use App\Models\UmrahPackage;
use App\Models\UmrahPackagePrice;
use App\Models\UmrahPackagePrivatePrice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UmrahPackageImporter
{
    /**
     * Import data dari file CSV.
     *
     * @return array{success: int, failed: int, errors: array<string>}
     */
    public function import(string $filePath): array
    {
        if (! file_exists($filePath) || ! is_readable($filePath)) {
            return [
                'success' => 0,
                'failed' => 0,
                'errors' => ['File CSV tidak ditemukan atau tidak dapat dibaca.'],
            ];
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return [
                'success' => 0,
                'failed' => 0,
                'errors' => ['Gagal membuka file CSV.'],
            ];
        }

        // Baca header
        $rawHeader = fgetcsv($handle, 0, ',');
        if ($rawHeader === false) {
            fclose($handle);

            return [
                'success' => 0,
                'failed' => 0,
                'errors' => ['File CSV kosong atau tidak memiliki baris header.'],
            ];
        }

        // Deteksi pembatas alternatif jika kolom terdeteksi cuma 1 dan mengandung koma/titik-koma
        if (count($rawHeader) === 1 && str_contains($rawHeader[0], ';')) {
            rewind($handle);
            $rawHeader = fgetcsv($handle, 0, ';');
            $delimiter = ';';
        } else {
            $delimiter = ',';
        }

        // Hilangkan BOM UTF-8 pada header pertama jika ada
        if (isset($rawHeader[0])) {
            $rawHeader[0] = preg_replace('/^\xEF\xBB\xBF/', '', $rawHeader[0]);
        }

        $headerMap = $this->normalizeHeaders($rawHeader);

        $successCount = 0;
        $failedCount = 0;
        $errors = [];
        $rowNumber = 1; // Baris data pertama (setelah header)

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowNumber++;

                // Skip baris kosong
                if (empty($row) || (count($row) === 1 && is_null($row[0]))) {
                    continue;
                }

                $rowData = [];
                foreach ($headerMap as $index => $mappedKey) {
                    if (isset($row[$index])) {
                        $rowData[$mappedKey] = trim($row[$index]);
                    }
                }

                // Validasi data baris
                $validationErrors = $this->validateRow($rowData, $rowNumber);
                if (! empty($validationErrors)) {
                    $errors = array_merge($errors, $validationErrors);
                    $failedCount++;

                    continue;
                }

                // Persiapkan data model
                $nameId = $rowData['name_id'] ?? $rowData['name'] ?? null;
                $nameEn = $rowData['name_en'] ?? $rowData['name'] ?? $nameId;
                $nameMs = $rowData['name_ms'] ?? $rowData['name'] ?? $nameId;

                $slugId = $rowData['slug_id'] ?? $rowData['slug'] ?? Str::slug($nameId);
                $slugEn = $rowData['slug_en'] ?? $rowData['slug'] ?? Str::slug($nameEn);
                $slugMs = $rowData['slug_ms'] ?? $rowData['slug'] ?? Str::slug($nameMs);

                $descId = $rowData['description_id'] ?? $rowData['description'] ?? '';
                $descEn = $rowData['description_en'] ?? $rowData['description'] ?? $descId;
                $descMs = $rowData['description_ms'] ?? $rowData['description'] ?? $descId;

                $packageType = strtolower($rowData['package_type'] ?? 'regular');
                if ($packageType === 'umrah istimewa' || $packageType === 'istimewa' || $packageType === 'bespoke') {
                    $packageType = 'private';
                }

                // Simpan atau perbarui model
                $package = UmrahPackage::query()->updateOrCreate(
                    [
                        'slug->id' => $slugId,
                    ],
                    [
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
                        'duration_days' => $this->parseInteger($rowData['duration_days'] ?? null, 9),
                        'price_idr' => $this->parseInteger($rowData['price_idr'] ?? null, 0),
                        'airline' => $rowData['airline'] ?? null,
                        'hotel_makkah' => $rowData['hotel_makkah'] ?? null,
                        'hotel_makkah_stars' => $this->parseInteger($rowData['hotel_makkah_stars'] ?? null, null),
                        'hotel_madinah' => $rowData['hotel_madinah'] ?? null,
                        'hotel_madinah_stars' => $this->parseInteger($rowData['hotel_madinah_stars'] ?? null, null),
                        'visa_included' => $this->parseBoolean($rowData['visa_included'] ?? null, true),
                        'handling_included' => $this->parseBoolean($rowData['handling_included'] ?? null, true),
                        'is_active' => $this->parseBoolean($rowData['is_active'] ?? null, true),
                        'is_featured' => $this->parseBoolean($rowData['is_featured'] ?? null, false),
                    ]
                );

                // Sinkronisasi Destinasi
                $destinationsStr = $rowData['destinations'] ?? null;
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
                $roomPricesStr = $rowData['room_prices'] ?? null;
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
                $privatePricesStr = $rowData['private_prices'] ?? null;
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

                $successCount++;
            }

            fclose($handle);

            if (! empty($errors)) {
                // Ada baris yang gagal validasi, batalkan seluruh transaksi (rollback)
                DB::rollBack();

                return [
                    'success' => 0,
                    'failed' => $failedCount,
                    'errors' => $errors,
                ];
            }

            DB::commit();

            return [
                'success' => $successCount,
                'failed' => 0,
                'errors' => [],
            ];
        } catch (\Throwable $e) {
            fclose($handle);
            DB::rollBack();

            return [
                'success' => 0,
                'failed' => $failedCount + 1,
                'errors' => ["Terjadi kesalahan sistem pada baris {$rowNumber}: ".$e->getMessage()],
            ];
        }
    }

    /**
     * Normalisasi nama-nama kolom header agar mempermudah pemetaan index kolom.
     */
    private function normalizeHeaders(array $headers): array
    {
        $map = [];
        $headerTranslations = [
            'name_id' => ['name_id', 'name', 'nama', 'nama_id', 'nama paket'],
            'name_en' => ['name_en', 'nama_en'],
            'name_ms' => ['name_ms', 'nama_ms'],
            'slug_id' => ['slug_id', 'slug', 'slug_url'],
            'slug_en' => ['slug_en'],
            'slug_ms' => ['slug_ms'],
            'description_id' => ['description_id', 'description', 'deskripsi', 'deskripsi_id'],
            'description_en' => ['description_en', 'deskripsi_en'],
            'description_ms' => ['description_ms', 'deskripsi_ms'],
            'package_type' => ['package_type', 'type', 'tipe', 'tipe_paket', 'jenis'],
            'duration_days' => ['duration_days', 'duration', 'durasi', 'hari', 'days'],
            'price_idr' => ['price_idr', 'price', 'harga', 'harga_idr'],
            'airline' => ['airline', 'maskapai'],
            'hotel_makkah' => ['hotel_makkah', 'hotel makkah', 'makkah hotel'],
            'hotel_makkah_stars' => ['hotel_makkah_stars', 'makkah stars', 'bintang makkah'],
            'hotel_madinah' => ['hotel_madinah', 'hotel madinah', 'madinah hotel'],
            'hotel_madinah_stars' => ['hotel_madinah_stars', 'madinah stars', 'bintang madinah'],
            'visa_included' => ['visa_included', 'visa', 'termasuk visa'],
            'handling_included' => ['handling_included', 'handling', 'termasuk handling'],
            'is_active' => ['is_active', 'active', 'aktif'],
            'is_featured' => ['is_featured', 'featured', 'unggulan'],
            'destinations' => ['destinations', 'destinasi'],
            'room_prices' => ['room_prices', 'room prices', 'harga kamar', 'harga_kamar'],
            'private_prices' => ['private_prices', 'private prices', 'harga privat', 'harga_privat', 'harga_istimewa'],
        ];

        foreach ($headers as $index => $header) {
            $headerClean = strtolower(trim((string) $header));

            $matched = false;
            foreach ($headerTranslations as $key => $aliases) {
                if (in_array($headerClean, $aliases)) {
                    $map[$index] = $key;
                    $matched = true;
                    break;
                }
            }

            if (! $matched) {
                // Fallback menggunakan nama kolom asli jika tidak cocok dengan alias
                $map[$index] = str_replace(' ', '_', $headerClean);
            }
        }

        return $map;
    }

    /**
     * Validasi isi baris data.
     */
    private function validateRow(array $data, int $rowNumber): array
    {
        $errors = [];

        $name = $data['name_id'] ?? $data['name'] ?? null;
        if (empty($name)) {
            $errors[] = "Baris {$rowNumber}: Nama paket (name / name_id) wajib diisi.";
        }

        $packageType = strtolower($data['package_type'] ?? '');
        $validTypes = ['regular', 'plus', 'vip', 'ramadan', 'private', 'umrah istimewa', 'istimewa', 'bespoke'];
        if (! empty($packageType) && ! in_array($packageType, $validTypes)) {
            $errors[] = "Baris {$rowNumber}: Tipe paket '{$packageType}' tidak valid. Harus salah satu dari: regular, plus, vip, ramadan, istimewa.";
        }

        $duration = $data['duration_days'] ?? null;
        if (is_null($duration) || $duration === '') {
            $errors[] = "Baris {$rowNumber}: Durasi perjalanan wajib diisi.";
        } elseif ($this->parseInteger($duration) <= 0) {
            $errors[] = "Baris {$rowNumber}: Durasi perjalanan harus bernilai angka positif.";
        }

        $price = $data['price_idr'] ?? null;
        if (is_null($price) || $price === '') {
            $errors[] = "Baris {$rowNumber}: Harga wajib diisi.";
        } elseif ($this->parseInteger($price) < 0) {
            $errors[] = "Baris {$rowNumber}: Harga tidak boleh bernilai negatif.";
        }

        return $errors;
    }

    private function parseBoolean(mixed $value, bool $default = false): bool
    {
        if (is_null($value) || $value === '') {
            return $default;
        }

        $value = strtolower(trim((string) $value));

        return in_array($value, ['1', 'true', 'yes', 'y', 'aktif', 'ya']);
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
}
