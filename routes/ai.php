<?php

declare(strict_types=1);

use App\Http\Controllers\Mcp\ActionDispatchController;
use App\Http\Controllers\Mcp\OAuthRegisterController;
use App\Http\Controllers\Mcp\OpenApiController;
use App\Http\Middleware\McpAuthorizationMiddleware;
use App\Mcp\Servers\BaharsyahServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;
use Laravel\Passport\Http\Controllers\AccessTokenController;

/*
|--------------------------------------------------------------------------
| AI & MCP Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Model Context Protocol (MCP) servers,
| OpenAPI 3.1 specification endpoints for ChatGPT Actions, and execution endpoints.
|
*/

// Standard MCP OAuth discovery endpoints (RFC 8414 & RFC 9728)
Mcp::oauthRoutes();

// Deduplicated MCP OAuth Dynamic Client Registration (RFC 7591)
Route::post('/oauth/register', OAuthRegisterController::class)
    ->name('mcp.oauth.register');

// OpenAPI 3.1 Specification endpoints (for ChatGPT Custom GPTs & REST AI Agents)
Route::get('/openapi.json', OpenApiController::class)->name('mcp.openapi');
Route::get('/mcp/openapi.json', OpenApiController::class)->name('mcp.openapi.mcp');
Route::get('/api/openapi.json', OpenApiController::class)->name('mcp.openapi.api');

// Standard Model Context Protocol (JSON-RPC 2.0) Server Endpoints
Mcp::web('/mcp/admin', BaharsyahServer::class)
    ->middleware([McpAuthorizationMiddleware::class]);

// Compatibility alias for /mcp endpoint
Mcp::web('/mcp', BaharsyahServer::class)
    ->middleware([McpAuthorizationMiddleware::class]);

// Override GET on /mcp/admin and /mcp to serve OpenAPI spec directly for clients importing via URL
Route::get('/mcp/admin', OpenApiController::class)->name('mcp.admin.spec');
Route::get('/mcp', OpenApiController::class)->name('mcp.spec');

// OpenAPI Actions Dispatcher (Authenticated via OAuth 2.0 Bearer Token)
Route::post('/api/actions/{tool}', ActionDispatchController::class)
    ->middleware([McpAuthorizationMiddleware::class])
    ->name('mcp.actions.dispatch');

Route::post('/mcp/actions/{tool}', ActionDispatchController::class)
    ->middleware([McpAuthorizationMiddleware::class])
    ->name('mcp.actions.dispatch.alias');

Mcp::local('baharsyah', BaharsyahServer::class);

// Compatibility aliases for OAuth clients expecting root /authorize and /token
Route::get('/authorize', function (Request $request) {
    $queryString = $request->getQueryString();

    return redirect()->to(url('/oauth/authorize'.($queryString ? '?'.$queryString : '')));
})->name('oauth.authorize.alias');

Route::post('/token', [AccessTokenController::class, 'issueToken'])
    ->middleware('throttle')
    ->name('oauth.token.alias');
