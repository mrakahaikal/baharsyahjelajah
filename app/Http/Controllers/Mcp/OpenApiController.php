<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mcp;

use App\Services\OpenApi\OpenApiSpecificationGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpenApiController
{
    public function __invoke(Request $request, OpenApiSpecificationGenerator $generator): JsonResponse
    {
        $domain = $request->query('domain');
        $limit = $request->query('limit', 30);

        if ($limit === 'all' || $limit === '0') {
            $limit = 'all';
        } else {
            $limit = is_numeric($limit) ? (int) $limit : 30;
        }

        $spec = $generator->generate(
            domain: is_string($domain) ? $domain : null,
            limit: $limit,
        );

        return response()->json($spec, 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type, Accept',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
