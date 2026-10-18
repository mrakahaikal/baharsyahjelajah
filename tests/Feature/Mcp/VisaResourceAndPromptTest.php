<?php

use App\Enums\VisaEntryType;
use App\Enums\VisaItemType;
use App\Mcp\Prompts\AuditVisaServicesReadinessPrompt;
use App\Mcp\Prompts\ConsultVisaRequirementsPrompt;
use App\Mcp\Resources\CurrencyRatesResource;
use App\Mcp\Resources\VisaCatalogResource;
use App\Mcp\Resources\VisaRequirementsSummaryResource;
use App\Mcp\Servers\BaharsyahServer;
use App\Models\Country;
use App\Models\CurrencyRate;
use App\Models\VisaService;
use App\Models\VisaServiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Mcp\Server\Transport\FakeTransporter;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-static-agent-token-visa-rp');

    $this->country = Country::create([
        'name' => ['id' => 'Arab Saudi', 'en' => 'Saudi Arabia'],
        'slug' => 'arab-saudi',
        'iso_alpha_2' => 'SA',
        'iso_alpha_3' => 'SAU',
        'currency_code' => 'SAR',
    ]);

    $this->visa = VisaService::create([
        'country_id' => $this->country->id,
        'name' => ['id' => 'Visa Turis eVisa', 'en' => 'Tourist eVisa'],
        'slug' => 'visa-turis-evisa',
        'visa_type' => ['id' => 'Elektronik', 'en' => 'Electronic'],
        'entry_type' => VisaEntryType::Multiple,
        'processing_days_min' => 1,
        'processing_days_max' => 3,
        'currency' => 'SAR',
        'price' => 350.00,
        'price_idr' => 1500000,
        'is_active' => true,
        'is_featured' => true,
    ]);

    VisaServiceItem::create([
        'visa_service_id' => $this->visa->id,
        'type' => VisaItemType::Requirement,
        'content' => ['id' => 'Paspor masih berlaku minimal 6 bulan'],
        'details' => ['id' => 'Scan berwarna halaman data paspor'],
        'is_mandatory' => true,
    ]);

    VisaServiceItem::create([
        'visa_service_id' => $this->visa->id,
        'type' => VisaItemType::Term,
        'content' => ['id' => 'Biaya visa tidak dapat dikembalikan'],
    ]);

    // Dummy currency rate
    CurrencyRate::updateRate('SAR', 0.00024);
});

test('currency and visa resources and prompts are registered in server', function () {
    $server = app(BaharsyahServer::class, ['transport' => new FakeTransporter]);
    $reflection = new ReflectionClass($server);

    $resources = $reflection->getProperty('resources')->getValue($server);
    expect($resources)->toContain(
        CurrencyRatesResource::class,
        VisaCatalogResource::class,
        VisaRequirementsSummaryResource::class,
    );

    $prompts = $reflection->getProperty('prompts')->getValue($server);
    expect($prompts)->toContain(
        ConsultVisaRequirementsPrompt::class,
        AuditVisaServicesReadinessPrompt::class,
    );
});

test('currency rates resource returns exchange rates via jsonrpc', function () {
    $response = $this->withToken('test-static-agent-token-visa-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'resources/read',
        'params' => [
            'uri' => 'currencies://rates',
        ],
    ]);

    $response->assertOk();
    $result = $response->json('result.contents.0.text');
    $data = json_decode($result, true);

    expect($data['resource'])->toBe('currencies://rates')
        ->and($data['base_currency'])->toBe('IDR')
        ->and($data['rates'])->toHaveKey('SAR');
});

test('visa catalog resource returns all active visas via jsonrpc', function () {
    $response = $this->withToken('test-static-agent-token-visa-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'resources/read',
        'params' => [
            'uri' => 'visas://catalog',
        ],
    ]);

    $response->assertOk();
    $result = $response->json('result.contents.0.text');
    $data = json_decode($result, true);

    expect($data['resource'])->toBe('visas://catalog')
        ->and($data['total_active_services'])->toBe(1)
        ->and($data['services'][0]['slug'])->toBe('visa-turis-evisa')
        ->and($data['services'][0]['country']['name'])->toBe('Arab Saudi');
});

test('visa requirements summary resource returns checklist per country', function () {
    $response = $this->withToken('test-static-agent-token-visa-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'resources/read',
        'params' => [
            'uri' => 'visas://requirements-summary',
        ],
    ]);

    $response->assertOk();
    $result = $response->json('result.contents.0.text');
    $data = json_decode($result, true);

    expect($data['resource'])->toBe('visas://requirements-summary')
        ->and($data['requirements_by_visa'][0]['requirements_checklist'][0]['item'])->toBe('Paspor masih berlaku minimal 6 bulan');
});

test('consult visa requirements prompt generates personalized advisory', function () {
    $response = $this->withToken('test-static-agent-token-visa-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 4,
        'method' => 'prompts/get',
        'params' => [
            'name' => 'consult_visa_requirements',
            'arguments' => [
                'country_identifier' => 'SA',
                'applicant_profile' => 'Keluarga 4 orang',
                'travel_purpose' => 'Umrah Mandiri',
            ],
        ],
    ]);

    $response->assertOk();
    $text = $response->json('result.messages.0.content.text');

    expect($text)->toContain('Visa Turis eVisa')
        ->and($text)->toContain('Paspor masih berlaku minimal 6 bulan')
        ->and($text)->toContain('Keluarga 4 orang')
        ->and($text)->toContain('Umrah Mandiri');
});

test('audit visa services readiness prompt returns validation checklist', function () {
    $response = $this->withToken('test-static-agent-token-visa-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 5,
        'method' => 'prompts/get',
        'params' => [
            'name' => 'audit_visa_services_readiness',
            'arguments' => [],
        ],
    ]);

    $response->assertOk();
    $text = $response->json('result.messages.0.content.text');

    expect($text)->toContain('Visa Turis eVisa')
        ->and($text)->toContain('Arab Saudi')
        ->and($text)->toContain('Kelengkapan Butir Persyaratan');
});

test('currency and visa items appear in discovery listing', function () {
    // 1. resources/list
    $res = $this->withToken('test-static-agent-token-visa-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 6,
        'method' => 'resources/list',
    ]);
    $res->assertOk();
    $uris = collect($res->json('result.resources'))->pluck('uri');
    expect($uris)->toContain('currencies://rates', 'visas://catalog', 'visas://requirements-summary');

    // 2. prompts/list
    $promptRes = $this->withToken('test-static-agent-token-visa-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 7,
        'method' => 'prompts/list',
    ]);
    $promptRes->assertOk();
    $names = collect($promptRes->json('result.prompts'))->pluck('name');
    expect($names)->toContain('consult_visa_requirements', 'audit_visa_services_readiness');
});
