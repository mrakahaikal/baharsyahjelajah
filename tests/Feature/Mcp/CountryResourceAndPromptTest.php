<?php

use App\Mcp\Prompts\AuditCountryReadinessPrompt;
use App\Mcp\Prompts\CurateCountryGuidePrompt;
use App\Mcp\Resources\CountryCatalogResource;
use App\Mcp\Resources\FeaturedCountriesResource;
use App\Mcp\Servers\BaharsyahServer;
use App\Models\Country;
use App\Models\Destination;
use App\Models\VisaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Mcp\Server\Transport\FakeTransporter;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-static-agent-token-country-rp');

    $this->saudi = Country::create([
        'name' => ['id' => 'Arab Saudi', 'en' => 'Saudi Arabia'],
        'slug' => 'arab-saudi',
        'iso_alpha_2' => 'SA',
        'iso_alpha_3' => 'SAU',
        'currency_code' => 'SAR',
        'capital_city' => ['id' => 'Riyadh', 'en' => 'Riyadh'],
        'language' => ['id' => 'Arab', 'en' => 'Arabic'],
        'best_time_to_visit' => ['id' => 'November - Februari', 'en' => 'November - February'],
        'travel_requirements_summary' => ['id' => 'Paspor minimal 6 bulan dan Visa', 'en' => 'Passport 6 months and Visa'],
        'is_active' => true,
        'is_featured' => true,
        'sort_order' => 1,
    ]);
});

test('country resources and prompts are registered in baharsyah server', function () {
    $server = app(BaharsyahServer::class, ['transport' => new FakeTransporter]);
    $reflection = new ReflectionClass($server);

    $resources = $reflection->getProperty('resources')->getValue($server);
    expect($resources)->toContain(
        CountryCatalogResource::class,
        FeaturedCountriesResource::class,
    );

    $prompts = $reflection->getProperty('prompts')->getValue($server);
    expect($prompts)->toContain(
        CurateCountryGuidePrompt::class,
        AuditCountryReadinessPrompt::class,
    );
});

test('country catalog resource returns all active countries via jsonrpc', function () {
    $inactive = Country::create([
        'name' => ['id' => 'Negara Nonaktif'],
        'slug' => 'negara-nonaktif',
        'iso_alpha_2' => 'XX',
        'is_active' => false,
    ]);

    $response = $this->withToken('test-static-agent-token-country-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'resources/read',
        'params' => [
            'uri' => 'countries://catalog',
        ],
    ]);

    $response->assertOk();
    $result = $response->json('result.contents.0.text');
    $data = json_decode($result, true);

    expect($data['resource'])->toBe('countries://catalog')
        ->and($data['total_active_countries'])->toBe(1)
        ->and($data['countries'][0]['slug'])->toBe('arab-saudi')
        ->and($data['countries'][0]['iso_alpha_2'])->toBe('SA')
        ->and($data['countries'][0]['iso_alpha_3'])->toBe('SAU')
        ->and($data['countries'][0]['currency_code'])->toBe('SAR');
});

test('featured countries resource returns only featured countries via jsonrpc', function () {
    Country::create([
        'name' => ['id' => 'Turki', 'en' => 'Turkey'],
        'slug' => 'turki',
        'iso_alpha_2' => 'TR',
        'is_active' => true,
        'is_featured' => false,
    ]);

    $response = $this->withToken('test-static-agent-token-country-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'resources/read',
        'params' => [
            'uri' => 'countries://featured',
        ],
    ]);

    $response->assertOk();
    $result = $response->json('result.contents.0.text');
    $data = json_decode($result, true);

    expect($data['resource'])->toBe('countries://featured')
        ->and($data['total_featured_countries'])->toBe(1)
        ->and($data['featured_countries'][0]['slug'])->toBe('arab-saudi');
});

test('curate country guide prompt generates structured travel guidance for a specific country', function () {
    $dest = Destination::create([
        'name' => ['id' => 'Masjidil Haram'],
        'slug' => 'masjidil-haram',
        'is_active' => true,
    ]);
    $dest->countries()->attach($this->saudi->id);

    VisaService::create([
        'country_id' => $this->saudi->id,
        'name' => ['id' => 'Visa Umrah Elektronik'],
        'slug' => 'visa-umrah-elektronik',
        'visa_type' => ['id' => 'Elektronik'],
        'is_active' => true,
    ]);

    $response = $this->withToken('test-static-agent-token-country-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'prompts/get',
        'params' => [
            'name' => 'curate_country_guide',
            'arguments' => [
                'country_identifier' => 'SA',
                'focus_theme' => 'Ibadah Umrah & Ziarah Bersejarah',
            ],
        ],
    ]);

    $response->assertOk();
    $text = $response->json('result.messages.0.content.text');

    expect($text)->toContain('Arab Saudi')
        ->and($text)->toContain('Masjidil Haram')
        ->and($text)->toContain('Visa Umrah Elektronik')
        ->and($text)->toContain('Ibadah Umrah & Ziarah Bersejarah')
        ->and($text)->toContain('SAR');
});

test('audit country readiness prompt returns checklist of countries', function () {
    $response = $this->withToken('test-static-agent-token-country-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 4,
        'method' => 'prompts/get',
        'params' => [
            'name' => 'audit_country_readiness',
            'arguments' => [],
        ],
    ]);

    $response->assertOk();
    $text = $response->json('result.messages.0.content.text');

    expect($text)->toContain('Arab Saudi')
        ->and($text)->toContain('SA/SAU')
        ->and($text)->toContain('Standarisasi Kode ISO');
});

test('country resources and prompts appear in discovery listing', function () {
    // 1. resources/list
    $res = $this->withToken('test-static-agent-token-country-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 5,
        'method' => 'resources/list',
    ]);
    $res->assertOk();
    $uris = collect($res->json('result.resources'))->pluck('uri');
    expect($uris)->toContain('countries://catalog', 'countries://featured');

    // 2. prompts/list
    $promptRes = $this->withToken('test-static-agent-token-country-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 6,
        'method' => 'prompts/list',
    ]);
    $promptRes->assertOk();
    $names = collect($promptRes->json('result.prompts'))->pluck('name');
    expect($names)->toContain('curate_country_guide', 'audit_country_readiness');
});
