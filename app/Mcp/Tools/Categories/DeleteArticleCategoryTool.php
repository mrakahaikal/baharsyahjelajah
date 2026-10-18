<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Categories;

use App\Models\PostCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_article_category')]
#[Title('Delete Article Category')]
#[Description('Safely delete an article category by ID. If articles are attached, reassign_to_category_id or force=true is required.')]
class DeleteArticleCategoryTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The ID of the category to delete.')->required(),
            'reassign_to_category_id' => $schema->integer()->description('Optional ID of another category to reassign attached articles to before deletion.'),
            'force' => $schema->boolean()->description('If true, attached articles will be deleted along with the category (default: false).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        if (empty($id)) {
            return Response::error('The "id" parameter is required.');
        }

        $force = (bool) ($request->get('force') ?? false);
        $reassignId = $request->get('reassign_to_category_id');

        /** @var PostCategory|null $category */
        $category = PostCategory::find((int) $id);

        if (! $category) {
            return Response::error("Category with ID {$id} was not found.");
        }

        $postsCount = $category->posts()->count();

        if ($postsCount > 0) {
            if (! empty($reassignId)) {
                $targetCategory = PostCategory::find((int) $reassignId);
                if (! $targetCategory) {
                    return Response::error("Target category with ID {$reassignId} for reassignment was not found.");
                }

                $category->posts()->update(['post_category_id' => $targetCategory->id]);
            } elseif (! $force) {
                $categoryName = $category->getTranslation('name', 'id', false) ?: "ID {$id}";

                return Response::error(
                    "Cannot delete category '{$categoryName}' (ID: {$id}) because {$postsCount} article(s) are assigned to it. ".
                    "Provide 'reassign_to_category_id' to move them, or 'force' => true to delete them along with the category."
                );
            } else {
                $category->posts()->delete();
            }
        }

        $categoryName = $category->getTranslation('name', 'id', false) ?: "ID {$id}";
        $category->delete();

        return Response::json([
            'message' => "Category '{$categoryName}' (ID: {$id}) was deleted successfully.",
            'affected_articles_count' => $postsCount,
        ]);
    }
}

