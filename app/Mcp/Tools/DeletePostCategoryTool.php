<?php

namespace App\Mcp\Tools;

use App\Models\PostCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Delete a post category record from the database by ID.')]
class DeletePostCategoryTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:post_categories,id',
        ]);

        $category = PostCategory::findOrFail($validated['id']);
        $categoryId = $category->id;
        $categoryName = $category->getTranslation('name', 'id');

        $category->delete();

        return Response::text((string) json_encode([
            'success' => true,
            'message' => "Post category '{$categoryName}' (ID: {$categoryId}) deleted successfully.",
            'deleted_id' => $categoryId,
        ], JSON_PRETTY_PRINT));
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The ID of the post category to delete.')->required(),
        ];
    }
}
