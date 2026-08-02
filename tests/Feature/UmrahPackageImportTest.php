<?php

use App\Models\Destination;
use App\Models\UmrahPackage;
use App\Services\UmrahPackageImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('imports valid CSV data successfully and resolves destinations', function () {
    $destination = Destination::factory()->create([
        'name' => ['id' => 'Makkah', 'en' => 'Makkah', 'ms' => 'Makkah'],
        'slug' => 'makkah',
    ]);

    // 2. Prepare valid CSV content
    $csvContent = "name_id,name_en,name_ms,package_type,duration_days,price_idr,airline,hotel_makkah,hotel_makkah_stars,hotel_madinah,hotel_madinah_stars,visa_included,handling_included,destinations,is_active,is_featured,description_id,description_en,description_ms\n";
    $csvContent .= "\"Reguler Awal Musim\",\"Early Season Regular\",\"Reguler Awal Musim\",\"regular\",9,\"Rp 28.500.000\",\"Saudia\",\"Hilton\",5,\"Pullman\",5,1,1,\"Makkah\",1,0,\"Detail ID\",\"Detail EN\",\"Detail MS\"\n";
    $csvContent .= "\"Istimewa Akhir Musim\",\"Bespoke Late Season\",\"Istimewa Akhir Musim\",\"istimewa\",12,\"Rp 35.000.000\",\"Garuda\",\"Fairmont\",5,\"Oberoi\",5,true,true,\"Makkah\",true,true,\"Detail 2 ID\",\"Detail 2 EN\",\"Detail 2 MS\"\n";

    $tempFile = tempnam(sys_get_temp_dir(), 'csv_import_test');
    file_put_contents($tempFile, $csvContent);

    // 3. Call importer
    $importer = new UmrahPackageImporter;
    $result = $importer->import($tempFile);

    if (file_exists($tempFile)) {
        unlink($tempFile);
    }

    // 4. Assertions
    expect($result['success'])->toBe(2)
        ->and($result['failed'])->toBe(0)
        ->and($result['errors'])->toBeEmpty();

    $package1 = UmrahPackage::query()->where('slug->id', 'reguler-awal-musim')->first();
    expect($package1)->not->toBeNull()
        ->and($package1->getTranslation('name', 'id'))->toBe('Reguler Awal Musim')
        ->and($package1->getTranslation('name', 'en'))->toBe('Early Season Regular')
        ->and($package1->package_type)->toBe('regular')
        ->and($package1->duration_days)->toBe(9)
        ->and($package1->price_idr)->toBe(28500000)
        ->and($package1->airline)->toBe('Saudia')
        ->and($package1->hotel_makkah_stars)->toBe(5)
        ->and($package1->visa_included)->toBeTrue()
        ->and($package1->destinations->pluck('id'))->toContain($destination->id);

    $package2 = UmrahPackage::query()->where('slug->id', 'istimewa-akhir-musim')->first();
    expect($package2)->not->toBeNull()
        ->and($package2->package_type)->toBe('private') // 'istimewa' mapped to 'private'
        ->and($package2->is_featured)->toBeTrue();
});

it('rejects CSV with validation errors and rolls back database', function () {
    // Prepare invalid CSV content (missing price, invalid package type, negative duration)
    $csvContent = "name_id,package_type,duration_days,price_idr\n";
    $csvContent .= "\"Valid Paket\",\"regular\",9,28500000\n";
    $csvContent .= "\"Invalid Paket\",\"invalid_type\",-5,100000\n";

    $tempFile = tempnam(sys_get_temp_dir(), 'csv_import_test');
    file_put_contents($tempFile, $csvContent);

    $importer = new UmrahPackageImporter;
    $result = $importer->import($tempFile);

    if (file_exists($tempFile)) {
        unlink($tempFile);
    }

    expect($result['success'])->toBe(0)
        ->and($result['failed'])->toBe(1)
        ->and($result['errors'])->not->toBeEmpty();

    expect($result['errors'][0])->toContain('Tipe paket')
        ->and($result['errors'][1])->toContain('Durasi perjalanan harus bernilai angka positif');

    // Database should have 0 UmrahPackages because of transaction rollback
    expect(UmrahPackage::query()->count())->toBe(0);
});

it('allows downloading the CSV template file', function () {
    $response = get(route('admin.umrah-packages.download-template'));

    $response->assertSuccessful()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
        ->assertHeader('Content-Disposition', 'attachment; filename="template-import-umroh.csv"');

    expect($response->streamedContent())
        ->toContain('name_id,name_en,name_ms')
        ->toContain('Umrah Reguler Awal Musim');
});
