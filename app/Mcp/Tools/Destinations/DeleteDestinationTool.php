<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Destinations;

use App\Models\Destination;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_destination')]
#[Title('Delete Destination')]
#[Description('Delete a destination safely. If connected to articles or tour/umrah packages, requires force=true to confirm detaching and permanent deletion.')]
class DeleteDestinationTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The numeric ID of the destination to delete.'),
            'slug' => $schema->string()->description('The slug of the destination to delete (used if "id" is omitted).'),
            'force' => $schema->boolean()->description('Force deletion by automatically detaching from all linked articles and tour packages. Default is false.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $slug = $request->get('slug');

        if (empty($id) && empty($slug)) {
            return Response::error('Either "id" or "slug" must be provided.');
        }

        /** @var Destination|null $destination */
        $destination = ! empty($id)
            ? Destination::find((int) $id)
            : Destination::where('slug', (string) $slug)->first();

        if (! $destination) {
            $identifier = ! empty($id) ? "ID {$id}" : "slug '{$slug}'";

            return Response::error("Destination with {$identifier} not found.");
        }

        $force = (bool) $request->get('force', false);

        $postsCount = $destination->posts()->count();
        $toursCount = $destination->tourPackages()->count();
        $umrahCount = $destination->umrahPackages()->count();
        $totalLinked = $postsCount + $toursCount + $umrahCount;

        $name = $destination->getTranslation('name', 'id', false);

        if ($totalLinked > 0 && ! $force) {
            return Response::error(
                "Cannot delete destination '{$name}' because it is currently linked to {$totalLinked} content item(s) ({$postsCount} article(s), {$toursCount} tour package(s), {$umrahCount} umrah package(s)). Set 'force' => true to detach all links and delete permanently."
            );
        }

        $destId = $destination->id;

        DB::transaction(function () use ($destination): void {
            $destination->posts()->detach();
            $destination->tourPackages()->detach();
            $destination->umrahPackages()->detach();
            $destination->countries()->detach();
            $destination->delete();
        });

        return Response::json([
            'message' => "Destination '{$name}' (ID {$destId}) deleted successfully.",
            'deleted_id' => $destId,
            'detached_links' => [
                'articles' => $postsCount,
                'tour_packages' => $toursCount,
                'umrah_packages' => $umrahCount,
            ],
        ]);
    }
}

