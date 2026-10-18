<?php

use App\Mcp\Servers\BaharsyahServer;
use App\Mcp\Tools\Destinations\CreateDestinationTool;
use App\Mcp\Tools\Destinations\DeleteDestinationTool;
use App\Mcp\Tools\Destinations\GetDestinationTool;
use App\Mcp\Tools\Destinations\ListDestinationsTool;
use App\Mcp\Tools\Destinations\UpdateDestinationTool;
use App\Models\Country;
use App\Models\Destination;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Mcp\Server\Transport\FakeTransporter;
use Laravel\Passport\ClientRepository;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-static-agent-token-dest');

    $clientRepository = app(ClientRepository::class);
    $clientRepository->createPersonalAccessGrantClient('Test Personal Client', 'users');

    $this->country = Country::create([
        'name' => ['id' => 'Indonesia', 'en' => 'Indonesia', 'ms' => 'Indonesia'],
        'slug' => 'indonesia',
        'iso_alpha_2' => 'ID',
        'iso_alpha_3' => 'IDN',
    ]);
});

test('destination tools are registered in baharsyah server', function () {
    $server = app(BaharsyahServer::class, ['transport' => new FakeTransporter]);
    $reflection = new ReflectionClass($server);
    $tools = $reflection->getProperty('tools')->getValue($server);

    expect($tools)->toContain(
        ListDestinationsTool::class,
        GetDestinationTool::class,
        CreateDestinationTool::class,
        UpdateDestinationTool::class,
        DeleteDestinationTool::class,
    );
});

test('list destinations tool returns destinations with country, pagination, and locale formatting', function () {
    $dest1 = Destination::create([
        'name' => ['id' => 'Kepulauan Derawan', 'en' => 'Derawan Islands', 'ms' => 'Kepulauan Derawan'],
        'slug' => 'kepulauan-derawan',
        'description' => ['id' => 'Surga bawah laut di Berau', 'en' => 'Underwater paradise in Berau'],
        'location' => 'Kabupaten Berau, Kalimantan Timur',
        'is_active' => true,
        'is_featured' => true,
    ]);
    $dest1->countries()->attach($this->country->id);

    $dest2 = Destination::create([
        'name' => ['id' => 'Bukit Bangkirai', 'en' => 'Bangkirai Hill', 'ms' => 'Bukit Bangkirai'],
        'slug' => 'bukit-bangkirai',
        'location' => 'Kutai Kartanegara, Kalimantan Timur',
        'is_active' => false,
        'is_featured' => false,
    ]);

    // 1. List active only in Indonesian
    BaharsyahServer::tool(ListDestinationsTool::class, ['is_active' => true, 'locale' => 'id'])
        ->assertSee('Kepulauan Derawan')
        ->assertSee('Surga bawah laut di Berau')
        ->assertDontSee('Bukit Bangkirai');

    // 2. List in English
    BaharsyahServer::tool(ListDestinationsTool::class, ['is_active' => true, 'locale' => 'en'])
        ->assertSee('Derawan Islands')
        ->assertSee('Underwater paradise in Berau');

    // 3. Filter by country slug
    BaharsyahServer::tool(ListDestinationsTool::class, ['country_slug' => 'indonesia'])
        ->assertSee('Kepulauan Derawan');
});

test('get destination tool retrieves full details by id or slug', function () {
    $destination = Destination::create([
        'name' => ['id' => 'Labuan Cermin', 'en' => 'Mirror Lake', 'ms' => 'Tasik Cermin'],
        'slug' => 'labuan-cermin',
        'description' => ['id' => 'Danau dua rasa yang sangat jernih'],
        'location' => 'Biduk-Biduk, Berau',
        'is_active' => true,
    ]);
    $destination->countries()->attach($this->country->id);

    // Get by ID
    BaharsyahServer::tool(GetDestinationTool::class, ['id' => $destination->id])
        ->assertSee('Labuan Cermin')
        ->assertSee('Biduk-Biduk, Berau')
        ->assertSee('Indonesia');

    // Get by Slug in English
    BaharsyahServer::tool(GetDestinationTool::class, ['slug' => 'labuan-cermin', 'locale' => 'en'])
        ->assertSee('Mirror Lake');
});

test('create destination tool creates destination with string and country associations', function () {
    BaharsyahServer::tool(CreateDestinationTool::class, [
        'name' => 'Taman Nasional Kutai',
        'description' => 'Habitat orangutan liar dan pohon ulin raksasa.',
        'location' => 'Bontang & Kutai Timur',
        'country_slugs' => ['indonesia'],
        'is_featured' => true,
    ])
        ->assertSee('Destination created successfully.')
        ->assertSee('taman-nasional-kutai');

    $destination = Destination::where('slug', 'taman-nasional-kutai')->first();
    expect($destination)->not->toBeNull()
        ->and($destination->getTranslation('name', 'id', false))->toBe('Taman Nasional Kutai')
        ->and($destination->is_featured)->toBeTrue()
        ->and($destination->countries()->count())->toBe(1);
});

