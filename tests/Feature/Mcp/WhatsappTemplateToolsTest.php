<?php

use App\Mcp\Prompts\DraftWhatsappFollowupPrompt;
use App\Mcp\Resources\WhatsappTemplatesResource;
use App\Mcp\Servers\BaharsyahServer;
use App\Mcp\Tools\WhatsappTemplates\CreateWhatsappTemplateTool;
use App\Mcp\Tools\WhatsappTemplates\DeleteWhatsappTemplateTool;
use App\Mcp\Tools\WhatsappTemplates\GetWhatsappTemplateTool;
use App\Mcp\Tools\WhatsappTemplates\ListWhatsappTemplatesTool;
use App\Mcp\Tools\WhatsappTemplates\RenderWhatsappMessageTool;
use App\Mcp\Tools\WhatsappTemplates\UpdateWhatsappTemplateTool;
use App\Models\User;
use App\Models\WhatsappTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Mcp\Server\Transport\FakeTransporter;
use Laravel\Passport\ClientRepository;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-static-agent-token-whatsapp');

    $clientRepository = app(ClientRepository::class);
    $clientRepository->createPersonalAccessGrantClient('Test Personal Client', 'users');

    $this->templateTourId = WhatsappTemplate::create([
        'product_type' => 'tour',
        'locale' => 'id',
        'template' => 'Halo Baharsyah Jelajah, saya ingin menanyakan paket {product_name} untuk keberangkatan {date} sebanyak {pax} orang.',
        'variables' => ['{product_name}', '{date}', '{pax}'],
    ]);

    $this->templateUmrahId = WhatsappTemplate::create([
        'product_type' => 'umrah',
        'locale' => 'id',
        'template' => 'Assalamu\'alaikum, mohon info ketersediaan seat {product_name} paket kamar {room_type} untuk {pax} jamaah.',
        'variables' => ['{product_name}', '{room_type}', '{pax}'],
    ]);
});

test('whatsapp template tools, resource, and prompt are registered in baharsyah server', function () {
    $server = app(BaharsyahServer::class, ['transport' => new FakeTransporter]);
    $reflection = new ReflectionClass($server);

    $tools = $reflection->getProperty('tools')->getValue($server);
    expect($tools)->toContain(
        ListWhatsappTemplatesTool::class,
        GetWhatsappTemplateTool::class,
        RenderWhatsappMessageTool::class,
        CreateWhatsappTemplateTool::class,
        UpdateWhatsappTemplateTool::class,
        DeleteWhatsappTemplateTool::class,
    );

    $resources = $reflection->getProperty('resources')->getValue($server);
    expect($resources)->toContain(
        WhatsappTemplatesResource::class,
    );

    $prompts = $reflection->getProperty('prompts')->getValue($server);
    expect($prompts)->toContain(
        DraftWhatsappFollowupPrompt::class,
    );
});

test('list whatsapp templates returns filtered list', function () {
    WhatsappTemplate::create([
        'product_type' => 'vehicle',
        'locale' => 'en',
        'template' => 'Hello, I would like to book {vehicle_name} in {city} for {days} days.',
        'variables' => ['{vehicle_name}', '{city}', '{days}'],
    ]);

    // 1. Filter by product_type
    BaharsyahServer::tool(ListWhatsappTemplatesTool::class, ['product_type' => 'tour'])
        ->assertSee('paket {product_name}')
        ->assertDontSee('booking {vehicle_name}');

    // 2. Filter by locale
    BaharsyahServer::tool(ListWhatsappTemplatesTool::class, ['locale' => 'en'])
        ->assertSee('book {vehicle_name}')
        ->assertDontSee('Assalamu\'alaikum');

    // 3. Search query
    BaharsyahServer::tool(ListWhatsappTemplatesTool::class, ['search' => 'jamaah'])
        ->assertSee('umrah')
        ->assertDontSee('vehicle');
});

