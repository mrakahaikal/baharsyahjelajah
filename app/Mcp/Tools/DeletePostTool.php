<?php

namespace App\Mcp\Tools;

use App\Models\Post;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Delete a blog post record from the database by ID.')]
class DeletePostTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:posts,id',
        ]);

        $post = Post::findOrFail($validated['id']);
        $postId = $post->id;
        $postTitle = $post->getTranslation('title', 'id');

        $post->delete();

        return Response::text((string) json_encode([
            'success' => true,
            'message' => "Post '{$postTitle}' (ID: {$postId}) deleted successfully.",
            'deleted_id' => $postId,
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
            'id' => $schema->integer()->description('The ID of the post to delete.')->required(),
        ];
    }
}
