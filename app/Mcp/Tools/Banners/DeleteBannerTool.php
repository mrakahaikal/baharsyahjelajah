<?php

namespace App\Mcp\Tools\Banners;

use App\Models\Banner;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_banner')]
#[Title('Delete Banner Entry')]
#[Description('Permanently delete a banner campaign by ID.')]
class DeleteBannerTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Banner numeric ID to delete.')->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        if (empty($id)) {
            return Response::error('The "id" parameter is required.');
        }

        $banner = Banner::find((int) $id);
        if (! $banner) {
            return Response::error("Banner with ID {$id} not found.");
        }

        $titleSummary = $banner->getTranslation('title', 'id', false) ?: $banner->getTranslation('title', 'en', false);
        $banner->delete();

        return Response::json([
            'message' => 'Banner berhasil dihapus.',
            'deleted_banner' => [
                'id' => (int) $id,
                'title' => $titleSummary,
            ],
        ]);
    }
}