test('get whatsapp template returns details by id or product_type and locale', function () {
    // 1. By ID
    BaharsyahServer::tool(GetWhatsappTemplateTool::class, ['id' => $this->templateTourId->id])
        ->assertSee('tour')
        ->assertSee('{product_name}');

    // 2. By composite key
    BaharsyahServer::tool(GetWhatsappTemplateTool::class, ['product_type' => 'umrah', 'locale' => 'id'])
        ->assertSee('umrah')
        ->assertSee('{room_type}');

    // 3. Error if not found
    BaharsyahServer::tool(GetWhatsappTemplateTool::class, ['id' => 99999])
        ->assertSee('WhatsApp template with ID 99999 not found.');
});

test('render whatsapp message replaces variables and builds direct wa link', function () {
    $response = BaharsyahServer::tool(RenderWhatsappMessageTool::class, [
        'product_type' => 'tour',
        'locale' => 'id',
        'variables' => [
            'product_name' => 'Eksotisme Turki 10D',
            'date' => '25 November 2026',
            'pax' => '4',
        ],
        'phone_number' => '+62 812-3456-7890',
    ]);

    $response->assertSee('Eksotisme Turki 10D')
        ->assertSee('25 November 2026')
        ->assertSee('4 orang')
        ->assertSee('https://wa.me/6281234567890?text=');
});

test('create whatsapp template stores entry and auto extracts variable placeholders', function () {
    $response = BaharsyahServer::tool(CreateWhatsappTemplateTool::class, [
        'product_type' => 'vehicle',
        'locale' => 'id',
        'template' => 'Halo Baharsyah Jelajah, saya butuh sewa mobil {vehicle_name} di area {rental_area} mulai tanggal {start_date} selama {days} hari.',
    ]);

    $response->assertSee('Template WhatsApp berhasil dibuat.')
        ->assertSee('vehicle');

    $this->assertDatabaseHas('whatsapp_templates', [
        'product_type' => 'vehicle',
        'locale' => 'id',
    ]);

    $template = WhatsappTemplate::where('product_type', 'vehicle')->where('locale', 'id')->first();
    expect($template->variables)->toContain('{vehicle_name}', '{rental_area}', '{start_date}', '{days}');
});

test('create whatsapp template prevents duplicate product_type and locale', function () {
    BaharsyahServer::tool(CreateWhatsappTemplateTool::class, [
        'product_type' => 'tour',
        'locale' => 'id',
        'template' => 'Template duplikat',
    ])->assertSee("already exists");
});

test('update whatsapp template modifies text and auto updates placeholders', function () {
    $response = BaharsyahServer::tool(UpdateWhatsappTemplateTool::class, [
        'id' => $this->templateTourId->id,
        'template' => 'Pesan baru {product_name} dengan kode promo {promo_code}.',
    ]);

    $response->assertSee('Template WhatsApp berhasil diperbarui.');

    $fresh = $this->templateTourId->fresh();
    expect($fresh->template)->toBe('Pesan baru {product_name} dengan kode promo {promo_code}.')
        ->and($fresh->variables)->toContain('{product_name}', '{promo_code}');
});

test('delete whatsapp template removes record', function () {
    $response = BaharsyahServer::tool(DeleteWhatsappTemplateTool::class, [
        'id' => $this->templateUmrahId->id,
    ]);

    $response->assertSee('Template WhatsApp berhasil dihapus.');
    $this->assertDatabaseMissing('whatsapp_templates', ['id' => $this->templateUmrahId->id]);
});

