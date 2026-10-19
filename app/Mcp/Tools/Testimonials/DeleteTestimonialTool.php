<?php

namespace App\Mcp\Tools\Testimonials;

use App\Models\Testimonial;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_testimonial')]
#[Title('Delete Testimonial Entry')]
#[Description('Permanently delete a customer review and testimonial by ID.')]
class DeleteTestimonialTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Testimonial numeric ID to delete.')->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        if (empty($id)) {
            return Response::error('The "id" parameter is required.');
        }

        $item = Testimonial::find((int) $id);
        if (! $item) {
            return Response::error("Testimonial with ID {$id} not found.");
        }

        $reviewerName = $item->getTranslation('reviewer_name', 'id', false) ?: $item->getTranslation('reviewer_name', 'en', false);
        $item->delete();

        return Response::json([
            'message' => 'Testimonial berhasil dihapus.',
            'deleted_testimonial' => [
                'id' => (int) $id,
                'reviewer_name' => $reviewerName,
            ],
        ]);
    }
}
