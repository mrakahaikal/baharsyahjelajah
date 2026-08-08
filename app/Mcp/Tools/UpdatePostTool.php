<?php

namespace App\Mcp\Tools;

use App\Models\Post;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Update an existing blog post record in the database.')]
class UpdatePostTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:posts,id',
            'post_category_id' => 'nullable|integer|exists:post_categories,id',
            'user_id' => 'nullable|integer|exists:users,id',
            'title' => 'nullable|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'title_ms' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'slug_en' => 'nullable|string|max:255',
            'slug_ms' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string',
            'excerpt_en' => 'nullable|string',
            'excerpt_ms' => 'nullable|string',
            'content' => 'nullable|string',
            'content_en' => 'nullable|string',
            'content_ms' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'status' => 'nullable|string|in:draft,published',
            'published_at' => 'nullable|date',
        ]);

        $post = Post::findOrFail($validated['id']);

        $title = $post->getTranslations('title');
        if (isset($validated['title'])) {
            $title['id'] = $validated['title'];
        }
        if (isset($validated['title_en'])) {
            $title['en'] = $validated['title_en'];
        }
        if (isset($validated['title_ms'])) {
            $title['ms'] = $validated['title_ms'];
        }

        $slug = $post->getTranslations('slug');
        if (isset($validated['slug'])) {
            $slug['id'] = Str::slug($validated['slug']);
        } elseif (isset($validated['title'])) {
            $slug['id'] = Str::slug($validated['title']);
        }

        if (isset($validated['slug_en'])) {
            $slug['en'] = Str::slug($validated['slug_en']);
        } elseif (isset($validated['title_en'])) {
            $slug['en'] = Str::slug($validated['title_en']);
        }

        if (isset($validated['slug_ms'])) {
            $slug['ms'] = Str::slug($validated['slug_ms']);
        } elseif (isset($validated['title_ms'])) {
            $slug['ms'] = Str::slug($validated['title_ms']);
        }

        $excerpt = $post->getTranslations('excerpt');
        if (array_key_exists('excerpt', $validated)) {
            $excerpt['id'] = $validated['excerpt'];
        }
        if (array_key_exists('excerpt_en', $validated)) {
            $excerpt['en'] = $validated['excerpt_en'];
        }
        if (array_key_exists('excerpt_ms', $validated)) {
            $excerpt['ms'] = $validated['excerpt_ms'];
        }

        $content = $post->getTranslations('content');
        if (array_key_exists('content', $validated)) {
            $content['id'] = $validated['content'];
        }
        if (array_key_exists('content_en', $validated)) {
            $content['en'] = $validated['content_en'];
        }
        if (array_key_exists('content_ms', $validated)) {
            $content['ms'] = $validated['content_ms'];
        }

        $updateData = [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $content,
        ];

        if (array_key_exists('post_category_id', $validated)) {
            $updateData['post_category_id'] = $validated['post_category_id'];
        }

        if (array_key_exists('user_id', $validated)) {
            $updateData['user_id'] = $validated['user_id'];
        }

        if (array_key_exists('cover_image', $validated)) {
            $updateData['cover_image'] = $validated['cover_image'];
        }

        if (isset($validated['status'])) {
            $updateData['status'] = $validated['status'];
            if ($validated['status'] === 'published' && ! $post->published_at && ! isset($validated['published_at'])) {
                $updateData['published_at'] = now();
            }
        }

        if (array_key_exists('published_at', $validated)) {
            $updateData['published_at'] = $validated['published_at'];
        }

        $post->update($updateData);

        return Response::text((string) json_encode([
            'success' => true,
            'message' => 'Post updated successfully.',
            'post' => [
                'id' => $post->id,
                'post_category_id' => $post->post_category_id,
                'user_id' => $post->user_id,
                'title' => $post->getTranslations('title'),
                'slug' => $post->getTranslations('slug'),
                'excerpt' => $post->getTranslations('excerpt'),
                'content' => $post->getTranslations('content'),
                'status' => $post->status,
                'cover_image' => $post->cover_image,
                'published_at' => $post->published_at?->toIso8601String(),
                'updated_at' => $post->updated_at?->toIso8601String(),
            ],
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
            'id' => $schema->integer()->description('The ID of the post to update.')->required(),
            'title' => $schema->string()->description('Updated title in Indonesian. Optional.'),
            'title_en' => $schema->string()->description('Updated title in English. Optional.'),
            'title_ms' => $schema->string()->description('Updated title in Malay. Optional.'),
            'slug' => $schema->string()->description('Updated slug in Indonesian. Optional.'),
            'slug_en' => $schema->string()->description('Updated slug in English. Optional.'),
            'slug_ms' => $schema->string()->description('Updated slug in Malay. Optional.'),
            'excerpt' => $schema->string()->description('Updated excerpt in Indonesian. Optional.'),
            'excerpt_en' => $schema->string()->description('Updated excerpt in English. Optional.'),
            'excerpt_ms' => $schema->string()->description('Updated excerpt in Malay. Optional.'),
            'content' => $schema->string()->description('Updated content in Indonesian. Optional.'),
            'content_en' => $schema->string()->description('Updated content in English. Optional.'),
            'content_ms' => $schema->string()->description('Updated content in Malay. Optional.'),
            'post_category_id' => $schema->integer()->description('Updated category ID. Optional.'),
            'user_id' => $schema->integer()->description('Updated author User ID. Optional.'),
            'cover_image' => $schema->string()->description('Updated cover image URL or path. Optional.'),
            'status' => $schema->string()->description('Updated status ("draft" or "published"). Optional.'),
            'published_at' => $schema->string()->description('Updated publication date. Optional.'),
        ];
    }
}
