<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mcp;

use Illuminate\Container\Container;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Mcp\Server\Http\Controllers\OAuthRegisterController as BaseOAuthRegisterController;
use Laravel\Mcp\Server\Registrar;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Throwable;

class OAuthRegisterController extends BaseOAuthRegisterController
{
    /**
     * Register a new OAuth client or reuse an existing client with deduplication.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'client_name' => ['nullable', 'string', 'min:1', 'max:255'],
            'name' => ['nullable', 'string', 'min:1', 'max:255'],
            'client_id' => ['nullable', 'string'],
            'redirect_uris' => ['required', 'array', 'min:1'],
            'redirect_uris.*' => ['bail', 'required', 'string', function (string $attribute, $value, $fail): void {
                if (! $this->isValidRedirectUri($value)) {
                    $fail($attribute.' is not a valid URL.');

                    return;
                }

                if (! in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true)) {
                    return;
                }

                if (in_array('*', config('mcp.redirect_domains', []), true)) {
                    return;
                }

                if ($this->hasLocalhostDomain() && $this->isLocalhostUrl($value)) {
                    return;
                }

                if (! Str::startsWith($value, $this->allowedDomains())) {
                    $fail($attribute.' is not a permitted redirect domain.');
                }
            }],
            'logo_uri' => ['nullable', 'string', 'url:http,https', 'max:2048'],
            'client_uri' => ['nullable', 'string', 'url:http,https', 'max:2048'],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $redirectError = $errors->first('redirect_uris*');

            return response()->json([
                'error' => $redirectError !== '' ? 'invalid_redirect_uri' : 'invalid_client_metadata',
                'error_description' => $redirectError !== '' ? $redirectError : $errors->first(),
            ], 400);
        }

        $validated = $validator->validated();

        if (! class_exists(ClientRepository::class)) {
            return response()->json([
                'error' => 'server_error',
                'error_description' => 'OAuth support (Passport) is not installed.',
            ], 500);
        }

        /** @var ClientRepository $clients */
        $clients = Container::getInstance()->make(ClientRepository::class);

        $clientName = $this->resolveClientName($validated);
        $redirectUris = $validated['redirect_uris'];

        try {
            // 1. Check if client already exists (Deduplication)
            $client = $this->findExistingClient($validated, $clientName, $redirectUris);

            if ($client !== null) {
                // Ensure redirect_uris is up to date (e.g. if local ephemeral port changed)
                $currentUris = (array) ($client->redirect_uris ?? []);
                if (array_values($currentUris) !== array_values($redirectUris)) {
                    $client->forceFill(['redirect_uris' => $redirectUris])->save();
                }

                $this->grantMcpScope($client);
                $metadata = $this->persistClientMetadata($client, $validated);

                return response()->json([
                    'client_id' => (string) $client->id,
                    'grant_types' => $client->grant_types,
                    'response_types' => ['code'],
                    'redirect_uris' => $client->redirect_uris,
                    'scope' => Registrar::OAUTH_SCOPE,
                    'token_endpoint_auth_method' => 'none',
                    ...$metadata,
                ], 200);
            }

            // 2. If no existing client, create a new public client
            $client = $clients->createAuthorizationCodeGrantClient(
                name: $clientName,
                redirectUris: $redirectUris,
                confidential: false,
                enableDeviceFlow: false,
            );

            $this->grantMcpScope($client);
            $metadata = $this->persistClientMetadata($client, $validated);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'error' => 'server_error',
                'error_description' => 'The client could not be registered.',
            ], 500);
        }

        return response()->json([
            'client_id' => (string) $client->id,
            'grant_types' => $client->grant_types,
            'response_types' => ['code'],
            'redirect_uris' => $client->redirect_uris,
            'scope' => Registrar::OAUTH_SCOPE,
            'token_endpoint_auth_method' => 'none',
            ...$metadata,
        ], 201);
    }

    /**
     * Find an existing unrevoked client that matches client_id or client_name and redirect domain pattern.
     *
     * @param  array<string, mixed>  $validated
     * @param  array<int, string>  $redirectUris
     */
    protected function findExistingClient(array $validated, string $clientName, array $redirectUris): ?Client
    {
        // A. Match by explicit client_id if provided
        if (! empty($validated['client_id'])) {
            /** @var Client|null $client */
            $client = Client::query()
                ->where('revoked', false)
                ->find($validated['client_id']);

            if ($client !== null) {
                return $client;
            }
        }

        // B. Match by name and exact redirect URIs
        $exactMatch = Client::query()
            ->where('revoked', false)
            ->where('name', $clientName)
            ->whereJsonContains('redirect_uris', $redirectUris[0] ?? '')
            ->latest('created_at')
            ->first();

        if ($exactMatch !== null) {
            return $exactMatch;
        }

        // C. Match by name for desktop/local native clients (where port or path might shift)
        $firstUri = (string) ($redirectUris[0] ?? '');
        $scheme = parse_url($firstUri, PHP_URL_SCHEME);
        $isLocalOrCustom = $this->isLocalhostUrl($firstUri) || in_array($scheme, config('mcp.custom_schemes', []), true);

        if ($isLocalOrCustom) {
            return Client::query()
                ->where('revoked', false)
                ->where('name', $clientName)
                ->latest('created_at')
                ->first();
        }

        // D. Fallback: match by client name if active client exists with that name
        return Client::query()
            ->where('revoked', false)
            ->where('name', $clientName)
            ->latest('created_at')
            ->first();
    }
}
