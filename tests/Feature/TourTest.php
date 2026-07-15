<?php

use App\Enums\TourType;
use App\Models\CurrencyRate;
use App\Models\Destination;
use App\Models\Tour;
use App\Models\TourCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

function createTourCategory(): TourCategory
{
    return TourCategory::create([
        'name' => ['en' => 'Nature Tour', 'id' => 'Wisata Alam', 'ms' => 'Pelancongan Alam'],
        'slug' => ['en' => 'nature-tour', 'id' => 'wisata-alam', 'ms' => 'pelancongan-alam'],
        'icon' => 'heroicon-o-sparkles',
        'sort_order' => 1,
    ]);
}

function createTourWithPackage(TourCategory $category, array $tourAttributes = [], array $packageAttributes = []): Tour
{
    $tour = Tour::create(array_replace_recursive([
        'tour_category_id' => $category->id,
        'name' => ['en' => 'River Forest Escape', 'id' => 'Jelajah Sungai Hutan', 'ms' => 'Jelajah Sungai Hutan'],
        'slug' => ['en' => 'river-forest-escape', 'id' => 'jelajah-sungai-hutan', 'ms' => 'jelajah-sungai-hutan'],
        'short_description' => ['en' => 'A calm nature trip.', 'id' => 'Perjalanan alam yang tenang.', 'ms' => 'Perjalanan alam yang tenang.'],
        'description' => ['en' => '<p>Explore the forest.</p>', 'id' => '<p>Jelajahi kawasan hutan.</p>', 'ms' => '<p>Jelajahi kawasan hutan.</p>'],
        'tour_type' => TourType::Domestic,
        'currency' => 'IDR',
        'is_active' => true,
        'is_featured' => false,
    ], $tourAttributes));

    $package = $tour->packages()->create(array_replace_recursive([
        'name' => ['en' => 'Three-Day Package', 'id' => 'Paket Tiga Hari', 'ms' => 'Pakej Tiga Hari'],
        'slug' => ['en' => 'three-day-package', 'id' => 'paket-tiga-hari', 'ms' => 'pakej-tiga-hari'],
        'duration_days' => 3,
        'duration_nights' => 2,
    ], $packageAttributes));

    $tier = $package->tiers()->create([
        'name' => ['en' => 'Standard', 'id' => 'Standar', 'ms' => 'Standard'],
        'hotel_stars' => 3,
    ]);

    $tier->priceTiers()->create([
        'min_pax' => 1,
        'max_pax' => null,
        'price' => $tourAttributes['package_price'] ?? 1500000,
        'currency' => $tourAttributes['package_currency'] ?? 'IDR',
    ]);

    return $tour;
}

it('renders the tour catalog and hides inactive tours', function () {
    $category = createTourCategory();
    createTourWithPackage($category);

    $inactiveTour = createTourWithPackage($category, [
        'name' => ['en' => 'Hidden Tour', 'id' => 'Tour Tersembunyi', 'ms' => 'Tour Tersembunyi'],
        'slug' => ['en' => 'hidden-tour', 'id' => 'tour-tersembunyi', 'ms' => 'tour-tersembunyi'],
        'is_active' => false,
    ], [
        'slug' => ['en' => 'hidden-package', 'id' => 'paket-tersembunyi', 'ms' => 'pakej-tersembunyi'],
    ]);

    get('/id/tour')
        ->assertSuccessful()
        ->assertSee('Jelajah Sungai Hutan')
        ->assertSee('Perjalanan alam yang tenang.')
        ->assertSee('Pilihan paket')
        ->assertSee('1 paket')
        ->assertSee('3 Hari 2 Malam')
        ->assertSee('Lihat detail')
        ->assertDontSee('Tour Tersembunyi');

    get('/en/tour')
        ->assertSuccessful()
        ->assertSee('River Forest Escape')
        ->assertSee('A calm nature trip.')
        ->assertSee('Package options')
        ->assertSee('1 package')
        ->assertSee('View details');

    get('/ms/tour')
        ->assertSuccessful()
        ->assertSee('Jelajah Sungai Hutan')
        ->assertSee('Pilihan pakej')
        ->assertSee('1 pakej')
        ->assertSee('Lihat lawatan');

    get('/id/tour?destination=Sungai&type=domestic')
        ->assertSuccessful()
        ->assertSee('Jelajah Sungai Hutan')
        ->assertDontSee('Tour Tersembunyi');

    get(route('tour.package.show', [
        'locale' => 'id',
        'tour' => $inactiveTour->slug,
        'package' => $inactiveTour->packages->first()->slug,
    ]))->assertNotFound();
});

