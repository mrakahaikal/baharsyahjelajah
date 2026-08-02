<?php

use App\Filament\Resources\UmrahPackages\Pages\ListUmrahPackages;
use App\Models\Destination;
use App\Models\UmrahPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['email' => 'admin@baharsyahjelajah.test']));
});

it('can import regular and private umrah packages from CSV using native ImportAction', function () {
    // 1. Create a destination
    $destination = Destination::factory()->create([
        'name' => ['id' => 'Makkah', 'en' => 'Makkah', 'ms' => 'Makkah'],
        'slug' => 'makkah',
    ]);

    // 2. Prepare test CSV
    $csvContent = "name_id,name_en,name_ms,package_type,duration_days,price_idr,airline,hotel_makkah,hotel_makkah_stars,hotel_madinah,hotel_madinah_stars,visa_included,handling_included,destinations,is_active,is_featured,room_prices,private_prices,description_id,description_en,description_ms\n";
    $csvContent .= "\"Reguler Awal Musim\",\"Early Season Regular\",\"Reguler Awal Musim\",\"regular\",9,\"Rp 28.500.000\",\"Saudia\",\"Hilton\",5,\"Pullman\",5,1,1,\"Makkah\",1,0,\"quad:28500000;triple:30000000;double:32000000\",\"\",\"Detail ID\",\"Detail EN\",\"Detail MS\"\n";
    $csvContent .= "\"Istimewa Akhir Musim\",\"Bespoke Late Season\",\"Istimewa Akhir Musim\",\"istimewa\",12,\"Rp 35.000.000\",\"Garuda\",\"Fairmont\",5,\"Oberoi\",5,true,true,\"Makkah\",true,true,\"\",\"6:4:14000000;6:6:13000000;9:4:16000000\",\"Detail 2 ID\",\"Detail 2 EN\",\"Detail 2 MS\"\n";

    $file = UploadedFile::fake()->createWithContent('umrah_packages.csv', $csvContent);

    // 3. Call ImportAction
    Livewire::test(ListUmrahPackages::class)
        ->callAction('import', [
            'file' => $file,
            'columnMap' => [
                'name_id' => 'name_id',
                'name_en' => 'name_en',
                'name_ms' => 'name_ms',
                'package_type' => 'package_type',
                'duration_days' => 'duration_days',
                'price_idr' => 'price_idr',
                'airline' => 'airline',
                'hotel_makkah' => 'hotel_makkah',
                'hotel_makkah_stars' => 'hotel_makkah_stars',
                'hotel_madinah' => 'hotel_madinah',
                'hotel_madinah_stars' => 'hotel_madinah_stars',
                'visa_included' => 'visa_included',
                'handling_included' => 'handling_included',
                'destinations' => 'destinations',
                'is_active' => 'is_active',
                'is_featured' => 'is_featured',
                'room_prices' => 'room_prices',
                'private_prices' => 'private_prices',
                'description_id' => 'description_id',
                'description_en' => 'description_en',
                'description_ms' => 'description_ms',
            ],
        ])
        ->assertHasNoActionErrors();

    // 4. Assertions - Package 1 (Regular)
    $package1 = UmrahPackage::query()->where('slug->id', 'reguler-awal-musim')->first();
    expect($package1)->not->toBeNull()
        ->and($package1->getTranslation('name', 'id'))->toBe('Reguler Awal Musim')
        ->and($package1->package_type)->toBe('regular')
        ->and($package1->duration_days)->toBe(9)
        ->and($package1->price_idr)->toBe(28500000)
        ->and($package1->prices()->count())->toBe(3)
        ->and($package1->prices()->where('room_type', 'quad')->value('price_idr'))->toBe(28500000)
        ->and($package1->prices()->where('room_type', 'double')->value('price_idr'))->toBe(32000000)
        ->and($package1->destinations->pluck('id'))->toContain($destination->id);

    // 5. Assertions - Package 2 (Istimewa / Private)
    $package2 = UmrahPackage::query()->where('slug->id', 'istimewa-akhir-musim')->first();
    expect($package2)->not->toBeNull()
        ->and($package2->package_type)->toBe('private')
        ->and($package2->privatePrices()->count())->toBe(3)
        ->and($package2->privatePrices()->where('duration_nights', 6)->where('pax', 4)->value('price_idr'))->toBe(14000000)
        ->and($package2->privatePrices()->where('duration_nights', 9)->where('pax', 4)->value('price_idr'))->toBe(16000000);
});

it('fails validation if required columns are missing or invalid', function () {
    $file = UploadedFile::fake()->createWithContent(
        'invalid_umrah.csv',
        "name_id,package_type,duration_days,price_idr\n".
        "\"\",\"regular\",-5,28500000\n"
    );

    Livewire::test(ListUmrahPackages::class)
        ->callAction('import', [
            'file' => $file,
            'columnMap' => [
                'name_id' => 'name_id',
                'package_type' => 'package_type',
                'duration_days' => 'duration_days',
                'price_idr' => 'price_idr',
            ],
        ]);

    expect(UmrahPackage::query()->count())->toBe(0);
});
