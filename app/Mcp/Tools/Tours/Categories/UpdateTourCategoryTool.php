<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Tours\Categories;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\TourCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('update_tour_category')]
#[Title('Update Tour Category')]
#[Description('Update an existing tour category by ID, including multilingual name/slug, icon, and sort order.')]
class UpdateTourCategoryTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Numeric ID of the tour category to update.')->required(),
            'name' => $schema->union(['string', 'object'])->description('Updated category name (plain string or multilingual map).'),
            'slug' => $schema->union(['string', 'object'])->description('Updated URL slug (plain string or multilingual map).'),
            'icon' => $schema->string()->description('Updated Lucide icon name.'),
            'sort_order' => $schema->integer()->description('Updated display sort order priority.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        if (empty($id)) {
            return Response::error('The "id" parameter is required.');
        }

        $category = TourCategory::find((int) $id);
        if (! $category) {
            return Response::error("Tour category with ID {$id} not found.");
        }

        if ($request->has('name') && ! empty($request->get('name'))) {
            $rawName = $request->get('name');
            $existingName = $category->getTranslations('name');
            $incomingName = is_array($rawName)
                ? $rawName
                : $this->normalizeMultilingualInput($rawName);

            $mergedName = array_merge($existingName, array_filter($incomingName, fn ($v) => $v !== null && $v !== ''));
            $category->setTranslations('name', $mergedName);
        }

        if ($request->has('slug') && ! empty($request->get('slug'))) {
            $rawSlug = $request->get('slug');
            $existingSlug = $category->getTranslations('slug');
            $incomingSlug = is_array($rawSlug)
                ? array_map(fn ($v) => Str::slug((string) $v), $rawSlug)
                : array_map(fn ($v) => Str::slug((string) $v), $this->normalizeMultilingualInput($rawSlug));

            $mergedSlug = array_merge($existingSlug, array_filter($incomingSlug, fn ($v) => $v !== ''));
            $category->setTranslations('slug', $mergedSlug);
        }

        if ($request->has('icon')) {
            $icon = $request->get('icon');
            $category->icon = $icon !== null && trim((string) $icon) !== '' ? trim((string) $icon) : null;
        }

        if ($request->has('sort_order')) {
            $category->sort_order = max(0, (int) $request->get('sort_order'));
        }

        $category->save();

        return Response::json([
            'message' => 'Kategori tur berhasil diperbarui.',
            'category' => [
                'id' => $category->id,
                'name' => $category->getTranslations('name'),
                'slug' => $category->getTranslations('slug'),
                'icon' => $category->icon,
                'sort_order' => (int) $category->sort_order,
                'updated_at' => $category->updated_at?->toIso8601String(),
            ],
        ]);
    }
}
