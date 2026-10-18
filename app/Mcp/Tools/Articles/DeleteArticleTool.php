<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Articles;

use App\Models\Post;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_article')]
#[Title('Delete Article')]
#[Description('Delete an article safely. Published articles require force=true confirmation to prevent accidental loss.')]
class DeleteArticleTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The numeric ID of the article to delete.'),
            'slug' => $schema->string()->description('The slug of the article to delete (used if "id" is omitted).'),
            'force' => $schema->boolean()->description('Force delete even if the article is currently "published". Default is false.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $slug = $request->get('slug');

        if (empty($id) && empty($slug)) {
            return Response::error('Either "id" or "slug" must be provided.');
        }

        /** @var Post|null $post */
        $post = ! empty($id)
            ? Post::find((int) $id)
            : Post::whereLocalizedSlug((string) $slug)->first();

        if (! $post) {
            $identifier = ! empty($id) ? "ID {$id}" : "slug '{$slug}'";

            return Response::error("Article with {$identifier} not found.");
        }

        $force = (bool) $request->get('force', false);

        if ($post->status === 'published' && ! $force) {
            return Response::error(
                "Article '{$post->getTranslation('title', 'id', false)}' is currently published. Set 'force' => true to confirm permanent deletion."
            );
        }

        $title = $post->getTranslation('title', 'id', false);
        $postId = $post->id;

        $post->delete();

        return Response::json([
            'message' => "Article '{$title}' (ID {$postId}) deleted successfully.",
            'deleted_id' => $postId,
        ]);
    }
}
