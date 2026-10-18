<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Passport\AccessToken;
use League\OAuth2\Server\ResourceServer;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response;

class McpAuthorizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (empty($token)) {
            return new JsonResponse([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32000,
                    'message' => 'Unauthorized: Invalid or missing MCP bearer token.',
                ],
                'id' => $request->input('id'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        // 1. Static Bearer Token fallback (Local AI Agent / developer CLI)
        $expectedToken = config('mcp.auth_token');
        if (! empty($expectedToken) && hash_equals((string) $expectedToken, $token)) {
            return $next($request);
        }

        // 2. Validate OAuth 2.0 Access Token (Passport / RFC 6749)
        try {
            /** @var ResourceServer $resourceServer */
            $resourceServer = app(ResourceServer::class);

            $psrRequest = (new PsrHttpFactory)->createRequest($request);
            $validatedPsr = $resourceServer->validateAuthenticatedRequest($psrRequest);
            $accessToken = AccessToken::fromPsrRequest($validatedPsr);
        } catch (\Throwable) {
            return new JsonResponse([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32000,
                    'message' => 'Unauthorized: Invalid or missing MCP bearer token.',
                ],
                'id' => $request->input('id'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Resolve authenticated user if present on the token
        $userId = $validatedPsr->getAttribute('oauth_user_id');
        if (! empty($userId)) {
            $user = User::find($userId);
            if ($user) {
                $user->withAccessToken($accessToken);
                $request->setUserResolver(fn () => $user);
            }
        }

        // 3. Verify granular scope for the requested tool or method
        $method = (string) $request->input('method', '');
        $params = $request->input('params', []);
        $toolName = is_array($params) ? ($params['name'] ?? null) : null;

        $requiredScope = $this->resolveRequiredScope($method, $toolName);

        // 'mcp:admin' or 'mcp:use' has full access to MCP tools and methods
        if (! $accessToken->can('mcp:admin') && ! $accessToken->can('mcp:use') && ! $accessToken->can($requiredScope)) {
            $target = $toolName ? "tool '{$toolName}'" : "method '{$method}'";

            return new JsonResponse([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32003,
                    'message' => "Forbidden: OAuth token lacks required scope '{$requiredScope}' for {$target}.",
                ],
                'id' => $request->input('id'),
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }

    /**
     * Resolve the required OAuth scope based on JSON-RPC method and MCP tool name.
     */
    protected function resolveRequiredScope(string $method, ?string $toolName): string
    {
        if ($method === 'tools/call' && ! empty($toolName)) {
            if (str_starts_with($toolName, 'delete_') || str_starts_with($toolName, 'merge_')) {
                return 'mcp:admin';
            }

            if (str_starts_with($toolName, 'publish_')) {
                return 'mcp:publish';
            }

            if (str_starts_with($toolName, 'create_') || str_starts_with($toolName, 'update_')) {
                return 'mcp:write';
            }

            return 'mcp:read';
        }

        return 'mcp:read';
    }
}
