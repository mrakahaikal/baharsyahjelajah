<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Categories;

use App\Models\PostCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('create_article_category')]
#[Title('Create Article Category')]
#[Description('Create a new article category with multilingual name, slug, and description support.')]
class CreateArticleCategoryTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('The name of the new category. Can be a simple string (e.g., "Tips Perjalanan") or a JSON string / object with keys "id", "en", "ms".')->required(),
            'slug' => $schema->string()->description('Optional custom URL-friendly slug. Can be a simple string or a JSON string / object with keys "id", "en", "ms".'),
            'description' => $schema->string()->description('Optional description of the category. Can be a simple string or JSON string / object with keys "id", "en", "ms".'),
        ];
    }

    public function handle(Request $request): Response
    {
        $rawName = $request->get('name');
        $rawSlug = $request->get('slug');
        $rawDescription = $request->get('description');

        if (empty($rawName)) {
            return Response::error('The "name" parameter is required.');
        }

        $name = $this->normalizeMultilingualInput($rawName);
        if (empty($name['id'])) {
            return Response::error('Category name for Indonesian (id) cannot be empty.');
        }

        $slug = $this->resolveSlug($name, $rawSlug);
        if (empty($slug['id'])) {
            return Response::error('Invalid category slug generated.');
        }

        // Check uniqueness for primary slug
        $slugExists = PostCategory::query()
            ->whereJsonContains('slug->id', $slug['id'])
            ->exists();

        if ($slugExists) {
            return Response::error("A category with the slug '{$slug['id']}' already exists.");
        }

        $description = ! empty($rawDescription) ? $this->normalizeMultilingualInput($rawDescription) : null;

        /** @var PostCategory $category */
        $category = PostCategory::create([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
        ]);

        return Response::json([
            'message' => 'Category created successfully.',
            'category' => [
                'id' => $category->id,
                'name' => $category->getTranslation('name', 'id', false),
                'slug' => $category->localizedSlug('id'),
                'description' => $category->getTranslation('description', 'id', false),
                'translations' => [
                    'name' => $category->getTranslations('name'),
                    'slug' => $category->getTranslations('slug'),
                    'description' => $category->getTranslations('description'),
                ],
                'created_at' => $category->created_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Normalize string or associative array/json to a standard ['id' => ..., 'en' => ..., 'ms' => ...] array.
     *
     * @return array<string, string>
     */
    protected function normalizeMultilingualInput(mixed $input): array
    {
        if (is_string($input)) {
            $trimmed = trim($input);
            if (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) {
                $decoded = json_decode($trimmed, true);
                if (is_array($decoded)) {
                    $input = $decoded;
                }
            }
        }

        if (is_array($input)) {
            $primary = (string) ($input['id'] ?? $input['en'] ?? $input['ms'] ?? '');

            return [
                'id' => (string) ($input['id'] ?? $primary),
                'en' => (string) ($input['en'] ?? $primary),
                'ms' => (string) ($input['ms'] ?? $primary),
            ];
        }

        $stringValue = trim((string) $input);

        return [
            'id' => $stringValue,
            'en' => $stringValue,
            'ms' => $stringValue,
        ];
    }

    /**
     * Resolve slug translations from input or name translations.
     *
     * @param  array<string, string>  $name
     * @return array<string, string>
     */
    protected function resolveSlug(array $name, mixed $rawSlug): array
    {
        if (! empty($rawSlug)) {
            $normalized = $this->normalizeMultilingualInput($rawSlug);

            return [
                'id' => Str::slug($normalized['id'] ?: $name['id']),
                'en' => Str::slug($normalized['en'] ?: ($name['en'] ?: $name['id'])),
                'ms' => Str::slug($normalized['ms'] ?: ($name['ms'] ?: $name['id'])),
            ];
        }

        return [
            'id' => Str::slug($name['id']),
            'en' => Str::slug($name['en'] ?: $name['id']),
            'ms' => Str::slug($name['ms'] ?: $name['id']),
        ];
    }
}
