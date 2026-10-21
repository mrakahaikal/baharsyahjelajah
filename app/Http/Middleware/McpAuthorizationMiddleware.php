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

        $isActionRoute = $request->route('tool') !== null;

        if (empty($token)) {
            $data = $isActionRoute
                ? ['error' => 'Unauthorized: Invalid or missing OAuth bearer token.']
                : [
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => -32000,
                        'message' => 'Unauthorized: Invalid or missing MCP bearer token.',
                    ],
                    'id' => $request->input('id'),
                ];

            return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
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
            $data = $isActionRoute
                ? ['error' => 'Unauthorized: Invalid or expired OAuth bearer token.']
                : [
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => -32000,
                        'message' => 'Unauthorized: Invalid or missing MCP bearer token.',
                    ],
                    'id' => $request->input('id'),
                ];

            return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
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
        $routeTool = $request->route('tool');
        $params = $request->input('params', []);
        $toolName = ! empty($routeTool) ? (string) $routeTool : (is_array($params) ? ($params['name'] ?? null) : null);
        $method = ! empty($routeTool) ? 'tools/call' : (string) $request->input('method', '');

        $requiredScope = $this->resolveRequiredScope($method, $toolName);

        // 'mcp:admin' or 'mcp:use' has full access to MCP tools and methods
        if (! $accessToken->can('mcp:admin') && ! $accessToken->can('mcp:use') && ! $accessToken->can($requiredScope)) {
            $target = $toolName ? "tool '{$toolName}'" : "method '{$method}'";
            $message = "Forbidden: OAuth token lacks required scope '{$requiredScope}' for {$target}.";

            $data = $isActionRoute
                ? ['error' => $message]
                : [
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => -32003,
                        'message' => $message,
                    ],
                    'id' => $request->input('id'),
                ];

            return new JsonResponse($data, Response::HTTP_FORBIDDEN);
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

            if (str_starts_with($toolName, 'create_') || str_starts_with($toolName, 'update_') || str_starts_with($toolName, 'manage_')) {
                return 'mcp:write';
            }

            return 'mcp:read';
        }

        return 'mcp:read';
    }
}