test('whatsapp template tools enforce granular oauth scopes over http', function () {
    $adminUser = User::factory()->create();
    $readToken = $adminUser->createToken('Read Token', ['mcp:read'])->accessToken;
    $writeToken = $adminUser->createToken('Write Token', ['mcp:write'])->accessToken;
    $adminToken = $adminUser->createToken('Admin Token', ['mcp:admin'])->accessToken;

    // 1. list_whatsapp_templates & render_whatsapp_message allowed with mcp:read
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => [
            'name' => 'list_whatsapp_templates',
            'arguments' => [],
        ],
    ], [
        'Authorization' => "Bearer {$readToken}",
    ])->assertOk();

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/call',
        'params' => [
            'name' => 'render_whatsapp_message',
            'arguments' => [
                'product_type' => 'tour',
                'variables' => ['product_name' => 'Turki', 'date' => 'Besok', 'pax' => '2'],
            ],
        ],
    ], [
        'Authorization' => "Bearer {$readToken}",
    ])->assertOk();

    // 2. create_whatsapp_template forbidden with mcp:read, allowed with mcp:write
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'tools/call',
        'params' => [
            'name' => 'create_whatsapp_template',
            'arguments' => [
                'product_type' => 'visa',
                'locale' => 'id',
                'template' => 'Info visa {country}',
            ],
        ],
    ], [
        'Authorization' => "Bearer {$readToken}",
    ])->assertStatus(403);

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 4,
        'method' => 'tools/call',
        'params' => [
            'name' => 'create_whatsapp_template',
            'arguments' => [
                'product_type' => 'visa',
                'locale' => 'id',
                'template' => 'Info visa {country}',
            ],
        ],
    ], [
        'Authorization' => "Bearer {$writeToken}",
    ])->assertOk();

    // 3. delete_whatsapp_template forbidden with mcp:write, allowed with mcp:admin
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 5,
        'method' => 'tools/call',
        'params' => [
            'name' => 'delete_whatsapp_template',
            'arguments' => ['id' => $this->templateTourId->id],
        ],
    ], [
        'Authorization' => "Bearer {$writeToken}",
    ])->assertStatus(403);

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 6,
        'method' => 'tools/call',
        'params' => [
            'name' => 'delete_whatsapp_template',
            'arguments' => ['id' => $this->templateTourId->id],
        ],
    ], [
        'Authorization' => "Bearer {$adminToken}",
    ])->assertOk();
});

test('whatsapp template resource and prompt can be read and listed via mcp', function () {
    // 1. resources/read returns templates
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 10,
        'method' => 'resources/read',
        'params' => ['uri' => 'whatsapp://templates'],
    ], [
        'Authorization' => 'Bearer test-static-agent-token-whatsapp',
    ])->assertOk()
        ->assertSee('paket {product_name}');

    // 2. resources/list pagination: page 2 contains whatsapp://templates
    $firstPage = $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 11,
        'method' => 'resources/list',
    ], [
        'Authorization' => 'Bearer test-static-agent-token-whatsapp',
    ]);
    $cursor = $firstPage->json('result.nextCursor');

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 12,
        'method' => 'resources/list',
        'params' => ['cursor' => $cursor],
    ], [
        'Authorization' => 'Bearer test-static-agent-token-whatsapp',
    ])->assertOk()
        ->assertJsonFragment(['uri' => 'whatsapp://templates']);

    // 3. prompts/list pagination: page 2 contains draft_whatsapp_followup
    $promptPage1 = $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 13,
        'method' => 'prompts/list',
    ], [
        'Authorization' => 'Bearer test-static-agent-token-whatsapp',
    ]);
    $promptCursor = $promptPage1->json('result.nextCursor');

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 14,
        'method' => 'prompts/list',
        'params' => ['cursor' => $promptCursor],
    ], [
        'Authorization' => 'Bearer test-static-agent-token-whatsapp',
    ])->assertOk()
        ->assertJsonFragment(['name' => 'draft_whatsapp_followup']);

    // 4. prompts/get generates customized follow-up prompt
    $promptResponse = $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 14,
        'method' => 'prompts/get',
        'params' => [
            'name' => 'draft_whatsapp_followup',
            'arguments' => [
                'customer_name' => 'Ibu Rahmawati',
                'product_type' => 'tour',
                'product_name' => 'Tour Eropa Barat 12 Hari',
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-static-agent-token-whatsapp',
    ]);

    $promptResponse->assertOk()
        ->assertSee('Ibu Rahmawati')
        ->assertSee('Tour Eropa Barat 12 Hari')
        ->assertSee('Naskah Chat WhatsApp Siap Kirim');
});