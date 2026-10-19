<?php

use App\Enums\FaqCategory;
use App\Mcp\Prompts\ConsultFaqAnswersPrompt;
use App\Mcp\Resources\FaqKnowledgeBaseResource;
use App\Mcp\Servers\BaharsyahServer;
use App\Mcp\Tools\Faqs\CreateFaqTool;
use App\Mcp\Tools\Faqs\DeleteFaqTool;
use App\Mcp\Tools\Faqs\GetFaqTool;
use App\Mcp\Tools\Faqs\ListFaqsTool;
use App\Mcp\Tools\Faqs\UpdateFaqTool;
use App\Models\Faq;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Mcp\Server\Transport\FakeTransporter;
use Laravel\Passport\ClientRepository;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-static-agent-token-faq');

    $clientRepository = app(ClientRepository::class);
    $clientRepository->createPersonalAccessGrantClient('Test Personal Client', 'users');

    $this->faqGeneral = Faq::create([
        'question' => [
            'id' => 'Bagaimana cara memesan paket perjalanan di Baharsyah Jelajah?',
            'en' => 'How do I book a tour package at Baharsyah Jelajah?',
        ],
        'answer' => [
            'id' => 'Pemesanan dapat dilakukan langsung melalui situs web atau tim WhatsApp kami.',
            'en' => 'Bookings can be made directly through our website or WhatsApp support team.',
        ],
        'category' => FaqCategory::General,
        'contexts' => ['home', 'booking'],
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $this->faqTour = Faq::create([
        'question' => [
            'id' => 'Apakah harga paket tur sudah termasuk tiket pesawat internasional?',
            'en' => 'Does the tour package price include international flight tickets?',
        ],
        'answer' => [
            'id' => 'Sebagian besar paket wisata kami sudah include tiket penerbangan PP kelas ekonomi.',
            'en' => 'Most of our tour packages include return economy class flight tickets.',
        ],
        'category' => FaqCategory::Tour,
        'contexts' => ['home', 'tour'],
        'sort_order' => 2,
        'is_active' => true,
    ]);
});

test('faq tools, resource, and prompt are registered in baharsyah server', function () {
    $server = app(BaharsyahServer::class, ['transport' => new FakeTransporter]);
    $reflection = new ReflectionClass($server);

    $tools = $reflection->getProperty('tools')->getValue($server);
    expect($tools)->toContain(
        ListFaqsTool::class,
        GetFaqTool::class,
        CreateFaqTool::class,
        UpdateFaqTool::class,
        DeleteFaqTool::class,
    );

    $resources = $reflection->getProperty('resources')->getValue($server);
    expect($resources)->toContain(
        FaqKnowledgeBaseResource::class,
    );

    $prompts = $reflection->getProperty('prompts')->getValue($server);
    expect($prompts)->toContain(
        ConsultFaqAnswersPrompt::class,
    );
});

test('list faqs returns filtered results by category context and search', function () {
    Faq::create([
        'question' => ['id' => 'Kapan batas pelunasan paket Umrah?'],
        'answer' => ['id' => 'Pelunasan maksimal H-30 sebelum tanggal keberangkatan.'],
        'category' => FaqCategory::Umrah,
        'contexts' => ['home', 'umrah'],
        'sort_order' => 3,
        'is_active' => false,
    ]);

    // 1. Filter active only
    BaharsyahServer::tool(ListFaqsTool::class, ['is_active' => true])
        ->assertSee('Bagaimana cara memesan')
        ->assertDontSee('Kapan batas pelunasan paket Umrah');

    // 2. Filter by category
    BaharsyahServer::tool(ListFaqsTool::class, ['category' => 'tour'])
        ->assertSee('tiket pesawat internasional')
        ->assertDontSee('Bagaimana cara memesan');

    // 3. Filter by context
    BaharsyahServer::tool(ListFaqsTool::class, ['context' => 'booking'])
        ->assertSee('Bagaimana cara memesan')
        ->assertDontSee('tiket pesawat internasional');

    // 4. Search keyword
    BaharsyahServer::tool(ListFaqsTool::class, ['search' => 'tiket'])
        ->assertSee('tiket pesawat internasional');
});

test('get faq returns complete details with all translations', function () {
    BaharsyahServer::tool(GetFaqTool::class, ['id' => $this->faqGeneral->id, 'locale' => 'id'])
        ->assertSee('Bagaimana cara memesan')
        ->assertSee('Pemesanan dapat dilakukan langsung')
        ->assertSee('general');

    BaharsyahServer::tool(GetFaqTool::class, ['id' => 9999])
        ->assertSee('FAQ with ID 9999 not found.');
});

