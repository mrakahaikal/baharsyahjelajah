<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Articles;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('publish_article')]
#[Title('Publish Article')]
#[Description('Quickly publish, schedule, unpublish (to draft), or archive an article by ID or slug.')]
class PublishArticleTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The numeric ID of the article.'),
            'slug' => $schema->string()->description('The slug of the article (used if "id" is omitted).'),
            'action' => $schema->string()->description('The action to perform: "publish" (make live), "unpublish" (revert to draft), or "archive". Default is "publish".')->enum(['publish', 'unpublish', 'archive']),
            'published_at' => $schema->string()->description('Optional ISO publication datetime (e.g. "2026-10-20T08:00:00Z"). For future scheduled posts or backdating. If omitted for "publish", current time is used.'),
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

        $action = (string) $request->get('action', 'publish');
        if (! in_array($action, ['publish', 'unpublish', 'archive'], true)) {
            $action = 'publish';
        }

        switch ($action) {
            case 'publish':
                $rawPublishedAt = $request->get('published_at');
                if (! empty($rawPublishedAt)) {
                    try {
                        $publishedAt = Carbon::parse($rawPublishedAt);
                    } catch (\Throwable) {
                        return Response::error("Invalid 'published_at' datetime format: {$rawPublishedAt}");
                    }
                } else {
                    $publishedAt = $post->published_at ?? Carbon::now();
                }

                $post->update([
                    'status' => 'published',
                    'published_at' => $publishedAt,
                ]);

                $message = $publishedAt->isFuture()
                    ? "Article scheduled to publish on {$publishedAt->toIso8601String()}."
                    : 'Article published successfully.';
                break;

            case 'unpublish':
                $post->update([
                    'status' => 'draft',
                ]);
                $message = 'Article unpublished and reverted to draft status.';
                break;

            case 'archive':
                $post->update([
                    'status' => 'archived',
                ]);
                $message = 'Article archived successfully.';
                break;
        }

        return Response::json([
            'message' => $message,
            'article' => [
                'id' => $post->id,
                'title' => $post->getTranslation('title', 'id', false),
                'slug' => $post->localizedSlug('id'),
                'status' => $post->status,
                'published_at' => $post->published_at?->toIso8601String(),
                'updated_at' => $post->updated_at?->toIso8601String(),
            ],
        ]);
    }
}
