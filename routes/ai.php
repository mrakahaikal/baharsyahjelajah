<?php

declare(strict_types=1);

use App\Http\Controllers\Mcp\OAuthRegisterController;
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
| Here is where you can register Model Context Protocol (MCP) servers and
| AI-related endpoints for external and local AI agents.
|
*/

// Standard MCP OAuth discovery endpoints (RFC 8414 & RFC 9728)
Mcp::oauthRoutes();

// Deduplicated MCP OAuth Dynamic Client Registration (RFC 7591)
Route::post('/oauth/register', OAuthRegisterController::class)
    ->name('mcp.oauth.register');

Mcp::web('/mcp/admin', BaharsyahServer::class)
    ->middleware([McpAuthorizationMiddleware::class]);

Mcp::local('baharsyah', BaharsyahServer::class);

// Compatibility aliases for OAuth clients expecting root /authorize and /token
Route::get('/authorize', function (Request $request) {
    $queryString = $request->getQueryString();

    return redirect()->to(url('/oauth/authorize'.($queryString ? '?'.$queryString : '')));
})->name('oauth.authorize.alias');

Route::post('/token', [AccessTokenController::class, 'issueToken'])
    ->middleware('throttle')
    ->name('oauth.token.alias');
