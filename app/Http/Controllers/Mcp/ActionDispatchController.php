<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mcp;

use App\Mcp\Servers\BaharsyahServer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Mcp\Request as McpRequest;
use Laravel\Mcp\Response as McpResponse;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Content\Text;
use Laravel\Mcp\Server\Tool;
use ReflectionClass;
use Throwable;

class ActionDispatchController
{
    /**
     * Cache for resolved tool class names.
     *
     * @var array<string, class-string<Tool>>|null
     */
    protected static ?array $toolMap = null;

    public function __invoke(Request $request, string $tool): JsonResponse
    {
        $toolClass = $this->resolveToolClass($tool);

        if ($toolClass === null) {
            return response()->json([
                'error' => "Action tool '{$tool}' is not found or not registered on this server.",
            ], 404);
        }

        /** @var Tool $toolInstance */
        $toolInstance = app($toolClass);

        $mcpRequest = new McpRequest(
            arguments: $request->all(),
        );

        try {
            /** @var McpResponse $response */
            $response = $toolInstance->handle($mcpRequest);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }

        $content = $response->content();
        $isError = $response->isError();

        if ($content instanceof Text) {
            $rawText = $content->toArray()['text'] ?? '';
            $decoded = json_decode($rawText, true);

            $payload = (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                ? $decoded
                : ['result' => $rawText];

            return response()->json($payload, $isError ? 400 : 200, [
                'Access-Control-Allow-Origin' => '*',
            ]);
        }

        return response()->json($content->toArray(), $isError ? 400 : 200, [
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    /**
     * Resolve the Tool class string matching the tool name.
     *
     * @return class-string<Tool>|null
     */
    protected function resolveToolClass(string $toolName): ?string
    {
        if (static::$toolMap === null) {
            $map = [];
            $toolClasses = BaharsyahServer::registeredToolClasses();

            foreach ($toolClasses as $class) {
                $reflection = new ReflectionClass($class);
                $attributes = $reflection->getAttributes(Name::class);

                if (! empty($attributes)) {
                    $name = (string) ($attributes[0]->getArguments()[0] ?? '');
                    if ($name !== '') {
                        $map[$name] = $class;

                        continue;
                    }
                }

                /** @var Tool $instance */
                $instance = app($class);
                $map[$instance->name()] = $class;
            }

            static::$toolMap = $map;
        }

        return static::$toolMap[$toolName] ?? null;
    }
}
