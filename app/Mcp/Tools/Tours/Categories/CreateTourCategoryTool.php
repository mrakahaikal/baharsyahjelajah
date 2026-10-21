<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Tours\Categories;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\TourCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('create_tour_category')]
#[Title('Create Tour Category')]
#[Description('Create a new tour category with multilingual name/slug, optional Lucide icon, and sort order.')]
class CreateTourCategoryTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->union(['string', 'object'])->description('Category name as a plain string or multilingual map {"id": "...", "en": "...", "ms": "..."}. Required.'),
            'slug' => $schema->union(['string', 'object'])->description('Optional custom URL slug (plain string or multilingual map). Auto-generated from name if omitted.'),
            'icon' => $schema->string()->description('Optional Lucide icon name (e.g., "lucide-compass", "lucide-mountain").'),
            'sort_order' => $schema->integer()->description('Display sort order priority (default: 0).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $rawName = $request->get('name');
        if (empty($rawName)) {
            return Response::error('The "name" parameter is required.');
        }

        $name = $this->normalizeMultilingualInput($rawName);
        if (empty($name['id'])) {
            return Response::error('Category name for Indonesian (id) cannot be empty.');
        }

        $slug = $this->resolveSlugTranslations($name, $request->get('slug'));

        $category = TourCategory::create([
            'name' => $name,
            'slug' => $slug,
            'icon' => $request->filled('icon') ? trim((string) $request->get('icon')) : null,
            'sort_order' => max(0, (int) $request->get('sort_order', 0)),
        ]);

        return Response::json([
            'message' => 'Kategori tur berhasil dibuat.',
            'category' => [
                'id' => $category->id,
                'name' => $category->getTranslations('name'),
                'slug' => $category->getTranslations('slug'),
                'icon' => $category->icon,
                'sort_order' => (int) $category->sort_order,
                'created_at' => $category->created_at?->toIso8601String(),
            ],
        ]);
    }
}
