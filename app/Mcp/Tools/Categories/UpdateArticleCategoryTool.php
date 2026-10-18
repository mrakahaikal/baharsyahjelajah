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

#[Name('update_article_category')]
#[Title('Update Article Category')]
#[Description('Update the name, slug, or description of an existing article category.')]
class UpdateArticleCategoryTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The ID of the category to update.')->required(),
            'name' => $schema->string()->description('The updated name (simple string or JSON string / object with keys "id", "en", "ms").'),
            'slug' => $schema->string()->description('The updated slug (simple string or JSON string / object with keys "id", "en", "ms"). Must be unique.'),
            'description' => $schema->string()->description('The updated description (simple string or JSON string / object with keys "id", "en", "ms").'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        if (empty($id)) {
            return Response::error('The "id" parameter is required.');
        }

        /** @var PostCategory|null $category */
        $category = PostCategory::find((int) $id);
        if (! $category) {
            return Response::error("Category with ID {$id} was not found.");
        }

        $rawName = $request->get('name');
        $rawSlug = $request->get('slug');
        $rawDescription = $request->get('description');

        if (empty($rawName) && empty($rawSlug) && empty($rawDescription) && ! $request->has('description')) {
            return Response::error('Please provide at least a new "name", "slug", or "description" to update.');
        }

        if (! empty($rawName)) {
            $updatedName = $this->mergeMultilingualInput($category->getTranslations('name'), $rawName);
            $category->name = $updatedName;
        }

        if (! empty($rawSlug)) {
            $normalizedSlug = $this->normalizeSlugInput($rawSlug);

            if (! empty($normalizedSlug['id'])) {
                $slugExists = PostCategory::query()
                    ->where('id', '!=', $category->id)
                    ->whereJsonContains('slug->id', $normalizedSlug['id'])
                    ->exists();

                if ($slugExists) {
                    return Response::error("The slug '{$normalizedSlug['id']}' is already taken by another category.");
                }
            }

            $currentSlugs = $category->getTranslations('slug');
            $category->slug = array_merge($currentSlugs, array_filter($normalizedSlug));
        }

        if ($request->has('description')) {
            $currentDesc = $category->getTranslations('description');
            $category->description = ! empty($rawDescription)
                ? $this->mergeMultilingualInput($currentDesc, $rawDescription)
                : null;
        }

        $category->save();

        return Response::json([
            'message' => 'Category updated successfully.',
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
                'updated_at' => $category->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Merge new inputs onto existing translations.
     *
     * @param  array<string, string>  $existing
     * @return array<string, string>
     */
    protected function mergeMultilingualInput(array $existing, mixed $input): array
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
            return array_merge($existing, array_filter($input, fn ($v) => $v !== null));
        }

        $stringValue = trim((string) $input);

        return [
            'id' => $stringValue,
            'en' => $existing['en'] ?? $stringValue,
            'ms' => $existing['ms'] ?? $stringValue,
        ];
    }

    /**
     * Normalize slug inputs.
     *
     * @return array<string, string>
     */
    protected function normalizeSlugInput(mixed $input): array
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
            $result = [];
            foreach (['id', 'en', 'ms'] as $locale) {
                if (! empty($input[$locale])) {
                    $result[$locale] = Str::slug((string) $input[$locale]);
                }
            }

            return $result;
        }

        $slug = Str::slug(trim((string) $input));

        return [
            'id' => $slug,
            'en' => $slug,
            'ms' => $slug,
        ];
    }
}

