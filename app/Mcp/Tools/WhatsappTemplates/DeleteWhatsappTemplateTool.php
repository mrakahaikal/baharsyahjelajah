<?php

namespace App\Mcp\Tools\WhatsappTemplates;

use App\Models\WhatsappTemplate;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_whatsapp_template')]
#[Title('Delete WhatsApp Message Template')]
#[Description('Permanently delete a WhatsApp message template by numeric ID or product_type and locale.')]
class DeleteWhatsappTemplateTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Template numeric ID to delete.'),
            'product_type' => $schema->string()->description('Product type: "tour", "umrah", "vehicle", or "visa".'),
            'locale' => $schema->string()->description('Language locale: "id", "en", or "ms".'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $productType = (string) $request->get('product_type');
        $locale = (string) $request->get('locale');

        if (empty($id) && (empty($productType) || empty($locale))) {
            return Response::error('Provide either "id" OR both "product_type" and "locale".');
        }

        $template = WhatsappTemplate::query()
            ->when(! empty($id), fn ($q) => $q->where('id', (int) $id))
            ->when(! empty($productType) && ! empty($locale), fn ($q) => $q->where('product_type', $productType)->where('locale', $locale))
            ->first();

        if (! $template) {
            $identifier = ! empty($id) ? "ID {$id}" : "product_type '{$productType}' and locale '{$locale}'";

            return Response::error("WhatsApp template with {$identifier} not found.");
        }

        $summary = "{$template->product_type} ({$template->locale})";
        $template->delete();

        return Response::json([
            'message' => 'Template WhatsApp berhasil dihapus.',
            'deleted_template' => [
                'id' => $template->id,
                'target' => $summary,
            ],
        ]);
    }
}
