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

#[Description('Update an existing post category record in the database.')]
class UpdatePostCategoryTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:post_categories,id',
            'name' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_ms' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'slug_en' => 'nullable|string|max:255',
            'slug_ms' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_ms' => 'nullable|string',
        ]);

        $category = PostCategory::findOrFail($validated['id']);

        $name = $category->getTranslations('name');
        if (isset($validated['name'])) {
            $name['id'] = $validated['name'];
        }
        if (isset($validated['name_en'])) {
            $name['en'] = $validated['name_en'];
        }
        if (isset($validated['name_ms'])) {
            $name['ms'] = $validated['name_ms'];
        }

        $slug = $category->getTranslations('slug');
        if (isset($validated['slug'])) {
            $slug['id'] = Str::slug($validated['slug']);
        } elseif (isset($validated['name'])) {
            $slug['id'] = Str::slug($validated['name']);
        }

        if (isset($validated['slug_en'])) {
            $slug['en'] = Str::slug($validated['slug_en']);
        } elseif (isset($validated['name_en'])) {
            $slug['en'] = Str::slug($validated['name_en']);
        }

        if (isset($validated['slug_ms'])) {
            $slug['ms'] = Str::slug($validated['slug_ms']);
        } elseif (isset($validated['name_ms'])) {
            $slug['ms'] = Str::slug($validated['name_ms']);
        }

        $description = $category->getTranslations('description');
        if (array_key_exists('description', $validated)) {
            $description['id'] = $validated['description'];
        }
        if (array_key_exists('description_en', $validated)) {
            $description['en'] = $validated['description_en'];
        }
        if (array_key_exists('description_ms', $validated)) {
            $description['ms'] = $validated['description_ms'];
        }

        $category->update([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
        ]);

        return Response::text((string) json_encode([
            'success' => true,
            'message' => 'Post category updated successfully.',
            'category' => [
                'id' => $category->id,
                'name' => $category->getTranslations('name'),
                'slug' => $category->getTranslations('slug'),
                'description' => $category->getTranslations('description'),
                'updated_at' => $category->updated_at?->toIso8601String(),
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
            'id' => $schema->integer()->description('The ID of the post category to update.')->required(),
            'name' => $schema->string()->description('Updated name in Indonesian locale. Optional.'),
            'name_en' => $schema->string()->description('Updated name in English locale. Optional.'),
            'name_ms' => $schema->string()->description('Updated name in Malay locale. Optional.'),
            'slug' => $schema->string()->description('Updated slug in Indonesian locale. Optional.'),
            'slug_en' => $schema->string()->description('Updated slug in English locale. Optional.'),
            'slug_ms' => $schema->string()->description('Updated slug in Malay locale. Optional.'),
            'description' => $schema->string()->description('Updated description in Indonesian locale. Optional.'),
            'description_en' => $schema->string()->description('Updated description in English locale. Optional.'),
            'description_ms' => $schema->string()->description('Updated description in Malay locale. Optional.'),
        ];
    }
}
