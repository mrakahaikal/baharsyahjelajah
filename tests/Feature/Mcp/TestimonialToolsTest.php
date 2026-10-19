<?php

use App\Enums\VehicleCategory;
use App\Mcp\Prompts\CurateSocialProofPrompt;
use App\Mcp\Resources\CuratedTestimonialsResource;
use App\Mcp\Servers\BaharsyahServer;
use App\Mcp\Tools\Testimonials\CreateTestimonialTool;
use App\Mcp\Tools\Testimonials\DeleteTestimonialTool;
use App\Mcp\Tools\Testimonials\GetTestimonialTool;
use App\Mcp\Tools\Testimonials\ListTestimonialsTool;
use App\Mcp\Tools\Testimonials\UpdateTestimonialTool;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Mcp\Server\Transport\FakeTransporter;
use Laravel\Passport\ClientRepository;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-static-agent-token-testimonial');

    $clientRepository = app(ClientRepository::class);
    $clientRepository->createPersonalAccessGrantClient('Test Personal Client', 'users');

    $this->vehicle = Vehicle::create([
        'catalog_code' => 'ALPHARD-TEST-01',
        'name' => ['id' => 'Toyota Alphard Transformer'],
        'slug' => ['id' => 'toyota-alphard-transformer'],
        'category' => VehicleCategory::Car,
        'capacity_pax' => 5,
        'transmission' => 'automatic',
        'is_active' => true,
    ]);

    $this->testimonialFeatured = Testimonial::create([
        'reviewer_name' => [
            'id' => 'H. Ahmad Fauzi & Keluarga',
            'en' => 'H. Ahmad Fauzi & Family',
        ],
        'reviewer_country' => 'Indonesia',
        'reviewer_flag' => 'ID',
        'product_type' => Vehicle::class,
        'product_id' => $this->vehicle->id,
        'rating' => 5,
        'content' => [
            'id' => 'Pelayanan sewa Alphard di Makkah sangat memuaskan, driver sopan dan mobil wangi.',
            'en' => 'Alphard rental service in Makkah was exceptional, polite driver and pristine car.',
        ],
        'is_featured' => true,
        'is_active' => true,
    ]);

    $this->testimonialNormal = Testimonial::create([
        'reviewer_name' => [
            'id' => 'Siti Aminah',
            'en' => 'Siti Aminah',
        ],
        'reviewer_country' => 'Malaysia',
        'reviewer_flag' => 'MY',
        'product_type' => Vehicle::class,
        'product_id' => $this->vehicle->id,
        'rating' => 4,
        'content' => [
            'id' => 'Sangat recommended untuk ziarah rombongan kecil, tepat waktu.',
            'en' => 'Highly recommended for small group holy site visits, always on time.',
        ],
        'is_featured' => false,
        'is_active' => true,
    ]);
});

test('testimonial tools, resource, and prompt are registered in baharsyah server', function () {
    $server = app(BaharsyahServer::class, ['transport' => new FakeTransporter]);
    $reflection = new ReflectionClass($server);

    $tools = $reflection->getProperty('tools')->getValue($server);
    expect($tools)->toContain(
        ListTestimonialsTool::class,
        GetTestimonialTool::class,
        CreateTestimonialTool::class,
        UpdateTestimonialTool::class,
        DeleteTestimonialTool::class,
    );

    $resources = $reflection->getProperty('resources')->getValue($server);
    expect($resources)->toContain(
        CuratedTestimonialsResource::class,
    );

    $prompts = $reflection->getProperty('prompts')->getValue($server);
    expect($prompts)->toContain(
        CurateSocialProofPrompt::class,
    );
});

test('list testimonials returns filtered and paginated results', function () {
    Testimonial::create([
        'reviewer_name' => ['id' => 'Ulasan Nonaktif'],
        'content' => ['id' => 'Konten nonaktif'],
        'product_type' => Vehicle::class,
        'product_id' => $this->vehicle->id,
        'rating' => 3,
        'is_active' => false,
    ]);

    // 1. Filter active
    BaharsyahServer::tool(ListTestimonialsTool::class, ['is_active' => true])
        ->assertSee('H. Ahmad Fauzi')
        ->assertDontSee('Ulasan Nonaktif');

    // 2. Filter featured
    BaharsyahServer::tool(ListTestimonialsTool::class, ['is_featured' => true])
        ->assertSee('H. Ahmad Fauzi')
        ->assertDontSee('Siti Aminah');

    // 3. Filter min rating
    BaharsyahServer::tool(ListTestimonialsTool::class, ['min_rating' => 5])
        ->assertSee('H. Ahmad Fauzi')
        ->assertDontSee('Siti Aminah');

    // 4. Filter product_type
    BaharsyahServer::tool(ListTestimonialsTool::class, ['product_type' => 'vehicle'])
        ->assertSee('H. Ahmad Fauzi');
});

test('get testimonial returns detailed review profile with stars and product info', function () {
    BaharsyahServer::tool(GetTestimonialTool::class, ['id' => $this->testimonialFeatured->id, 'locale' => 'id'])
        ->assertSee('H. Ahmad Fauzi')
        ->assertSee('★★★★★')
        ->assertSee('Toyota Alphard Transformer');

    BaharsyahServer::tool(GetTestimonialTool::class, ['id' => 99999])
        ->assertSee('Testimonial with ID 99999 not found.');
});

