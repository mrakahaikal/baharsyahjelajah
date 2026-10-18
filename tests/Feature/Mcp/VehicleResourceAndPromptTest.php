<?php

use App\Enums\VehicleCategory;
use App\Enums\VehicleRentalTermType;
use App\Mcp\Prompts\AuditFleetReadinessPrompt;
use App\Mcp\Prompts\RecommendVehicleForTripPrompt;
use App\Mcp\Resources\VehicleFleetCatalogResource;
use App\Mcp\Resources\VehicleRentalTermsResource;
use App\Mcp\Servers\BaharsyahServer;
use App\Models\Vehicle;
use App\Models\VehicleRentalTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Mcp\Server\Transport\FakeTransporter;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mcp.auth_token', 'test-static-agent-token-vehicle-rp');

    $this->vehicle = Vehicle::create([
        'catalog_code' => 'ALPHARD-TRANS-01',
        'name' => ['id' => 'Toyota Alphard Transformer', 'en' => 'Toyota Alphard Transformer'],
        'slug' => ['id' => 'toyota-alphard-transformer'],
        'category' => VehicleCategory::Car,
        'brand' => 'Toyota',
        'model' => 'Alphard G',
        'year' => 2022,
        'capacity_pax' => 6,
        'capacity_luggage' => 4,
        'transmission' => 'automatic',
        'has_ac' => true,
        'has_wifi' => true,
        'price_per_day_idr' => 2500000,
        'overtime_rate_idr' => 250000,
        'is_active' => true,
        'is_featured' => true,
    ]);

    VehicleRentalTerm::create([
        'code' => 'TERM-INCLUDED-CAR',
        'type' => VehicleRentalTermType::Included,
        'vehicle_category' => VehicleCategory::Car,
        'title' => ['id' => 'Termasuk Driver & BBM'],
        'content' => ['id' => 'Sewa sudah mencakup biaya driver profesional dan bahan bakar untuk rute dalam kota.'],
        'is_active' => true,
    ]);
});

test('vehicle resources and prompts are registered in server', function () {
    $server = app(BaharsyahServer::class, ['transport' => new FakeTransporter]);
    $reflection = new ReflectionClass($server);

    $resources = $reflection->getProperty('resources')->getValue($server);
    expect($resources)->toContain(
        VehicleFleetCatalogResource::class,
        VehicleRentalTermsResource::class,
    );

    $prompts = $reflection->getProperty('prompts')->getValue($server);
    expect($prompts)->toContain(
        RecommendVehicleForTripPrompt::class,
        AuditFleetReadinessPrompt::class,
    );
});

test('vehicle fleet catalog resource returns active vehicles via jsonrpc', function () {
    $response = $this->withToken('test-static-agent-token-vehicle-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'resources/read',
        'params' => [
            'uri' => 'vehicles://fleet-catalog',
        ],
    ]);

    $response->assertOk();
    $result = $response->json('result.contents.0.text');
    $data = json_decode($result, true);

    expect($data['resource'])->toBe('vehicles://fleet-catalog')
        ->and($data['total_active_fleet'])->toBe(1)
        ->and($data['fleet'][0]['catalog_code'])->toBe('ALPHARD-TRANS-01')
        ->and($data['fleet'][0]['specifications']['capacity_pax'])->toBe(6);
});

test('vehicle rental terms resource returns terms grouped by category via jsonrpc', function () {
    $response = $this->withToken('test-static-agent-token-vehicle-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'resources/read',
        'params' => [
            'uri' => 'vehicles://rental-terms',
        ],
    ]);

    $response->assertOk();
    $result = $response->json('result.contents.0.text');
    $data = json_decode($result, true);

    expect($data['resource'])->toBe('vehicles://rental-terms')
        ->and($data['categories_count'])->toBe(1)
        ->and($data['terms_by_category'][0]['category'])->toBe('car')
        ->and($data['terms_by_category'][0]['terms'][0]['title']['id'])->toBe('Termasuk Driver & BBM');
});

test('recommend vehicle for trip prompt generates tailored advisory', function () {
    $response = $this->withToken('test-static-agent-token-vehicle-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'prompts/get',
        'params' => [
            'name' => 'recommend_vehicle_for_trip',
            'arguments' => [
                'passenger_count' => 5,
                'luggage_count' => 3,
                'destination_or_route' => 'Balikpapan ke IKN Nusantara',
                'duration_days' => 2,
            ],
        ],
    ]);

    $response->assertOk();
    $text = $response->json('result.messages.0.content.text');

    expect($text)->toContain('Toyota Alphard Transformer')
        ->and($text)->toContain('Balikpapan ke IKN Nusantara')
        ->and($text)->toContain('5 Orang')
        ->and($text)->toContain('2 Hari');
});

test('audit fleet readiness prompt returns validation checklist', function () {
    $response = $this->withToken('test-static-agent-token-vehicle-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 4,
        'method' => 'prompts/get',
        'params' => [
            'name' => 'audit_fleet_readiness',
            'arguments' => [],
        ],
    ]);

    $response->assertOk();
    $text = $response->json('result.messages.0.content.text');

    expect($text)->toContain('ALPHARD-TRANS-01')
        ->and($text)->toContain('Toyota Alphard Transformer')
        ->and($text)->toContain('Kepastian Tarif Sewa & Overtime');
});

test('vehicle items appear in discovery listing', function () {
    // 1. resources/list
    $res = $this->withToken('test-static-agent-token-vehicle-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 5,
        'method' => 'resources/list',
    ]);
    $res->assertOk();
    $uris = collect($res->json('result.resources'))->pluck('uri');
    expect($uris)->toContain('vehicles://fleet-catalog', 'vehicles://rental-terms');

    // 2. prompts/list
    $promptRes = $this->withToken('test-static-agent-token-vehicle-rp')->postJson('/mcp/admin', [
        'jsonrpc' => '2.0',
        'id' => 6,
        'method' => 'prompts/list',
    ]);
    $promptRes->assertOk();
    $names = collect($promptRes->json('result.prompts'))->pluck('name');
    expect($names)->toContain('recommend_vehicle_for_trip', 'audit_fleet_readiness');
});
