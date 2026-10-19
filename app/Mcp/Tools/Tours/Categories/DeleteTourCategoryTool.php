<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Tours\Categories;

use App\Models\TourCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_tour_category')]
#[Title('Delete Tour Category')]
#[Description('Delete a tour category by ID. Prevents deletion if the category still has associated tours unless they are reassigned first.')]
class DeleteTourCategoryTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Numeric ID of the tour category to delete.')->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        if (empty($id)) {
            return Response::error('The "id" parameter is required.');
        }

        $category = TourCategory::withCount('tours')->find((int) $id);
        if (! $category) {
            return Response::error("Tour category with ID {$id} not found.");
        }

        if ($category->tours_count > 0) {
            return Response::error(
                "Cannot delete tour category '{$category->getTranslation('name', 'id', false)}' (ID {$category->id}) because it still has {$category->tours_count} associated tour(s). Please reassign or delete those tours first."
            );
        }

        $nameSummary = $category->getTranslation('name', 'id', false) ?: $category->getTranslation('name', 'en', false);
        $category->delete();

        return Response::json([
            'message' => 'Kategori tur berhasil dihapus.',
            'deleted_category' => [
                'id' => (int) $id,
                'name' => $nameSummary,
            ],
        ]);
    }
}