it('renders a tour overview with explicit package options', function () {
    $category = createTourCategory();
    $tour = createTourWithPackage($category, [
        'name' => ['en' => 'Amazing Jungle', 'id' => 'Hutan Menakjubkan', 'ms' => 'Hutan Menakjubkan'],
        'slug' => ['en' => 'amazing-jungle', 'id' => 'hutan-menakjubkan', 'ms' => 'hutan-menakjubkan'],
        'short_description' => ['en' => 'Explore the jungle.', 'id' => 'Jelajahi hutan.', 'ms' => 'Jelajahi hutan.'],
        'is_featured' => true,
    ], [
        'name' => ['en' => 'Jungle Explorer Package', 'id' => 'Paket Penjelajah Hutan', 'ms' => 'Pakej Penjelajah Hutan'],
        'slug' => ['en' => 'jungle-explorer-package', 'id' => 'paket-penjelajah-hutan', 'ms' => 'pakej-penjelajah-hutan'],
    ]);

    get('/id/tour/hutan-menakjubkan')
        ->assertSuccessful()
        ->assertSee('Hutan Menakjubkan')
        ->assertSee('Jelajahi hutan.')
        ->assertSee('Paket Penjelajah Hutan')
        ->assertSee('Paket untuk perjalanan ini')
        ->assertSee('1 paket')
        ->assertSee('3 Hari 2 Malam')
        ->assertSee('0 hari itinerary')
        ->assertSee('1 pilihan tier')
        ->assertSee('Lihat detail paket')
        ->assertSee('Rp 1.500.000')
        ->assertSee('<link rel="canonical" href="'.route('tour.show', ['locale' => 'id', 'tour' => 'hutan-menakjubkan']).'">', false)
        ->assertSee('<link rel="alternate" hreflang="en" href="'.route('tour.show', ['locale' => 'en', 'tour' => 'amazing-jungle']).'">', false)
        ->assertSee(route('tour.package.show', [
            'locale' => 'id',
            'tour' => $tour->slug,
            'package' => 'paket-penjelajah-hutan',
        ]))
        ->assertDontSee('difficulty')
        ->assertDontSee('max_pax');

    get('/en/tour/amazing-jungle')
        ->assertSuccessful()
        ->assertSee('Amazing Jungle')
        ->assertSee('Jungle Explorer Package')
        ->assertSee('The package for this journey')
        ->assertSee('0 itinerary days')
        ->assertSee('View package details');

    get('/ms/tour/hutan-menakjubkan')
        ->assertSuccessful()
        ->assertSee('Pakej untuk perjalanan ini')
        ->assertSee('Lihat butiran pakej');

    get('/en/tour/hutan-menakjubkan')
        ->assertRedirect(route('tour.show', ['locale' => 'en', 'tour' => 'amazing-jungle']))
        ->assertStatus(301);
});

