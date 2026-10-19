<?php

use App\Enums\BannerCtaType;
use App\Enums\BannerPlacement;
use App\Mcp\Prompts\DesignBannerCampaignPrompt;
use App\Mcp\Resources\ActiveBannersResource;
use App\Mcp\Servers\BaharsyahServer;
use App\Mcp\Tools\Banners\CreateBannerTool;
use App\Mcp\Tools\Banners\DeleteBannerTool;
use App\Mcp\Tools\Banners\GetBannerTool;
use App\Mcp\Tools\Banners\ListBannersTool;
use App\Mcp\Tools\Banners\UpdateBannerTool;
use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Mcp\Server\Transport\FakeTransporter;
use Laravel\Passport\ClientRepository;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-static-agent-token-banner');

    $clientRepository = app(ClientRepository::class);
    $clientRepository->createPersonalAccessGrantClient('Test Personal Client', 'users');

    $this->heroBanner = Banner::create([
        'title' => [
            'id' => 'Jelajahi Keindahan Dunia Bersama Baharsyah',
            'en' => 'Explore the Beauty of the World with Baharsyah',
        ],
        'subtitle' => [
            'id' => 'Paket Umrah & Wisata Halal terpercaya dengan fasilitas bintang lima.',
            'en' => 'Trusted Umrah & Halal Tour packages with five-star amenities.',
        ],
        'placement' => BannerPlacement::HomeHero,
        'cta_type' => BannerCtaType::Route,
        'cta_value' => 'tour.index',
        'cta_label' => ['id' => 'Lihat Paket Tur', 'en' => 'View Tour Packages'],
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $this->promoBanner = Banner::create([
        'title' => [
            'id' => 'Promo Early Bird Umrah Musim Dingin',
            'en' => 'Winter Umrah Early Bird Promotion',
        ],
        'subtitle' => [
            'id' => 'Diskon khusus pendaftaran hingga akhir bulan ini.',
            'en' => 'Special discount for registrations until the end of this month.',
        ],
        'placement' => BannerPlacement::HomePromo,
        'cta_type' => BannerCtaType::Whatsapp,
        'cta_value' => '6281234567890',
        'cta_label' => ['id' => 'Hubungi via WhatsApp', 'en' => 'Chat on WhatsApp'],
        'sort_order' => 2,
        'is_active' => true,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addMonth(),
    ]);
});

test('banner tools, resource, and prompt are registered in baharsyah server', function () {
    $server = app(BaharsyahServer::class, ['transport' => new FakeTransporter]);
    $reflection = new ReflectionClass($server);

    $tools = $reflection->getProperty('tools')->getValue($server);
    expect($tools)->toContain(
        ListBannersTool::class,
        GetBannerTool::class,
        CreateBannerTool::class,
        UpdateBannerTool::class,
        DeleteBannerTool::class,
    );

    $resources = $reflection->getProperty('resources')->getValue($server);
    expect($resources)->toContain(
        ActiveBannersResource::class,
    );

    $prompts = $reflection->getProperty('prompts')->getValue($server);
    expect($prompts)->toContain(
        DesignBannerCampaignPrompt::class,
    );
});

test('list banners returns filtered and live scheduled banners', function () {
    Banner::create([
        'title' => ['id' => 'Banner Kedaluwarsa'],
        'placement' => BannerPlacement::HomePromo,
        'is_active' => true,
        'starts_at' => now()->subMonths(2),
        'ends_at' => now()->subMonth(),
    ]);

    // 1. Filter placement
    BaharsyahServer::tool(ListBannersTool::class, ['placement' => 'home_hero'])
        ->assertSee('Jelajahi Keindahan Dunia')
        ->assertDontSee('Promo Early Bird Umrah');

    // 2. Filter currently running
    BaharsyahServer::tool(ListBannersTool::class, ['currently_running' => true])
        ->assertSee('Promo Early Bird Umrah')
        ->assertDontSee('Banner Kedaluwarsa');

    // 3. Search keyword
    BaharsyahServer::tool(ListBannersTool::class, ['search' => 'Early Bird'])
        ->assertSee('Promo Early Bird Umrah');
});

test('get banner returns complete details with cta and scheduling', function () {
    BaharsyahServer::tool(GetBannerTool::class, ['id' => $this->heroBanner->id, 'locale' => 'id'])
        ->assertSee('Jelajahi Keindahan Dunia')
        ->assertSee('tour.index')
        ->assertSee('home_hero');

    BaharsyahServer::tool(GetBannerTool::class, ['id' => 99999])
        ->assertSee('Banner with ID 99999 not found.');
});