test('create faq stores multilingual question answer and auto infers contexts', function () {
    $response = BaharsyahServer::tool(CreateFaqTool::class, [
        'question' => [
            'id' => 'Apakah tersedia layanan sewa mobil lepas kunci di Saudi?',
            'en' => 'Is self-drive car rental available in Saudi Arabia?',
        ],
        'answer' => [
            'id' => 'Saat ini seluruh armada sewa kendaraan kami di Arab Saudi include driver profesional.',
            'en' => 'Currently all our fleet rentals in Saudi Arabia include professional drivers.',
        ],
        'category' => 'vehicle',
        'sort_order' => 5,
    ]);

    $response->assertSee('FAQ berhasil dibuat.')
        ->assertSee('vehicle');

    $this->assertDatabaseHas('faqs', [
        'category' => 'vehicle',
        'sort_order' => 5,
    ]);

    $createdFaq = Faq::where('category', 'vehicle')->first();
    expect($createdFaq->contexts)->toContain('home', 'vehicle');
});

test('update faq modifies partial fields and updates translations', function () {
    $response = BaharsyahServer::tool(UpdateFaqTool::class, [
        'id' => $this->faqTour->id,
        'question' => [
            'id' => 'Apakah harga tur sudah include visa dan asuransi?',
        ],
        'category' => 'general',
        'sort_order' => 10,
    ]);

    $response->assertSee('FAQ berhasil diperbarui.')
        ->assertSee('Apakah harga tur sudah include visa');

    expect($this->faqTour->fresh()->sort_order)->toBe(10)
        ->and($this->faqTour->fresh()->category)->toBe(FaqCategory::General);
});

test('delete faq permanently removes entry', function () {
    $response = BaharsyahServer::tool(DeleteFaqTool::class, [
        'id' => $this->faqTour->id,
    ]);

    $response->assertSee('FAQ berhasil dihapus.');
    $this->assertDatabaseMissing('faqs', ['id' => $this->faqTour->id]);
});

test('faq tools enforce granular oauth scopes over http transport', function () {
    $adminUser = User::factory()->create();
    $readToken = $adminUser->createToken('Read Token', ['mcp:read'])->accessToken;
    $writeToken = $adminUser->createToken('Write Token', ['mcp:write'])->accessToken;
    $adminToken = $adminUser->createToken('Admin Token', ['mcp:admin'])->accessToken;

    // 1. list_faqs allowed with mcp:read
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => [
            'name' => 'list_faqs',
            'arguments' => [],
        ],
    ], [
        'Authorization' => "Bearer {$readToken}",
    ])->assertOk();

    // 2. create_faq forbidden with mcp:read, allowed with mcp:write
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/call',
        'params' => [
            'name' => 'create_faq',
            'arguments' => [
                'question' => 'Contoh pertanyaan',
                'answer' => 'Contoh jawaban',
            ],
        ],
    ], [
        'Authorization' => "Bearer {$readToken}",
    ])->assertStatus(403);

    $createResponse = $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'tools/call',
        'params' => [
            'name' => 'create_faq',
            'arguments' => [
                'question' => 'Contoh pertanyaan',
                'answer' => 'Contoh jawaban',
            ],
        ],
    ], [
        'Authorization' => "Bearer {$writeToken}",
    ]);
    $createResponse->assertOk();
    $createdId = $createResponse->json('result.content.0.text');

    // 3. delete_faq forbidden with mcp:write, allowed with mcp:admin
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 4,
        'method' => 'tools/call',
        'params' => [
            'name' => 'delete_faq',
            'arguments' => ['id' => $this->faqGeneral->id],
        ],
    ], [
        'Authorization' => "Bearer {$writeToken}",
    ])->assertStatus(403);

    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 5,
        'method' => 'tools/call',
        'params' => [
            'name' => 'delete_faq',
            'arguments' => ['id' => $this->faqGeneral->id],
        ],
    ], [
        'Authorization' => "Bearer {$adminToken}",
    ])->assertOk();
});

test('faq resource and prompt can be read and listed via mcp', function () {
    // 1. resources/list contains faqs://knowledge-base
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 10,
        'method' => 'resources/list',
    ], [
        'Authorization' => 'Bearer test-static-agent-token-faq',
    ])->assertOk()
        ->assertJsonFragment(['uri' => 'faqs://knowledge-base']);

    // 2. resources/read returns active faqs
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 11,
        'method' => 'resources/read',
        'params' => ['uri' => 'faqs://knowledge-base'],
    ], [
        'Authorization' => 'Bearer test-static-agent-token-faq',
    ])->assertOk()
        ->assertSee('Bagaimana cara memesan');

    // 3. prompts/list contains consult_faq_answers
    $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 12,
        'method' => 'prompts/list',
    ], [
        'Authorization' => 'Bearer test-static-agent-token-faq',
    ])->assertOk()
        ->assertJsonFragment(['name' => 'consult_faq_answers']);

    // 4. prompts/get generates prompt with customer query
    $promptResponse = $this->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 13,
        'method' => 'prompts/get',
        'params' => [
            'name' => 'consult_faq_answers',
            'arguments' => [
                'customer_query' => 'Apakah tiket pesawat sudah termasuk?',
                'category' => 'tour',
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-static-agent-token-faq',
    ]);

    $promptResponse->assertOk()
        ->assertSee('Apakah tiket pesawat sudah termasuk?')
        ->assertSee('tiket penerbangan PP kelas ekonomi');
});