it('adapts the tour overview for multiple packages and missing package pricing', function () {
    Storage::fake('public');

    $category = createTourCategory();
    $tour = createTourWithPackage($category, [
        'name' => ['en' => 'Flexible Jungle', 'id' => 'Jelajah Hutan Fleksibel', 'ms' => 'Jelajah Hutan Fleksibel'],
        'slug' => ['en' => 'flexible-jungle', 'id' => 'jelajah-hutan-fleksibel', 'ms' => 'jelajah-hutan-fleksibel'],
    ]);
    $firstPackage = $tour->packages->sole();
    $firstItinerary = $firstPackage->itineraries()->create([
        'day_number' => 1,
        'title' => ['en' => 'River Arrival', 'id' => 'Tiba di Sungai', 'ms' => 'Tiba di Sungai'],
    ]);

    $secondPackage = $tour->packages()->create([
        'name' => ['en' => 'Five-Day Explorer', 'id' => 'Penjelajah Lima Hari', 'ms' => 'Penjelajah Lima Hari'],
        'slug' => ['en' => 'five-day-explorer', 'id' => 'penjelajah-lima-hari', 'ms' => 'penjelajah-lima-hari'],
        'duration_days' => 5,
        'duration_nights' => 4,
    ]);
    $secondItinerary = $secondPackage->itineraries()->create([
        'day_number' => 1,
        'title' => ['en' => 'Forest Arrival', 'id' => 'Tiba di Hutan', 'ms' => 'Tiba di Hutan'],
        'description' => ['en' => 'Meet the guide.', 'id' => 'Bertemu pemandu.', 'ms' => 'Bertemu pemandu.'],
    ]);
    $sharedDestination = Destination::create([
        'name' => ['en' => 'Shared River', 'id' => 'Sungai Bersama', 'ms' => 'Sungai Bersama'],
        'slug' => 'sungai-bersama',
    ]);
    $sharedDestination->addMedia(UploadedFile::fake()->image('shared-first.jpg', 720, 540))
        ->toMediaCollection(Destination::MEDIA_COLLECTION_GALLERY);
    $sharedDestination->addMedia(UploadedFile::fake()->image('shared-second.jpg', 720, 540))
        ->toMediaCollection(Destination::MEDIA_COLLECTION_GALLERY);
    $firstItinerary->destinations()->attach($sharedDestination);
    $secondItinerary->destinations()->attach($sharedDestination);

    $uniqueDestination = Destination::create([
        'name' => ['en' => 'Unique Village', 'id' => 'Desa Unik', 'ms' => 'Desa Unik'],
        'slug' => 'desa-unik',
    ]);
    $secondItinerary->destinations()->attach($uniqueDestination);

    $packageOnlyDestination = Destination::create([
        'name' => ['en' => 'Package Only Destination', 'id' => 'Destinasi Paket Saja', 'ms' => 'Destinasi Pakej Sahaja'],
        'slug' => 'destinasi-paket-saja',
    ]);
    $firstPackage->destinations()->attach($packageOnlyDestination);
    $secondPackage->includes()->createMany([
        ['type' => 'include', 'sort_order' => 1, 'item' => ['en' => 'Guide', 'id' => 'Pemandu', 'ms' => 'Pemandu']],
        ['type' => 'include', 'sort_order' => 2, 'item' => ['en' => 'Meals', 'id' => 'Makan', 'ms' => 'Makanan']],
        ['type' => 'include', 'sort_order' => 3, 'item' => ['en' => 'Transport', 'id' => 'Transportasi', 'ms' => 'Pengangkutan']],
        ['type' => 'include', 'sort_order' => 4, 'item' => ['en' => 'Entrance fees', 'id' => 'Tiket masuk', 'ms' => 'Tiket masuk']],
    ]);

    $response = get('/id/tour/jelajah-hutan-fleksibel')
        ->assertSuccessful()
        ->assertSee('Tempat yang akan dikunjungi')
        ->assertSee('Sungai Bersama')
        ->assertSee('Desa Unik')
        ->assertDontSee('Destinasi Paket Saja')
        ->assertSee($sharedDestination->getFirstMediaUrl(Destination::MEDIA_COLLECTION_GALLERY), false)
        ->assertDontSee($sharedDestination->getMedia(Destination::MEDIA_COLLECTION_GALLERY)->last()->getUrl(), false)
        ->assertSee('data-lightbox-variant="compact"', false)
        ->assertSee('Bandingkan 2 pilihan paket')
        ->assertSee('3-5 hari')
        ->assertSee('Penjelajah Lima Hari')
        ->assertSee('1 hari itinerary')
        ->assertSee('Pemandu')
        ->assertSee('+1 fasilitas lainnya')
        ->assertSee('Harga tersedia melalui konsultasi');

    expect(substr_count(
        $response->getContent(),
        'data-tour-destination-id="'.$sharedDestination->id.'"',
    ))->toBe(1);
});

