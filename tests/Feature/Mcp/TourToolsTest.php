<?php

use App\Enums\TourType;
use App\Mcp\Prompts\ConsultTourPackagePrompt;
use App\Mcp\Prompts\DraftTourQuotationPrompt;
use App\Mcp\Resources\TourCatalogResource;
use App\Mcp\Servers\BaharsyahServer;
use App\Mcp\Tools\Tours\CreateTourTool;
use App\Mcp\Tools\Tours\DeleteTourTool;
use App\Mcp\Tools\Tours\GetTourTool;
use App\Mcp\Tools\Tours\ListToursTool;
use App\Mcp\Tools\Tours\UpdateTourTool;
use App\Models\Country;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\TourPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Mcp\Server\Transport\FakeTransporter;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-static-agent-token-tours');

    $this->category = TourCategory::create([
        'name' => ['id' => 'Eksplorasi Nusantara', 'en' => 'Archipelago Exploration'],
        'slug' => ['id' => 'eksplorasi-nusantara', 'en' => 'archipelago-exploration'],
        'sort_order' => 1,
    ]);

    $this->countryIndonesia = Country::create([
        'name' => ['id' => 'Indonesia', 'en' => 'Indonesia'],
        'slug' => 'indonesia',
        'code' => 'ID',
        'iso_alpha_2' => 'ID',
        'iso_alpha_3' => 'IDN',
        'currency_code' => 'IDR',
        'is_active' => true,
    ]);

    $this->countryTurkey = Country::create([
        'name' => ['id' => 'Turki', 'en' => 'Turkey'],
        'slug' => 'turki',
        'code' => 'TR',
        'iso_alpha_2' => 'TR',
        'iso_alpha_3' => 'TUR',
        'currency_code' => 'USD',
        'is_active' => true,
    ]);

    $this->tourDomestic = Tour::create([
        'tour_category_id' => $this->category->id,
        'name' => ['id' => 'Pesona Kepulauan Derawan', 'en' => 'Derawan Islands Charm'],
        'slug' => ['id' => 'pesona-kepulauan-derawan', 'en' => 'derawan-islands-charm'],
        'short_description' => ['id' => 'Nikmati keindahan bawah laut Derawan, Maratua, dan Kakaban.'],
        'description' => ['id' => '<p>Perjalanan wisata bahari terbaik di Kalimantan Timur.</p>'],
        'tour_type' => TourType::Domestic,
        'currency' => 'IDR',
        'is_active' => true,
        'is_featured' => true,
    ]);
    $this->tourDomestic->countries()->sync([$this->countryIndonesia->id]);
});

test('tour tools, catalog resource, and prompts are registered in baharsyah server', function () {
    $server = app(BaharsyahServer::class, ['transport' => new FakeTransporter]);
    $reflection = new ReflectionClass($server);

    $tools = $reflection->getProperty('tools')->getValue($server);
    expect($tools)->toContain(
        ListToursTool::class,
        GetTourTool::class,
        CreateTourTool::class,
        UpdateTourTool::class,
        DeleteTourTool::class,
    );

    $resources = $reflection->getProperty('resources')->getValue($server);
    expect($resources)->toContain(TourCatalogResource::class);

    $prompts = $reflection->getProperty('prompts')->getValue($server);
    expect($prompts)->toContain(
        ConsultTourPackagePrompt::class,
        DraftTourQuotationPrompt::class,
    );
});

test('list tours filters by category, type, country, status, and search query', function () {
    $intlTour = Tour::create([
        'tour_category_id' => $this->category->id,
        'name' => ['id' => 'Jelajah Cappadocia Turki'],
        'slug' => ['id' => 'jelajah-cappadocia-turki'],
        'short_description' => ['id' => 'Balon udara di Cappadocia'],
        'description' => ['id' => '<p>Liburan musim dingin di Turki</p>'],
        'tour_type' => TourType::International,
        'currency' => 'USD',
        'is_active' => false,
        'is_featured' => false,
    ]);
    $intlTour->countries()->sync([$this->countryTurkey->id]);

    // Filter by tour_type
    BaharsyahServer::tool(ListToursTool::class, ['tour_type' => 'domestic'])
        ->assertSee('Pesona Kepulauan Derawan')
        ->assertDontSee('Jelajah Cappadocia Turki');

    // Filter by country_id
    BaharsyahServer::tool(ListToursTool::class, ['country_id' => $this->countryTurkey->id])
        ->assertSee('Jelajah Cappadocia Turki')
        ->assertDontSee('Pesona Kepulauan Derawan');

    // Filter by is_active
    BaharsyahServer::tool(ListToursTool::class, ['is_active' => true])
        ->assertSee('Pesona Kepulauan Derawan')
        ->assertDontSee('Jelajah Cappadocia Turki');

    // Filter by search
    BaharsyahServer::tool(ListToursTool::class, ['search' => 'Kakaban'])
        ->assertSee('Pesona Kepulauan Derawan');
});

