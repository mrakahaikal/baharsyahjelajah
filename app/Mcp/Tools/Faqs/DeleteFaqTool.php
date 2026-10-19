<?php

namespace App\Mcp\Tools\Faqs;

use App\Models\Faq;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_faq')]
#[Title('Delete FAQ Entry')]
#[Description('Permanently delete a frequently asked question (FAQ) by ID.')]
class DeleteFaqTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('FAQ numeric ID to delete.')->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        if (empty($id)) {
            return Response::error('The "id" parameter is required.');
        }

        $faq = Faq::find((int) $id);
        if (! $faq) {
            return Response::error("FAQ with ID {$id} not found.");
        }

        $questionSummary = $faq->getTranslation('question', 'id', false) ?: $faq->getTranslation('question', 'en', false);
        $faq->delete();

        return Response::json([
            'message' => 'FAQ berhasil dihapus.',
            'deleted_faq' => [
                'id' => (int) $id,
                'question' => $questionSummary,
            ],
        ]);
    }
}