it('offers a custom trip when a tour has no fixed packages', function () {
    $category = createTourCategory();
    Tour::create([
        'tour_category_id' => $category->id,
        'name' => ['en' => 'Custom Forest Journey', 'id' => 'Perjalanan Hutan Custom', 'ms' => 'Perjalanan Hutan Khas'],
        'slug' => ['en' => 'custom-forest-journey', 'id' => 'perjalanan-hutan-custom', 'ms' => 'perjalanan-hutan-khas'],
        'short_description' => ['en' => 'Designed for your group.', 'id' => 'Dirancang untuk rombongan Anda.', 'ms' => 'Direka untuk kumpulan anda.'],
        'description' => ['en' => 'A flexible journey.', 'id' => 'Perjalanan yang fleksibel.', 'ms' => 'Perjalanan yang fleksibel.'],
        'tour_type' => TourType::Domestic,
        'currency' => 'IDR',
        'is_active' => true,
        'is_featured' => false,
    ]);

    get('/id/tour/perjalanan-hutan-custom')
        ->assertSuccessful()
        ->assertSee('Belum ada paket tetap')
        ->assertSee('Konsultasi custom trip')
        ->assertSee(route('contact.index', [
            'locale' => 'id',
            'tour' => 'perjalanan-hutan-custom',
        ]));
});

