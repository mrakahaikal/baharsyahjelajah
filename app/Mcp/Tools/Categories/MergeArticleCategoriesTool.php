<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Categories;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('merge_article_categories')]
#[Title('Merge Article Categories')]
#[Description('Consolidate two article categories by moving all articles from the source category into the target category and deleting the source category.')]
class MergeArticleCategoriesTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'source_category_id' => $schema->integer()->description('The ID of the source category to be merged and deleted.'),
            'source_category_slug' => $schema->string()->description('The slug of the source category (used if "source_category_id" is omitted).'),
            'target_category_id' => $schema->integer()->description('The ID of the target category that will receive all articles.'),
            'target_category_slug' => $schema->string()->description('The slug of the target category (used if "target_category_id" is omitted).'),
        ];
    }

    public function handle(Request $request): Response
    {
        // 1. Resolve source category
        $sourceId = $request->get('source_category_id');
        $sourceSlug = $request->get('source_category_slug');

        /** @var PostCategory|null $source */
        $source = ! empty($sourceId)
            ? PostCategory::find((int) $sourceId)
            : (! empty($sourceSlug) ? PostCategory::whereLocalizedSlug((string) $sourceSlug)->first() : null);

        if (! $source) {
            return Response::error('Source category could not be found with provided ID or slug.');
        }

        // 2. Resolve target category
        $targetId = $request->get('target_category_id');
        $targetSlug = $request->get('target_category_slug');

        /** @var PostCategory|null $target */
        $target = ! empty($targetId)
            ? PostCategory::find((int) $targetId)
            : (! empty($targetSlug) ? PostCategory::whereLocalizedSlug((string) $targetSlug)->first() : null);

        if (! $target) {
            return Response::error('Target category could not be found with provided ID or slug.');
        }

        if ($source->id === $target->id) {
            return Response::error('Source and target categories cannot be the same category.');
        }

        $sourceName = $source->getTranslation('name', 'id', false);
        $targetName = $target->getTranslation('name', 'id', false);
        $sourceIdVal = $source->id;

        // 3. Move posts and delete source inside transaction
        $movedCount = DB::transaction(function () use ($source, $target): int {
            $count = Post::query()
                ->where('post_category_id', $source->id)
                ->update(['post_category_id' => $target->id]);

            $source->delete();

            return $count;
        });

        return Response::json([
            'message' => "Successfully merged category '{$sourceName}' into '{$targetName}'. Moved {$movedCount} articles and deleted source category.",
            'moved_articles_count' => $movedCount,
            'source_deleted' => [
                'id' => $sourceIdVal,
                'name' => $sourceName,
            ],
            'target' => [
                'id' => $target->id,
                'name' => $targetName,
                'slug' => $target->localizedSlug('id'),
            ],
        ]);
    }
}
