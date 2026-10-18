<?php

use App\Mcp\Prompts\AuditDestinationsReadinessPrompt;
use App\Mcp\Prompts\PlanDestinationHighlightPrompt;
use App\Mcp\Resources\DestinationCatalogResource;
use App\Mcp\Resources\FeaturedDestinationsResource;
use App\Mcp\Servers\BaharsyahServer;
use App\Models\Country;
use App\Models\Destination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Mcp\Server\Transport\FakeTransporter;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-static-agent-token-dest-rp');

    $this->country = Country::create([
        'name' => ['id' => 'Indonesia', 'en' => 'Indonesia'],
        'slug' => 'indonesia',
        'iso_alpha_2' => 'ID',
    ]);
});

test('destination resources and prompts are registered in baharsyah server', function () {
    $server = app(BaharsyahServer::class, ['transport' => new FakeTransporter]);
    $reflection = new ReflectionClass($server);

    $resources = $reflection->getProperty('resources')->getValue($server);
    expect($resources)->toContain(
        DestinationCatalogResource::class,
        FeaturedDestinationsResource::class,
    );

    $prompts = $reflection->getProperty('prompts')->getValue($server);
    expect($prompts)->toContain(
        PlanDestinationHighlightPrompt::class,
        AuditDestinationsReadinessPrompt::class,
    );
});

test('destination catalog resource returns all active destinations via jsonrpc', function () {
    $activeDest = Destination::create([
        'name' => ['id' => 'Danau Kakaban', 'en' => 'Kakaban Lake'],
        'slug' => 'danau-kakaban',
        'location' => 'Berau, Kaltim',
        'is_active' => true,
    ]);
    $activeDest->countries()->attach($this->country->id);

    Destination::create([
        'name' => ['id' => 'Gua Tertutup'],
        'slug' => 'gua-tertutup',
        'is_active' => false,
    ]);

    $response = $this->withToken('test-static-agent-token-dest-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'resources/read',
        'params' => [
            'uri' => 'destinations://catalog',
        ],
    ]);

    $response->assertOk();
    $result = $response->json('result.contents.0.text');
    $data = json_decode($result, true);

    expect($data['resource'])->toBe('destinations://catalog')
        ->and($data['total_active_destinations'])->toBe(1)
        ->and($data['destinations'][0]['slug'])->toBe('danau-kakaban')
        ->and($data['destinations'][0]['countries'][0]['slug'])->toBe('indonesia');
});

test('featured destinations resource returns only featured destinations via jsonrpc', function () {
    Destination::create([
        'name' => ['id' => 'Pulau Sangalaki'],
        'slug' => 'pulau-sangalaki',
        'is_active' => true,
        'is_featured' => true,
    ]);

    Destination::create([
        'name' => ['id' => 'Taman Kota'],
        'slug' => 'taman-kota',
        'is_active' => true,
        'is_featured' => false,
    ]);

    $response = $this->withToken('test-static-agent-token-dest-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'resources/read',
        'params' => [
            'uri' => 'destinations://featured',
        ],
    ]);

    $response->assertOk();
    $result = $response->json('result.contents.0.text');
    $data = json_decode($result, true);

    expect($data['resource'])->toBe('destinations://featured')
        ->and($data['featured_count'])->toBe(1)
        ->and($data['featured_destinations'][0]['slug'])->toBe('pulau-sangalaki');
});

test('plan destination highlight prompt generates travel planning guidelines with destination context', function () {
    $destination = Destination::create([
        'name' => ['id' => 'Kepulauan Maratua', 'en' => 'Maratua Atoll'],
        'slug' => 'kepulauan-maratua',
        'description' => ['id' => 'Atol indah dengan terumbu karang megah'],
        'location' => 'Berau, Kaltim',
        'is_active' => true,
        'is_featured' => true,
    ]);

    $response = $this->withToken('test-static-agent-token-dest-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'prompts/get',
        'params' => [
            'name' => 'plan_destination_highlight',
            'arguments' => [
                'destination_slug' => 'kepulauan-maratua',
                'duration_days' => 4,
                'target_audience' => 'wisatawan bahari dan penyelam',
            ],
        ],
    ]);

    $response->assertOk();
    $text = $response->json('result.messages.0.content.text');

    expect($text)->toContain('Kepulauan Maratua')
        ->and($text)->toContain('4 Hari')
        ->and($text)->toContain('wisatawan bahari dan penyelam')
        ->and($text)->toContain('Rancangan Itinerary Harian')
        ->and($text)->toContain('create_article');
});

test('audit destinations readiness prompt lists readiness checklist for all destinations', function () {
    $destination = Destination::create([
        'name' => ['id' => 'Batu Dinding'],
        'slug' => 'batu-dinding',
        'is_active' => true,
    ]);

    $response = $this->withToken('test-static-agent-token-dest-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 4,
        'method' => 'prompts/get',
        'params' => [
            'name' => 'audit_destinations_readiness',
            'arguments' => [],
        ],
    ]);

    $response->assertOk();
    $text = $response->json('result.messages.0.content.text');

    expect($text)->toContain('batu-dinding')
        ->and($text)->toContain('Kelengkapan Multilingual')
        ->and($text)->toContain('Konektivitas Geografis & Peta')
        ->and($text)->toContain('update_destination');
});

test('resources/list and prompts/list include new destination items', function () {
    $res = $this->withToken('test-static-agent-token-dest-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 5,
        'method' => 'resources/list',
    ]);
    $res->assertOk();
    $uris = collect($res->json('result.resources'))->pluck('uri')->all();
    expect($uris)->toContain('destinations://catalog', 'destinations://featured');

    $promptRes = $this->withToken('test-static-agent-token-dest-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 6,
        'method' => 'prompts/list',
    ]);
    $promptRes->assertOk();
    $promptNames = collect($promptRes->json('result.prompts'))->pluck('name')->all();
    expect($promptNames)->toContain('plan_destination_highlight', 'audit_destinations_readiness');
});