test('create banner stores multilingual text placement and cta', function () {
    $response = BaharsyahServer::tool(CreateBannerTool::class, [
        'title' => [
            'id' => 'Sewa Mobil Mewah Jeddah & Makkah',
            'en' => 'Luxury Car Rental Jeddah & Makkah',
        ],
        'subtitle' => 'Layanan armada Alphard dan HiAce dengan driver berpengalaman.',
        'placement' => 'home_promo',
        'cta_type' => 'route',
        'cta_value' => 'transport.index',
        'cta_label' => 'Sewa Sekarang',
        'sort_order' => 3,
        'starts_at' => now()->startOfDay()->toIso8601String(),
        'ends_at' => now()->addDays(30)->endOfDay()->toIso8601String(),
    ]);

    $response->assertSee('Banner berhasil dibuat.')
        ->assertSee('Sewa Mobil Mewah');

    $this->assertDatabaseHas('banners', [
        'placement' => 'home_promo',
        'cta_value' => 'transport.index',
    ]);
});

test('update banner modifies fields and toggles schedule', function () {
    $response = BaharsyahServer::tool(UpdateBannerTool::class, [
        'id' => $this->promoBanner->id,
        'title' => [
            'id' => 'Promo Umrah Ramadhan 1448 H',
        ],
        'placement' => 'home_hero',
        'sort_order' => 10,
    ]);

    $response->assertSee('Banner berhasil diperbarui.');

    expect($this->promoBanner->fresh()->sort_order)->toBe(10)
        ->and($this->promoBanner->fresh()->placement)->toBe(BannerPlacement::HomeHero)
        ->and($this->promoBanner->fresh()->getTranslation('title', 'id'))->toBe('Promo Umrah Ramadhan 1448 H');
});

test('delete banner removes record', function () {
    $response = BaharsyahServer::tool(DeleteBannerTool::class, [
        'id' => $this->promoBanner->id,
    ]);

    $response->assertSee('Banner berhasil dihapus.');
    $this->assertDatabaseMissing('banners', ['id' => $this->promoBanner->id]);
});

test('banner tools enforce granular oauth scopes over http', function () {
    $adminUser = User::factory()->create();
    $readToken = $adminUser->createToken('Read Token', ['mcp:read'])->accessToken;
    $writeToken = $adminUser->createToken('Write Token', ['mcp:write'])->accessToken;
    $adminToken = $adminUser->createToken('Admin Token', ['mcp:admin'])->accessToken;

    // 1. list_banners allowed with mcp:read
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => [
            'name' => 'list_banners',
            'arguments' => [],
        ],
    ], [
        'Authorization' => "Bearer {$readToken}",
    ])->assertOk();

    // 2. create_banner forbidden with mcp:read, allowed with mcp:write
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/call',
        'params' => [
            'name' => 'create_banner',
            'arguments' => [
                'title' => 'Judul Uji Scope',
            ],
        ],
    ], [
        'Authorization' => "Bearer {$readToken}",
    ])->assertStatus(403);

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'tools/call',
        'params' => [
            'name' => 'create_banner',
            'arguments' => [
                'title' => 'Judul Uji Scope',
            ],
        ],
    ], [
        'Authorization' => "Bearer {$writeToken}",
    ])->assertOk();

    // 3. delete_banner forbidden with mcp:write, allowed with mcp:admin
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 4,
        'method' => 'tools/call',
        'params' => [
            'name' => 'delete_banner',
            'arguments' => ['id' => $this->heroBanner->id],
        ],
    ], [
        'Authorization' => "Bearer {$writeToken}",
    ])->assertStatus(403);

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 5,
        'method' => 'tools/call',
        'params' => [
            'name' => 'delete_banner',
            'arguments' => ['id' => $this->heroBanner->id],
        ],
    ], [
        'Authorization' => "Bearer {$adminToken}",
    ])->assertOk();
});

test('banner resource and prompt can be read and listed via mcp', function () {
    // 1. resources/read returns active banners
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 10,
        'method' => 'resources/read',
        'params' => ['uri' => 'banners://active-campaigns'],
    ], [
        'Authorization' => 'Bearer test-static-agent-token-banner',
    ])->assertOk()
        ->assertSee('Jelajahi Keindahan Dunia');

    // 2. resources/list pagination: page 2 contains banners://active-campaigns
    $firstPage = $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 11,
        'method' => 'resources/list',
    ], [
        'Authorization' => 'Bearer test-static-agent-token-banner',
    ]);
    $cursor = $firstPage->json('result.nextCursor');

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 12,
        'method' => 'resources/list',
        'params' => ['cursor' => $cursor],
    ], [
        'Authorization' => 'Bearer test-static-agent-token-banner',
    ])->assertOk()
        ->assertJsonFragment(['uri' => 'banners://active-campaigns']);

    // 3. prompts/list contains design_banner_campaign
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 12,
        'method' => 'prompts/list',
    ], [
        'Authorization' => 'Bearer test-static-agent-token-banner',
    ])->assertOk()
        ->assertJsonFragment(['name' => 'design_banner_campaign']);

    // 4. prompts/get generates creative campaign prompt
    $promptResponse = $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 13,
        'method' => 'prompts/get',
        'params' => [
            'name' => 'design_banner_campaign',
            'arguments' => [
                'topic' => 'Promo Ramadhan Berkah',
                'audience' => 'Keluarga Jamaah',
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-static-agent-token-banner',
    ]);

    $promptResponse->assertOk()
        ->assertSee('Promo Ramadhan Berkah')
        ->assertSee('Pilihan Headline');
});
