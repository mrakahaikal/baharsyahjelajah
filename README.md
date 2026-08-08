# 🌴 BAHARSYAH JELAJAH

> **Platform Terpadu Layanan Perjalanan Wisata, Ibadah Umrah, Rental Armada Transportasi, Pengurusan Visa Resmi, dan Panduan Destinasi Global.**

---

## 📌 1. Keterangan Projek (Project Overview)

**Baharsyah Jelajah** (`PT Baharsyah Jelajah Untuk Semua`) adalah aplikasi web modern berbasis **Laravel 13** dan **Filament v5** yang dirancang untuk memberikan pengalaman pencarian, pemesanan, dan eksplorasi perjalanan wisata serta ibadah secara transparan, fleksibel, dan terpercaya.

### 🌟 Fitur Utama Website Publik
- **🌍 Destinasi Negara & Bento Grid**: Halaman eksplorasi negara destinasi dengan statistik agregasi layanan (Tur, Umrah, Armada, Tempat Wisata, Visa), lengkap dengan metadata ibu kota, mata uang, bahasa resmi, dan waktu terbaik berkunjung.
- **✈️ Katalog Tur & Paket Wisata (Multi-Tier)**: Pencarian tur domestik & internasional dengan opsi tier akomodasi (Hotel 3★, 4★, 5★), rincian pax minimum/maksimum, itinerary harian interaktif, dan kalkulator estimasi biaya tur.
- **🕋 Paket Ibadah Umrah**: Pengelolaan paket umrah reguler, VIP, dan private beserta jadwal keberangkatan (*Umrah Departures*), sisa kuota, rincian harga kamar (*Quad, Triple, Double*), serta fasilitas pendukung.
- **🚌 Katalog Sewa Armada Transportasi**: Sewa kendaraan (Bus Executive, Coaster, MPV, VIP Car) dengan skema tarif regional per area (`VehicleRentalArea`), durasi sewa, tarif *overtime*, serta syarat & ketentuan sewa.
- **📄 Pengurusan Dokumen Visa**: Katalog persyaratan dan pengurusan visa resmi per negara lokasi.
- **📍 Tempat Wisata & Itinerary**: Katalog objek wisata populer yang terhubung dengan itinerary paket tur dan lokasi negara.
- **🎨 Hero Banner Carousel Interaktif**: Banner promosi di halaman utama berbasis **Alpine.js** dengan fitur *autoplay 6 detik*, *pause-on-hover*, navigasi titik/panah, dan dukungan *touch swipe* mobile.
- **🌐 Dukungan Multi-Bahasa (ID, EN, MS)**: Mendukung Bahasa Indonesia (`id`), English (`en`), dan Bahasa Melayu (`ms`) dengan penanganan slug terjemahan otomatis (*SEO-Friendly*).

### 🛠️ Fitur Utama Admin Panel (Filament v5)
- **Resource Management**: Pengelolaan penuh Tur, Paket Tur, Paket Umrah, Armada Kendaraan, Negara Destinasi, Tempat Wisata, Layanan Visa, Artikel Blog, Testimoni, FAQ, Banner, Template WhatsApp, dan Kurs Mata Uang (`CurrencyRates`).
- **Filament Bulk Actions (Tindakan Massal)**:
  - Bulk attach **Negara Destinasi** ke Paket Tur, Paket Umrah, Armada Kendaraan, dan Tempat Wisata.
  - Bulk attach **Armada Kendaraan** ke Paket Tur dan Paket Umrah.
- **Import/Export Data**: Fitur import data paket umrah dari file CSV/Excel secara otomatis.
- **Media Management**: Pengelolaan foto cover & galeri interaktif dengan Spatie MediaLibrary.

---

## 🛠️ 2. Teknologi & Stack (Tech Stack)

