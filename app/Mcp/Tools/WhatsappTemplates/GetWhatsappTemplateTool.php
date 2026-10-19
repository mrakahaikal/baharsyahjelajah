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

#[Name('get_whatsapp_template')]
#[Title('Get WhatsApp Template Details')]
#[Description('Get WhatsApp message template by numeric ID or by product_type and locale combination.')]
class GetWhatsappTemplateTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Template numeric ID.'),
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

        return Response::json([
            'template' => [
                'id' => $template->id,
                'product_type' => $template->product_type,
                'locale' => $template->locale,
                'template' => $template->template,
                'variables' => $template->variables ?? [],
                'created_at' => $template->created_at?->toIso8601String(),
                'updated_at' => $template->updated_at?->toIso8601String(),
            ],
        ]);
    }
}
