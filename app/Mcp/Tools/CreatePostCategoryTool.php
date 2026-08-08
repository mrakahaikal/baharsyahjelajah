<?php

namespace App\Mcp\Tools;

use App\Models\PostCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Create a new post category in the database with multi-language support (id, en, ms).')]
class CreatePostCategoryTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_ms' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'slug_en' => 'nullable|string|max:255',
            'slug_ms' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_ms' => 'nullable|string',
        ]);

        $nameId = $validated['name'];
        $nameEn = $validated['name_en'] ?? $nameId;
        $nameMs = $validated['name_ms'] ?? $nameId;

        $slugId = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($nameId);
        $slugEn = ! empty($validated['slug_en']) ? Str::slug($validated['slug_en']) : Str::slug($nameEn);
        $slugMs = ! empty($validated['slug_ms']) ? Str::slug($validated['slug_ms']) : Str::slug($nameMs);

        $descId = $validated['description'] ?? null;
        $descEn = $validated['description_en'] ?? $descId;
        $descMs = $validated['description_ms'] ?? $descId;

        $category = PostCategory::create([
            'name' => [
                'id' => $nameId,
                'en' => $nameEn,
                'ms' => $nameMs,
            ],
            'slug' => [
                'id' => $slugId,
                'en' => $slugEn,
                'ms' => $slugMs,
            ],
            'description' => [
                'id' => $descId,
                'en' => $descEn,
                'ms' => $descMs,
            ],
        ]);

        return Response::text((string) json_encode([
            'success' => true,
            'message' => 'Post category created successfully.',
            'category' => [
                'id' => $category->id,
                'name' => $category->getTranslations('name'),
                'slug' => $category->getTranslations('slug'),
                'description' => $category->getTranslations('description'),
                'created_at' => $category->created_at?->toIso8601String(),
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
            'name' => $schema->string()->description('Primary name of the category (Indonesian).')->required(),
            'name_en' => $schema->string()->description('English translation of category name. Optional.'),
            'name_ms' => $schema->string()->description('Malay translation of category name. Optional.'),
            'slug' => $schema->string()->description('Custom category slug for Indonesian locale. Auto-generated if omitted.'),
            'slug_en' => $schema->string()->description('Custom category slug for English locale. Auto-generated if omitted.'),
            'slug_ms' => $schema->string()->description('Custom category slug for Malay locale. Auto-generated if omitted.'),
            'description' => $schema->string()->description('Description of the category in Indonesian. Optional.'),
            'description_en' => $schema->string()->description('English translation of category description. Optional.'),
            'description_ms' => $schema->string()->description('Malay translation of category description. Optional.'),
        ];
    }
}