| Kategori | Teknologi | Deskripsi |
|---|---|---|
| **Core Framework** | **PHP 8.4** & **Laravel 13** | Backend framework utama |
| **Admin Panel** | **Filament v5** | Panel administrasi berbasis Livewire & Alpine.js |
| **Frontend UI** | **Livewire v4**, **Alpine.js**, **Blade** | Komponen UI reaktif & responsif |
| **Styling** | **TailwindCSS v4** | Utility-first CSS framework |
| **Icons** | **Lucide Icons** | Ikon modern untuk UI publik & admin |
| **Database** | **MySQL / SQLite** | Penyimpanan data relasional & polymorphic |
| **Media Library** | **Spatie MediaLibrary v11** | Manajemen berkas & gambar |
| **Localization** | **Spatie Translatable v6** | Penerjemahan kolom database multi-bahasa |
| **Settings** | **Spatie Laravel Settings v3** | Pengaturan aplikasi terpusat |
| **Testing** | **Pest PHP v4** / **PHPUnit v12** | Automated unit & feature testing |
| **Formatter** | **Laravel Pint v1** | Pengatur standar format kode PHP |

---

## 📐 3. Arsitektur Aplikasi (Architecture & Design Patterns)

Aplikasi dibangun menggunakan prinsip **Laravel Best Practices**:

```
baharsyahjelajah/
├── app/
│   ├── Enums/                 # Enum status, tipe paket, placement banner, dsb.
│   ├── Filament/              # Filament Resources, Pages, Tables, Schemas, & Clusters
│   ├── Http/
│   │   ├── Controllers/       # Controller publik (Home, Tour, Umrah, Vehicle, Country, dll.)
│   │   └── Middleware/        # Middleware lokalisasi & penanganan request
│   ├── Livewire/              # Komponen Livewire publik (Filter, Calculator, Search Panel)
│   ├── Models/                # Eloquent Models & Concerns (HasLocalizedSlug)
│   └── Settings/              # General & Business Settings Class
├── database/
│   ├── factories/             # Model Factories untuk Testing
│   ├── migrations/            # Skema tabel & tabel pivot morfologi
│   └── seeders/               # Data awal / dummy seeder
├── lang/                      # File kamus multi-bahasa (id, en, ms)
├── resources/
│   └── views/
│       ├── components/        # Blade Component (UI Cards, Buttons, Headers, Footers)
│       ├── livewire/          # Livewire Blade Views
│       └── pages/             # Halaman publik (Home, Tour, Umrah, Country, dll.)
├── routes/
│   ├── web.php                # Route publik ber-prefix /{locale}/
│   └── console.php            # Schedule & command konsol
└── tests/                     # Automated Pest Feature & Unit Tests
```

### 🔁 Arsitektur Relasi Polymorphic (Tabel Pivot Morfologi)
Aplikasi memanfaatkan tabel pivot morfologi Laravel untuk fleksibilitas keterkaitan entitas:

1. **`countryables`** (`country_id`, `countryable_id`, `countryable_type`):
   - Menghubungkan model `Country` secara banyak-ke-banyak ke `Tour`, `TourPackage`, `UmrahPackage`, `Vehicle`, `Destination`, dan `Post`.
2. **`vehicleables`** (`vehicle_id`, `vehicleable_id`, `vehicleable_type`):
   - Menghubungkan model `Vehicle` secara banyak-ke-banyak ke `TourPackage` dan `UmrahPackage`.
3. **`destinationables`** (`destination_id`, `destinationable_id`, `destinationable_type`):
   - Menghubungkan model `Destination` secara banyak-ke-banyak ke `TourPackage`, `TourPackageItinerary`, `UmrahPackage`, dan `Post`.

---

## 🗄️ 4. Struktur Database & ERD (Database Schema & ERD)

### 📊 Entity Relationship Diagram (ERD)

```mermaid
erdiagram
    countries ||--o{ countryables : "morphs to"
    tours ||--o{ tour_packages : "has many"
    tours ||--o{ countryables : "polymorphic"
    tour_categories ||--o{ tours : "belongs to"
    
    tour_packages ||--o{ package_tiers : "has many"
    package_tiers ||--o{ tour_price_tiers : "has many"
    tour_packages ||--o{ tour_package_includes : "has many"
    tour_packages ||--o{ tour_package_itineraries : "has many"
    tour_packages ||--o{ vehicleables : "polymorphic"
    tour_packages ||--o{ countryables : "polymorphic"
    
    umrah_packages ||--o{ umrah_departures : "has many"
    umrah_packages ||--o{ umrah_includes : "has many"
    umrah_packages ||--o{ umrah_package_prices : "has many"
    umrah_packages ||--o{ umrah_package_itineraries : "has many"
    umrah_packages ||--o{ vehicleables : "polymorphic"
    umrah_packages ||--o{ countryables : "polymorphic"
    
    vehicles ||--o{ vehicleables : "morphed by"
    vehicles ||--o{ vehicle_rental_rates : "has many"
    vehicles ||--o{ countryables : "polymorphic"
    vehicle_rental_areas ||--o{ vehicle_rental_rates : "belongs to"
    
    destinations ||--o{ destinationables : "morphed by"
    destinations ||--o{ countryables : "polymorphic"
    
    countries ||--o{ visa_services : "has many"
    visa_services ||--o{ visa_service_items : "has many"
    
    post_categories ||--o{ posts : "has many"
    posts ||--o{ countryables : "polymorphic"
```