test('create destination tool supports multilingual object input', function () {
    BaharsyahServer::tool(CreateDestinationTool::class, [
        'name' => [
            'id' => 'Masjid Raya Darussalam',
            'en' => 'Darussalam Grand Mosque',
            'ms' => 'Masjid Raya Darussalam',
        ],
        'description' => [
            'id' => 'Ikon wisata religi di Samarinda',
            'en' => 'Religious tourism landmark in Samarinda',
            'ms' => 'Mercu tanda pelancongan agama di Samarinda',
        ],
        'slug' => 'masjid-raya-darussalam-samarinda',
        'location' => 'Samarinda, Kalimantan Timur',
    ])
        ->assertSee('Destination created successfully.')
        ->assertSee('masjid-raya-darussalam-samarinda');

    $destination = Destination::where('slug', 'masjid-raya-darussalam-samarinda')->first();
    expect($destination)->not->toBeNull()
        ->and($destination->getTranslation('name', 'en', false))->toBe('Darussalam Grand Mosque');
});

test('update destination tool updates destination details and country relations', function () {
    $destination = Destination::create([
        'name' => ['id' => 'Nama Destinasi Lama'],
        'slug' => 'destinasi-lama',
        'is_active' => true,
    ]);

    BaharsyahServer::tool(UpdateDestinationTool::class, [
        'id' => $destination->id,
        'name' => 'Nama Destinasi Direvisi',
        'custom_slug' => 'destinasi-direvisi',
        'location' => 'Balikpapan',
        'is_featured' => true,
        'country_slugs' => ['indonesia'],
    ])
        ->assertSee('Destination updated successfully.')
        ->assertSee('Nama Destinasi Direvisi');

    $destination->refresh();
    expect($destination->getTranslation('name', 'id', false))->toBe('Nama Destinasi Direvisi')
        ->and($destination->slug)->toBe('destinasi-direvisi')
        ->and($destination->is_featured)->toBeTrue()
        ->and($destination->countries()->count())->toBe(1);
});

test('delete destination tool requires force=true when connected to articles', function () {
    $user = User::factory()->create(['email' => 'admin@baharsyahjelajah.com']);
    $category = PostCategory::create([
        'name' => ['id' => 'Berita'],
        'slug' => ['id' => 'berita'],
    ]);

    $destination = Destination::create([
        'name' => ['id' => 'Pulau Maratua Terlindung'],
        'slug' => 'pulau-maratua-terlindung',
    ]);

    $post = Post::create([
        'user_id' => $user->id,
        'post_category_id' => $category->id,
        'title' => ['id' => 'Menikmati Maratua'],
        'slug' => ['id' => 'menikmati-maratua'],
        'content' => ['id' => '<p>Konten Maratua</p>'],
        'status' => 'published',
    ]);
    $destination->posts()->attach($post->id);

    // 1. Delete without force rejected
    BaharsyahServer::tool(DeleteDestinationTool::class, [
        'id' => $destination->id,
        'force' => false,
    ])
        ->assertSee('because it is currently linked to 1 content item(s)');

    expect(Destination::find($destination->id))->not->toBeNull();

    // 2. Delete with force=true succeeds and detaches pivot
    BaharsyahServer::tool(DeleteDestinationTool::class, [
        'id' => $destination->id,
        'force' => true,
    ])
        ->assertSee('deleted successfully.');

    expect(Destination::find($destination->id))->toBeNull()
        ->and($post->fresh()->destinations()->count())->toBe(0);
});

test('destination tools enforce oauth scopes properly', function () {
    $user = User::factory()->create(['email' => 'agent@baharsyahjelajah.com']);

    // 1. Read token cannot call create_destination (needs mcp:write)
    $readToken = $user->createToken('Read Agent', ['mcp:read'])->accessToken;

    $createRes = $this->withToken($readToken)->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => [
            'name' => 'create_destination',
            'arguments' => ['name' => 'Uji Otorisasi'],
        ],
    ]);
    $createRes->assertStatus(403);
    $createRes->assertJsonPath('error.message', "Forbidden: OAuth token lacks required scope 'mcp:write' for tool 'create_destination'.");

    // 2. Write token cannot call delete_destination (needs mcp:admin)
    $writeToken = $user->createToken('Write Agent', ['mcp:write'])->accessToken;

    $dest = Destination::create([
        'name' => ['id' => 'Uji Hapus Scope'],
        'slug' => 'uji-hapus-scope',
    ]);

    $deleteRes = $this->withToken($writeToken)->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/call',
        'params' => [
            'name' => 'delete_destination',
            'arguments' => ['id' => $dest->id],
        ],
    ]);
    $deleteRes->assertStatus(403);
    $deleteRes->assertJsonPath('error.message', "Forbidden: OAuth token lacks required scope 'mcp:admin' for tool 'delete_destination'.");
});

