<?php

declare(strict_types=1);

namespace App\Mcp\Tools\VisaServices;

use App\Models\VisaService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_visa_service')]
#[Title('Delete Visa Service')]
#[Description('Delete (soft-delete or force-delete) a visa service. Scope: mcp:admin.')]
class DeleteVisaServiceTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The numeric ID of the visa service to delete.'),
            'slug' => $schema->string()->description('The URL slug of the visa service to delete (used if "id" is omitted).'),
            'force' => $schema->boolean()->description('Force permanent deletion instead of soft-delete. Default is false.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $slug = $request->get('slug');

        if (empty($id) && empty($slug)) {
            return Response::error('Either "id" or "slug" must be provided.');
        }

        /** @var VisaService|null $service */
        $service = ! empty($id)
            ? VisaService::withTrashed()->find((int) $id)
            : VisaService::withTrashed()->where('slug', (string) $slug)->first();

        if (! $service) {
            $identifier = ! empty($id) ? "ID {$id}" : "slug '{$slug}'";

            return Response::error("Visa service with {$identifier} not found.");
        }

        $serviceId = $service->id;
        $serviceName = $service->getTranslation('name', 'id', false);
        $force = (bool) $request->get('force', false);

        DB::transaction(function () use ($service, $force): void {
            if ($force) {
                $service->items()->delete();
                $service->forceDelete();
            } else {
                $service->delete();
            }
        });

        $mode = $force ? 'permanently deleted' : 'soft-deleted';

        return Response::json([
            'message' => "Visa service '{$serviceName}' (ID {$serviceId}) {$mode} successfully.",
            'deleted_id' => $serviceId,
            'force' => $force,
        ]);
    }
}