it('renders a scoped package detail with itinerary, inclusions, and pax pricing', function () {
    Storage::fake('public');

    $category = createTourCategory();
    $tour = createTourWithPackage($category, [
        'name' => ['en' => 'Borneo Adventure', 'id' => 'Petualangan Borneo', 'ms' => 'Pengembaraan Borneo'],
        'slug' => ['en' => 'borneo-adventure', 'id' => 'petualangan-borneo', 'ms' => 'pengembaraan-borneo'],
    ], [
        'name' => ['en' => 'River Package', 'id' => 'Paket Sungai', 'ms' => 'Pakej Sungai'],
        'slug' => ['en' => 'river-package', 'id' => 'paket-sungai', 'ms' => 'pakej-sungai'],
    ]);
    $package = $tour->packages->first();
    $itinerary = $package->itineraries()->create([
        'day_number' => 1,
        'title' => ['en' => 'River Journey', 'id' => 'Perjalanan Sungai', 'ms' => 'Perjalanan Sungai'],
        'description' => ['en' => '<p>Board the boat.</p>', 'id' => '<p>Naik perahu.</p>', 'ms' => '<p>Naik bot.</p>'],
    ]);
    $destination = Destination::create([
        'name' => ['en' => 'Sekonyer River', 'id' => 'Sungai Sekonyer', 'ms' => 'Sungai Sekonyer'],
        'slug' => 'sungai-sekonyer',
    ]);
    $destination->addMedia(UploadedFile::fake()->image('sekonyer-first.jpg', 720, 540))
        ->toMediaCollection(Destination::MEDIA_COLLECTION_GALLERY);
    $destination->addMedia(UploadedFile::fake()->image('sekonyer-second.jpg', 720, 540))
        ->toMediaCollection(Destination::MEDIA_COLLECTION_GALLERY);
    $itinerary->destinations()->attach($destination);
    $destinationImageUrl = $destination->getFirstMediaUrl(Destination::MEDIA_COLLECTION_GALLERY);
    $unusedDestinationImageUrl = $destination->getMedia(Destination::MEDIA_COLLECTION_GALLERY)->last()->getUrl();

    $destinationWithoutMedia = Destination::create([
        'name' => ['en' => 'Camp Leakey', 'id' => 'Camp Leakey', 'ms' => 'Camp Leakey'],
        'slug' => 'camp-leakey',
    ]);
    $itinerary->destinations()->attach($destinationWithoutMedia);
    $package->includes()->createMany([
        ['type' => 'include', 'sort_order' => 1, 'item' => ['en' => 'Boat', 'id' => 'Perahu', 'ms' => 'Bot']],
        ['type' => 'exclude', 'sort_order' => 2, 'item' => ['en' => 'Flights', 'id' => 'Tiket Pesawat', 'ms' => 'Tiket Penerbangan']],
        ['type' => 'note', 'sort_order' => 3, 'item' => ['en' => 'Weather dependent', 'id' => 'Menyesuaikan cuaca', 'ms' => 'Mengikut cuaca']],
    ]);
    $premiumTier = $package->tiers()->create([
        'name' => ['en' => 'Premium', 'id' => 'Premium', 'ms' => 'Premium'],
        'hotel_stars' => 4,
    ]);
    $premiumTier->priceTiers()->createMany([
        ['min_pax' => 1, 'max_pax' => 3, 'price' => 2200000, 'currency' => 'IDR'],
        ['min_pax' => 4, 'max_pax' => null, 'price' => 1800000, 'currency' => 'IDR'],
    ]);

    get(route('tour.package.show', [
        'locale' => 'id',
        'tour' => 'petualangan-borneo',
        'package' => 'paket-sungai',
        'tier' => $premiumTier->id,
        'pax' => 4,
    ]))
        ->assertSuccessful()
        ->assertSee('Paket Sungai')
        ->assertSee('3 Hari 2 Malam')
        ->assertSee('Perjalanan Sungai')
        ->assertSee('Sungai Sekonyer')
        ->assertSee('Camp Leakey')
        ->assertSee($destinationImageUrl, false)
        ->assertDontSee($unusedDestinationImageUrl, false)
        ->assertSee('data-destination-card', false)
        ->assertSee('data-lightbox-variant="compact"', false)
        ->assertSee('@click="open(0)"', false)
        ->assertSee('grid max-w-2xl grid-cols-2 gap-3 sm:grid-cols-3', false)
        ->assertSee('bg-linear-to-t from-slate-950/90', false)
        ->assertSee('Perahu')
        ->assertSee('Tiket Pesawat')
        ->assertSee('Menyesuaikan cuaca')
        ->assertSee('Premium')
        ->assertSee('Rp 1.800.000')
        ->assertSee('application/ld+json')
        ->assertSee('AggregateOffer')
        ->assertSee(route('tour.package.booking', [
            'locale' => 'id',
            'tour' => 'petualangan-borneo',
            'package' => 'paket-sungai',
        ]))
        ->assertDontSee('method="GET"', false)
        ->assertSee('<link rel="canonical" href="'.route('tour.package.show', [
            'locale' => 'id',
            'tour' => 'petualangan-borneo',
            'package' => 'paket-sungai',
        ]).'">', false)
        ->assertSee('<link rel="alternate" hreflang="en" href="'.route('tour.package.show', [
            'locale' => 'en',
            'tour' => 'borneo-adventure',
            'package' => 'river-package',
        ]).'">', false);

    get('/en/tour/borneo-adventure/package/river-package')
        ->assertSuccessful()
        ->assertSee('River Package')
        ->assertSee('River Journey')
        ->assertSee('Sekonyer River')
        ->assertSee('Camp Leakey');

    get('/en/tour/petualangan-borneo/package/paket-sungai')
        ->assertRedirect(route('tour.package.show', [
            'locale' => 'en',
            'tour' => 'borneo-adventure',
            'package' => 'river-package',
        ]))
        ->assertStatus(301);

    $otherTour = createTourWithPackage($category, [
        'name' => ['id' => 'Tour Lain'],
        'slug' => ['id' => 'tour-lain'],
    ], [
        'slug' => ['id' => 'paket-lain'],
    ]);
    $foreignTier = $otherTour->packages->first()->tiers->first();

    get('/id/tour/petualangan-borneo/package/paket-lain')->assertNotFound();
    get('/id/tour/petualangan-borneo/package/paket-sungai?tier='.$foreignTier->id.'&pax=2')
        ->assertSuccessful()
        ->assertSee('Sesuaikan perjalanan Anda')
        ->assertSee('Ajukan booking');
});