test('get tour returns complete details with packages and countries', function () {
    TourPackage::create([
        'tour_id' => $this->tourDomestic->id,
        'name' => ['id' => 'Paket Reguler 4H3M'],
        'slug' => ['id' => 'paket-reguler-4h3m'],
        'duration_days' => 4,
        'duration_nights' => 3,
    ]);

    BaharsyahServer::tool(GetTourTool::class, ['slug' => 'pesona-kepulauan-derawan'])
        ->assertSee('Pesona Kepulauan Derawan')
        ->assertSee('Paket Reguler 4H3M')
        ->assertSee('Indonesia');
});

test('create and update tour store semantic html description and sync countries', function () {
    $createResponse = BaharsyahServer::tool(CreateTourTool::class, [
        'tour_category_id' => $this->category->id,
        'name' => [
            'id' => 'Eksplorasi IKN Nusantara & Balikpapan',
            'en' => 'IKN Nusantara & Balikpapan Exploration',
        ],
        'short_description' => 'Kunjungan edukasi dan wisata ke Ibu Kota Nusantara.',
        'description' => "## Highlight Perjalanan\n\nMengunjungi Titik Nol IKN dan Kebun Raya Balikpapan.",
        'tour_type' => 'domestic',
        'currency' => 'IDR',
        'country_ids' => [$this->countryIndonesia->id],
        'is_featured' => true,
    ]);

    $createResponse->assertSee('Tur berhasil dibuat.')
        ->assertSee('eksplorasi-ikn-nusantara-balikpapan')
        ->assertSee('<h2>Highlight Perjalanan</h2>');

    $createdTour = Tour::whereLocalizedSlug('eksplorasi-ikn-nusantara-balikpapan')->firstOrFail();
    expect($createdTour->countries->pluck('id')->all())->toBe([$this->countryIndonesia->id]);

    BaharsyahServer::tool(UpdateTourTool::class, [
        'id' => $createdTour->id,
        'name' => ['id' => 'Tur Eksklusif IKN Nusantara'],
        'tour_type' => 'international',
        'country_ids' => [$this->countryIndonesia->id, $this->countryTurkey->id],
    ])->assertSee('Tur berhasil diperbarui.')
        ->assertSee('Tur Eksklusif IKN Nusantara');

    expect($createdTour->fresh()->tour_type)->toBe(TourType::International)
        ->and($createdTour->fresh()->countries)->toHaveCount(2);
});

test('delete tour cascades cleanly through packages, tiers, itineraries, and includes', function () {
    $pkg = TourPackage::create([
        'tour_id' => $this->tourDomestic->id,
        'name' => ['id' => 'Paket VIP Derawan'],
        'slug' => ['id' => 'paket-vip-derawan'],
        'duration_days' => 3,
        'duration_nights' => 2,
    ]);

    $tier = $pkg->tiers()->create([
        'name' => ['id' => 'Gold Resort'],
        'hotel_stars' => 4,
    ]);
    $tier->priceTiers()->create([
        'min_pax' => 2,
        'max_pax' => 6,
        'price' => 3500000,
        'currency' => 'IDR',
    ]);

    $pkg->itineraries()->create([
        'day_number' => 1,
        'title' => ['id' => 'Kedatangan di Berau'],
        'description' => ['id' => '<p>Transfer speedboat ke pulau.</p>'],
    ]);

    $pkg->includes()->create([
        'type' => 'include',
        'item' => ['id' => 'Speedboat PP'],
        'sort_order' => 1,
    ]);

    BaharsyahServer::tool(DeleteTourTool::class, ['id' => $this->tourDomestic->id])
        ->assertSee('Tur beserta seluruh paket terkait berhasil dihapus.');

    $this->assertDatabaseMissing('tours', ['id' => $this->tourDomestic->id]);
    $this->assertDatabaseMissing('tour_packages', ['id' => $pkg->id]);
    $this->assertDatabaseMissing('package_tiers', ['id' => $tier->id]);
});

test('tour catalog resource and prompts render properly', function () {
    $pkg = TourPackage::create([
        'tour_id' => $this->tourDomestic->id,
        'name' => ['id' => 'Paket Derawan 4H3M'],
        'slug' => ['id' => 'paket-derawan-4h3m'],
        'duration_days' => 4,
        'duration_nights' => 3,
    ]);

    $tier = $pkg->tiers()->create([
        'name' => ['id' => 'Silver Cottage'],
        'hotel_stars' => 3,
    ]);
    $tier->priceTiers()->create([
        'min_pax' => 2,
        'max_pax' => 10,
        'price' => 2750000,
        'currency' => 'IDR',
    ]);

    BaharsyahServer::resource(TourCatalogResource::class)
        ->assertSee('tours://catalog')
        ->assertSee('Pesona Kepulauan Derawan')
        ->assertSee('Paket Derawan 4H3M');

    BaharsyahServer::prompt(ConsultTourPackagePrompt::class, [
        'destination' => 'Derawan',
        'pax' => '4',
    ])->assertSee('Senior Travel Consultant')
        ->assertSee('Pesona Kepulauan Derawan');

    BaharsyahServer::prompt(DraftTourQuotationPrompt::class, [
        'tour_package_id' => (string) $pkg->id,
        'customer_name' => 'PT Borneo Mandiri',
        'pax' => '4',
    ])->assertSee('PT Borneo Mandiri')
        ->assertSee('Paket Derawan 4H3M')
        ->assertSee('Silver Cottage');
});