### 📋 Ringkasan Tabel Utama Database

| Tabel | Deskripsi |
|---|---|
| `users` | Akun pengguna & administrator sistem Filament |
| `countries` | Negara destinasi (nama, iso, flag, deskripsi, metadata travel) |
| `tours` & `tour_categories` | Kategori tur & wadah utama induk tur wisata |
| `tour_packages` | Paket detail tur (nama, durasi, harga mulai) |
| `package_tiers` & `tour_price_tiers` | Tiers akomodasi hotel (3★, 4★, 5★) & rincian tarif per pax |
| `tour_package_itineraries` | Rencana perjalanan harian paket tur |
| `umrah_packages` & `umrah_departures` | Paket umrah & jadwal tanggal keberangkatan/kuota |
| `umrah_package_prices` | Tarif spesifik kamar umrah (Quad, Triple, Double) |
| `vehicles` | Katalog armada kendaraan (jenis, kapasitas pax, transmisi) |
| `vehicle_rental_areas` & `rates` | Area cakupan sewa & skema tarif kendaraan harian/overtime |
| `destinations` | Objek / tempat menarik wisata |
| `visa_services` & `visa_service_items` | Layanan pengurusan visa resmi per negara & rincian item |
| `posts` & `post_categories` | Artikel blog & kategori panduan wisata |
| `banners` | Banner promosi lokasi (Home Hero, Home Promo, dll.) |
| `countryables` | Pivot morfologi relasi Negara ke berbagai entitas |
| `vehicleables` | Pivot morfologi relasi Armada ke Paket Tur & Umrah |
| `destinationables` | Pivot morfologi relasi Tempat Wisata ke Paket & Itinerary |

---

## ⚡ 5. Panduan Instalasi & Pengoperasian (Setup Guide)

### 🚀 1. Prasyarat Sistem
- PHP >= 8.4 dengan ekstensi `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `gd`/`imagick`
- Composer >= 2.x
- Node.js >= 20.x & NPM

### 🛠️ 2. Langkah Instalasi

1. **Clone repository**:
   ```bash
   git clone https://github.com/mrakahaikal/baharsyahjelajah.git
   cd baharsyahjelajah
   ```

2. **Install Dependensi PHP & Node**:
   ```bash
   composer install
   npm install
   ```

3. **Salin Environment File & Generate Key**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi Database** di `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=baharsyahjelajah
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Jalankan Migrasi & Database Seeder**:
   ```bash
   php artisan migrate --seed
   ```

6. **Buat Storage Link**:
   ```bash
   php artisan storage:link
   ```

7. **Jalankan Server Pengembang**:
   - Backend & Frontend Dev Server:
     ```bash
     composer run dev
     # Atau jalankan terpisah: php artisan serve & npm run dev
     ```

---

## 🧪 6. Pengujian Otomatis (Testing)

Proyek ini menggunakan **Pest PHP** dengan tingkat cakupan pengujian yang tinggi (230+ unit & feature tests):

- **Menjalankan Seluruh Pest Test Suite**:
  ```bash
  php -d memory_limit=512M vendor/bin/pest --compact
  ```

- **Menjalankan Test File Spesifik**:
  ```bash
  php -d memory_limit=512M vendor/bin/pest tests/Feature/HomeExperienceTest.php --compact
  ```

- **Menjalankan Laravel Pint Formatter**:
  ```bash
  vendor/bin/pint --format agent
  ```

---

## 📄 7. Lisensi

Hak Cipta © 2026 **PT Baharsyah Jelajah Untuk Semua**. Seluruh hak cipta dilindungi undang-undang.