it('uses Indonesian slugs when localized package slugs are missing', function () {
    $tour = createTourWithPackage(createTourCategory());
    $package = $tour->packages->first();

    $tour->forgetTranslations('slug')
        ->setTranslation('slug', 'id', 'wisata-jakarta-bandung')
        ->save();
    $package->forgetTranslations('slug')
        ->setTranslation('slug', 'id', 'jakarta-city-escape')
        ->save();

    get('/id/tour/wisata-jakarta-bandung/package/jakarta-city-escape')
        ->assertSuccessful()
        ->assertSee('<link rel="alternate" hreflang="ms" href="'.route('tour.package.show', [
            'locale' => 'ms',
            'tour' => 'wisata-jakarta-bandung',
            'package' => 'jakarta-city-escape',
        ]).'">', false)
        ->assertSee('<link rel="alternate" hreflang="en" href="'.route('tour.package.show', [
            'locale' => 'en',
            'tour' => 'wisata-jakarta-bandung',
            'package' => 'jakarta-city-escape',
        ]).'">', false);
});

it('converts the starting package price using the selected currency', function () {
    CurrencyRate::updateRate('USD', 0.00006250);
    CurrencyRate::updateRate('MYR', 0.00029200);

    createTourWithPackage(createTourCategory(), [
        'name' => ['en' => 'USD Tour', 'id' => 'Tour USD', 'ms' => 'Tour USD'],
        'slug' => ['en' => 'usd-tour', 'id' => 'tour-usd', 'ms' => 'tour-usd'],
        'currency' => 'USD',
        'package_price' => 100,
        'package_currency' => 'USD',
    ], [
        'slug' => ['en' => 'usd-package', 'id' => 'paket-usd', 'ms' => 'pakej-usd'],
    ]);

    get('/id/tour/tour-usd')
        ->assertSuccessful()
        ->assertSee('Rp 1.600.000');

    $this->withSession(['app_currency' => 'MYR'])
        ->get('/id/tour/tour-usd')
        ->assertSuccessful()
        ->assertSee('RM 467.20');

    $this->withSession(['app_currency' => 'USD'])
        ->get('/id/tour/tour-usd')
        ->assertSuccessful()
        ->assertSee('$ 100.00');
});

it('renders featured tours and localized header data without legacy package copy', function () {
    $category = createTourCategory();
    createTourWithPackage($category, [
        'name' => ['en' => 'Featured Borneo Tour', 'id' => 'Tour Borneo Unggulan', 'ms' => 'Lawatan Borneo Pilihan'],
        'slug' => ['en' => 'featured-borneo-tour', 'id' => 'tour-borneo-unggulan', 'ms' => 'lawatan-borneo-pilihan'],
        'is_featured' => true,
    ]);
    createTourWithPackage($category, [
        'name' => ['en' => 'Hidden Featured Tour', 'id' => 'Tour Unggulan Tersembunyi', 'ms' => 'Lawatan Pilihan Tersembunyi'],
        'slug' => ['en' => 'hidden-featured-tour', 'id' => 'tour-unggulan-tersembunyi', 'ms' => 'lawatan-pilihan-tersembunyi'],
        'is_featured' => true,
        'is_active' => false,
    ], [
        'slug' => ['en' => 'hidden-featured-package', 'id' => 'paket-unggulan-tersembunyi', 'ms' => 'pakej-pilihan-tersembunyi'],
    ]);

    get('/id')
        ->assertSuccessful()
        ->assertSee('Tour Borneo Unggulan')
        ->assertSee('1 pilihan paket')
        ->assertSee('1 tour aktif')
        ->assertSee('Lihat semua tour')
        ->assertSee('<option value="international">', false)
        ->assertDontSee('<option value="outbound">', false)
        ->assertDontSee('Tour Unggulan Tersembunyi')
        ->assertDontSee('Jelajahi Paket Tour');

    get('/en')
        ->assertSuccessful()
        ->assertSee('Featured Borneo Tour')
        ->assertSee('Featured Tours')
        ->assertDontSee('frontend.header.tour-mega-menu');

    get('/ms')
        ->assertSuccessful()
        ->assertSee('Lawatan Borneo Pilihan')
        ->assertSee('Lawatan Pilihan')
        ->assertDontSee('frontend.header.tour-mega-menu');
});
