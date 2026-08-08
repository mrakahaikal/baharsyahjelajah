<?php

namespace App\Mcp\Tools;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Create a new blog post in the database with multi-language support (id, en, ms).')]
class CreatePostTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'post_category_id' => 'nullable|integer|exists:post_categories,id',
            'user_id' => 'nullable|integer|exists:users,id',
            'title' => 'required|string|max:255',
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

        $titleId = $validated['title'];
        $titleEn = $validated['title_en'] ?? $titleId;
        $titleMs = $validated['title_ms'] ?? $titleId;

        $slugId = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($titleId);
        $slugEn = ! empty($validated['slug_en']) ? Str::slug($validated['slug_en']) : Str::slug($titleEn);
        $slugMs = ! empty($validated['slug_ms']) ? Str::slug($validated['slug_ms']) : Str::slug($titleMs);

        $excerptId = $validated['excerpt'] ?? null;
        $excerptEn = $validated['excerpt_en'] ?? $excerptId;
        $excerptMs = $validated['excerpt_ms'] ?? $excerptId;

        $contentId = $validated['content'] ?? $titleId;
        $contentEn = $validated['content_en'] ?? $contentId;
        $contentMs = $validated['content_ms'] ?? $contentId;

        $categoryId = $validated['post_category_id'] ?? PostCategory::first()?->id;
        if (! $categoryId) {
            $defaultCategory = PostCategory::create([
                'name' => ['id' => 'Umum', 'en' => 'General', 'ms' => 'Umum'],
                'slug' => ['id' => 'umum', 'en' => 'general', 'ms' => 'umum'],
            ]);
            $categoryId = $defaultCategory->id;
        }

        $userId = $validated['user_id'] ?? auth()->id() ?? User::first()?->id;
        $status = $validated['status'] ?? 'draft';
        $publishedAt = $validated['published_at'] ?? ($status === 'published' ? now() : null);

        $post = Post::create([
            'post_category_id' => $categoryId,
            'user_id' => $userId,
            'title' => [
                'id' => $titleId,
                'en' => $titleEn,
                'ms' => $titleMs,
            ],
            'slug' => [
                'id' => $slugId,
                'en' => $slugEn,
                'ms' => $slugMs,
            ],
            'excerpt' => [
                'id' => $excerptId,
                'en' => $excerptEn,
                'ms' => $excerptMs,
            ],
            'content' => [
                'id' => $contentId,
                'en' => $contentEn,
                'ms' => $contentMs,
            ],
            'cover_image' => $validated['cover_image'] ?? null,
            'status' => $status,
            'published_at' => $publishedAt,
        ]);

        return Response::text((string) json_encode([
            'success' => true,
            'message' => 'Post created successfully.',
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
                'created_at' => $post->created_at?->toIso8601String(),
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
            'title' => $schema->string()->description('Primary title of the post (Indonesian).')->required(),
            'title_en' => $schema->string()->description('English translation of title. Optional.'),
            'title_ms' => $schema->string()->description('Malay translation of title. Optional.'),
            'slug' => $schema->string()->description('Custom slug for Indonesian locale. Auto-generated if omitted.'),
            'slug_en' => $schema->string()->description('Custom slug for English locale. Auto-generated if omitted.'),
            'slug_ms' => $schema->string()->description('Custom slug for Malay locale. Auto-generated if omitted.'),
            'excerpt' => $schema->string()->description('Short excerpt in Indonesian. Optional.'),
            'excerpt_en' => $schema->string()->description('English translation of excerpt. Optional.'),
            'excerpt_ms' => $schema->string()->description('Malay translation of excerpt. Optional.'),
            'content' => $schema->string()->description('Full body content in Indonesian. Optional.'),
            'content_en' => $schema->string()->description('English translation of body content. Optional.'),
            'content_ms' => $schema->string()->description('Malay translation of body content. Optional.'),
            'post_category_id' => $schema->integer()->description('Category ID for this post. Optional.'),
            'user_id' => $schema->integer()->description('Author User ID. Defaults to current authenticated user.'),
            'cover_image' => $schema->string()->description('URL or path to cover image. Optional.'),
            'status' => $schema->string()->description('Publication status ("draft" or "published"). Defaults to "draft".'),
            'published_at' => $schema->string()->description('Publication date string. Auto-filled if status is "published".'),
        ];
    }
}