test('create testimonial creates entry and verifies associated product', function () {
    // 1. Success with valid vehicle product
    $response = BaharsyahServer::tool(CreateTestimonialTool::class, [
        'reviewer_name' => [
            'id' => 'Budi Santoso',
            'en' => 'Budi Santoso',
        ],
        'content' => 'Pengalaman luar biasa saat city tour Madinah bersama supir ramah.',
        'rating' => 5,
        'product_type' => 'vehicle',
        'product_id' => $this->vehicle->id,
        'reviewer_country' => 'Indonesia',
        'reviewer_flag' => 'ID',
        'is_featured' => true,
    ]);

    $response->assertSee('Testimonial berhasil dibuat.')
        ->assertSee('Budi Santoso');

    $this->assertDatabaseHas('testimonials', [
        'product_id' => $this->vehicle->id,
        'rating' => 5,
        'is_featured' => true,
    ]);

    // 2. Error if product does not exist
    BaharsyahServer::tool(CreateTestimonialTool::class, [
        'reviewer_name' => 'Reviewer Salah',
        'content' => 'Konten ulasan',
        'rating' => 5,
        'product_type' => 'vehicle',
        'product_id' => 88888,
    ])->assertSee('does not exist');
});

test('update testimonial modifies fields and rating', function () {
    $response = BaharsyahServer::tool(UpdateTestimonialTool::class, [
        'id' => $this->testimonialNormal->id,
        'rating' => 5,
        'reviewer_country' => 'Brunei',
        'is_featured' => true,
    ]);

    $response->assertSee('Testimonial berhasil diperbarui.');

    expect($this->testimonialNormal->fresh()->rating)->toBe(5)
        ->and($this->testimonialNormal->fresh()->reviewer_country)->toBe('Brunei')
        ->and($this->testimonialNormal->fresh()->is_featured)->toBeTrue();
});

test('delete testimonial removes record', function () {
    $response = BaharsyahServer::tool(DeleteTestimonialTool::class, [
        'id' => $this->testimonialNormal->id,
    ]);

    $response->assertSee('Testimonial berhasil dihapus.');
    $this->assertDatabaseMissing('testimonials', ['id' => $this->testimonialNormal->id]);
});

test('testimonial tools enforce granular oauth scopes over http', function () {
    $adminUser = User::factory()->create();
    $readToken = $adminUser->createToken('Read Token', ['mcp:read'])->accessToken;
    $writeToken = $adminUser->createToken('Write Token', ['mcp:write'])->accessToken;
    $adminToken = $adminUser->createToken('Admin Token', ['mcp:admin'])->accessToken;

    // 1. list_testimonials allowed with mcp:read
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => [
            'name' => 'list_testimonials',
            'arguments' => [],
        ],
    ], [
        'Authorization' => "Bearer {$readToken}",
    ])->assertOk();

    // 2. create_testimonial forbidden with mcp:read, allowed with mcp:write
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/call',
        'params' => [
            'name' => 'create_testimonial',
            'arguments' => [
                'reviewer_name' => 'Uji Scope',
                'content' => 'Bagus sekali',
                'rating' => 5,
                'product_type' => 'vehicle',
                'product_id' => $this->vehicle->id,
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
            'name' => 'create_testimonial',
            'arguments' => [
                'reviewer_name' => 'Uji Scope',
                'content' => 'Bagus sekali',
                'rating' => 5,
                'product_type' => 'vehicle',
                'product_id' => $this->vehicle->id,
            ],
        ],
    ], [
        'Authorization' => "Bearer {$writeToken}",
    ])->assertOk();

    // 3. delete_testimonial forbidden with mcp:write, allowed with mcp:admin
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 4,
        'method' => 'tools/call',
        'params' => [
            'name' => 'delete_testimonial',
            'arguments' => ['id' => $this->testimonialFeatured->id],
        ],
    ], [
        'Authorization' => "Bearer {$writeToken}",
    ])->assertStatus(403);

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 5,
        'method' => 'tools/call',
        'params' => [
            'name' => 'delete_testimonial',
            'arguments' => ['id' => $this->testimonialFeatured->id],
        ],
    ], [
        'Authorization' => "Bearer {$adminToken}",
    ])->assertOk();
});

test('testimonial resource and prompt can be read and listed via mcp', function () {
    // 1. resources/list contains testimonials://curated
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 10,
        'method' => 'resources/list',
    ], [
        'Authorization' => 'Bearer test-static-agent-token-testimonial',
    ])->assertOk()
        ->assertJsonFragment(['uri' => 'testimonials://curated']);

    // 2. resources/read returns curated testimonials
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 11,
        'method' => 'resources/read',
        'params' => ['uri' => 'testimonials://curated'],
    ], [
        'Authorization' => 'Bearer test-static-agent-token-testimonial',
    ])->assertOk()
        ->assertSee('H. Ahmad Fauzi');

    // 3. prompts/list contains curate_social_proof
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 12,
        'method' => 'prompts/list',
    ], [
        'Authorization' => 'Bearer test-static-agent-token-testimonial',
    ])->assertOk()
        ->assertJsonFragment(['name' => 'curate_social_proof']);

    // 4. prompts/get generates prompt with theme and product type
    $promptResponse = $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 13,
        'method' => 'prompts/get',
        'params' => [
            'name' => 'curate_social_proof',
            'arguments' => [
                'product_type' => 'vehicle',
                'theme' => 'Kenyamanan Ziarah Keluarga',
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-static-agent-token-testimonial',
    ]);

    $promptResponse->assertOk()
        ->assertSee('Kenyamanan Ziarah Keluarga')
        ->assertSee('Pelayanan sewa Alphard di Makkah sangat memuaskan');
});
