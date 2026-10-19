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

#[Name('create_whatsapp_template')]
#[Title('Create WhatsApp Message Template')]
#[Description('Create a new WhatsApp inquiry and booking message template for a product type and locale.')]
class CreateWhatsappTemplateTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'product_type' => $schema->string()->description('Product type: "tour", "umrah", "vehicle", or "visa". Required.')->required(),
            'locale' => $schema->string()->description('Language locale: "id", "en", or "ms". Required.')->required(),
            'template' => $schema->string()->description('Message template text containing placeholders like {product_name}, {pax}, {price}. Required.')->required(),
            'variables' => $schema->array()->description('List of variable placeholder names (e.g. ["{product_name}", "{pax}"]). Auto-extracted from template if omitted.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $productType = (string) $request->get('product_type');
        if (empty($productType)) {
            return Response::error('The "product_type" parameter is required.');
        }

        $locale = (string) $request->get('locale');
        if (empty($locale)) {
            return Response::error('The "locale" parameter is required.');
        }

        $templateText = (string) $request->get('template');
        if (empty($templateText)) {
            return Response::error('The "template" parameter is required.');
        }

        if (WhatsappTemplate::where('product_type', $productType)->where('locale', $locale)->exists()) {
            return Response::error("A WhatsApp template for product_type '{$productType}' and locale '{$locale}' already exists.");
        }

        $variables = $request->get('variables');
        if (! is_array($variables) || empty($variables)) {
            preg_match_all('/\{[a-zA-Z0-9_]+\}/', $templateText, $matches);
            $variables = array_values(array_unique($matches[0] ?? []));
        }

        $template = WhatsappTemplate::create([
            'product_type' => $productType,
            'locale' => $locale,
            'template' => $templateText,
            'variables' => $variables,
        ]);

        return Response::json([
            'message' => 'Template WhatsApp berhasil dibuat.',
            'template' => [
                'id' => $template->id,
                'product_type' => $template->product_type,
                'locale' => $template->locale,
                'template' => $template->template,
                'variables' => $template->variables,
                'created_at' => $template->created_at?->toIso8601String(),
            ],
        ]);
    }
}
