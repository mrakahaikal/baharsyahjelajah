<?php

use App\Mcp\Prompts\AuditCategoryTaxonomyPrompt;
use App\Mcp\Prompts\SuggestCategoryForTopicPrompt;
use App\Mcp\Resources\CategoryTaxonomyResource;
use App\Mcp\Servers\BaharsyahServer;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Mcp\Server\Transport\FakeTransporter;
use Laravel\Passport\ClientRepository;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-secret-mcp-token-123');

    $clientRepository = app(ClientRepository::class);
    $clientRepository->createPersonalAccessGrantClient('Test Personal Client', 'users');
});

test('category taxonomy resource and prompts are registered in baharsyah mcp server', function () {
    $server = app(BaharsyahServer::class, ['transport' => new FakeTransporter]);
    $reflection = new ReflectionClass($server);

    $resourcesProperty = $reflection->getProperty('resources');
    $resources = $resourcesProperty->getValue($server);
    expect($resources)->toContain(CategoryTaxonomyResource::class);

    $promptsProperty = $reflection->getProperty('prompts');
    $prompts = $promptsProperty->getValue($server);
    expect($prompts)->toContain(
        AuditCategoryTaxonomyPrompt::class,
        SuggestCategoryForTopicPrompt::class,
    );
});

test('category taxonomy resource returns active category list with translations and article counts', function () {
    $catA = PostCategory::create([
        'name' => ['id' => 'Wisata Bahari', 'en' => 'Marine Tourism', 'ms' => 'Pelancongan Marin'],
        'slug' => ['id' => 'wisata-bahari', 'en' => 'marine-tourism', 'ms' => 'pelancongan-marin'],
        'description' => ['id' => 'Eksplorasi laut', 'en' => 'Sea exploration', 'ms' => 'Penerokaan laut'],
    ]);

    $catB = PostCategory::create([
        'name' => ['id' => 'Ekowisata Hutan', 'en' => 'Jungle Ecotourism', 'ms' => 'Ekopelancongan Hutan'],
        'slug' => ['id' => 'ekowisata-hutan', 'en' => 'jungle-ecotourism', 'ms' => 'ekopelancongan-hutan'],
    ]);

    $user = User::factory()->create(['email' => 'admin@baharsyahjelajah.com']);
    Post::create([
        'user_id' => $user->id,
        'post_category_id' => $catA->id,
        'title' => ['id' => 'Snorkeling di Kakaban', 'en' => 'Snorkeling in Kakaban', 'ms' => 'Snorkeling di Kakaban'],
        'slug' => ['id' => 'snorkeling-di-kakaban', 'en' => 'snorkeling-in-kakaban', 'ms' => 'snorkeling-di-kakaban'],
        'content' => ['id' => '<p>Konten snorkeling.</p>', 'en' => '<p>Snorkeling content.</p>', 'ms' => '<p>Kandungan snorkeling.</p>'],
        'status' => 'published',
    ]);

    BaharsyahServer::resource(CategoryTaxonomyResource::class)
        ->assertSee('categories://taxonomy')
        ->assertSee('Wisata Bahari')
        ->assertSee('Marine Tourism')
        ->assertSee('Ekowisata Hutan');
});

test('mcp endpoint allows reading categories://taxonomy resource via json-rpc', function () {
    PostCategory::create([
        'name' => ['id' => 'Kategori Pulau', 'en' => 'Island Category', 'ms' => 'Kategori Pulau'],
        'slug' => ['id' => 'kategori-pulau', 'en' => 'island-category', 'ms' => 'kategori-pulau'],
    ]);

    $response = $this->withToken('test-secret-mcp-token-123')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 10,
        'method' => 'resources/read',
        'params' => [
            'uri' => 'categories://taxonomy',
        ],
    ]);

    $response->assertOk();
    $response->assertJsonPath('result.contents.0.uri', 'categories://taxonomy');
    $response->assertJsonPath('result.contents.0.mimeType', 'application/json');
    expect($response->json('result.contents.0.text'))->toContain('Kategori Pulau');
});

test('mcp endpoint lists registered resources including categories://taxonomy', function () {
    $response = $this->withToken('test-secret-mcp-token-123')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 11,
        'method' => 'resources/list',
    ]);

    $response->assertOk();
    $resources = $response->json('result.resources');
    $uris = collect($resources)->pluck('uri')->all();
    expect($uris)->toContain('categories://taxonomy');
});

test('audit category taxonomy prompt generates structured audit instructions with categories context', function () {
    PostCategory::create([
        'name' => ['id' => 'Kategori Kosong', 'en' => '', 'ms' => ''],
        'slug' => ['id' => 'kategori-kosong', 'en' => '', 'ms' => ''],
    ]);

    BaharsyahServer::prompt(AuditCategoryTaxonomyPrompt::class)
        ->assertSee('Kategori Kosong')
        ->assertSee('Kelengkapan Multilingual')
        ->assertSee('Kategori Kosong');
});

test('suggest category for topic prompt generates recommendations with given topic argument', function () {
    PostCategory::create([
        'name' => ['id' => 'Petualangan Gua', 'en' => 'Cave Adventure', 'ms' => 'Pengembaraan Gua'],
        'slug' => ['id' => 'petualangan-gua', 'en' => 'cave-adventure', 'ms' => 'pengembaraan-gua'],
    ]);

    BaharsyahServer::prompt(SuggestCategoryForTopicPrompt::class, [
        'topic' => 'Menyusuri Gua Haji Mangku Maratua',
        'keywords' => 'caving, stalaktit, maratua',
    ])
        ->assertSee('Menyusuri Gua Haji Mangku Maratua')
        ->assertSee('Petualangan Gua')
        ->assertSee('caving, stalaktit, maratua');
});

test('mcp endpoint lists registered prompts including audit and suggestion prompts', function () {
    $response = $this->withToken('test-secret-mcp-token-123')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 12,
        'method' => 'prompts/list',
    ]);

    $response->assertOk();
    $prompts = $response->json('result.prompts');
    $names = collect($prompts)->pluck('name')->all();
    expect($names)->toContain('audit_category_taxonomy', 'suggest_category_for_topic');
});

test('mcp endpoint handles prompts/get via oauth token with mcp:read scope', function () {
    $user = User::factory()->create(['email' => 'reader@baharsyahjelajah.com']);
    $token = $user->createToken('ChatGPT Prompt User', ['mcp:read'])->accessToken;

    $response = $this->withToken($token)->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 13,
        'method' => 'prompts/get',
        'params' => [
            'name' => 'suggest_category_for_topic',
            'arguments' => [
                'topic' => 'Wisata Penyu di Pulau Sangalaki',
            ],
        ],
    ]);

    $response->assertOk();
    $messages = $response->json('result.messages');
    expect($messages)->not->toBeEmpty();
    expect($messages[0]['content']['text'])->toContain('Wisata Penyu di Pulau Sangalaki');
});
