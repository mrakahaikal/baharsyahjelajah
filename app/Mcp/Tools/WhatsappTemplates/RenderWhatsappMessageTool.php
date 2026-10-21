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

#[Name('render_whatsapp_message')]
#[Title('Render Dynamic WhatsApp Message')]
#[Description('Simulate and render a WhatsApp message using a stored template, injecting dynamic variables, and generating a direct WhatsApp click-to-chat URL.')]
class RenderWhatsappMessageTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'product_type' => $schema->string()->description('Product type: "tour", "umrah", "vehicle", or "visa".')->required(),
            'locale' => $schema->string()->description('Language locale: "id", "en", or "ms". Default is "id".'),
            'variables' => $schema->object()->description('Key-value map of variable replacements (e.g. {"product_name": "Paket Umrah Syawal", "pax": "2"}).')->required(),
            'phone_number' => $schema->string()->description('Optional destination phone number (e.g. "6281234567890") to generate direct wa.me URL.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $productType = (string) $request->get('product_type');
        if (empty($productType)) {
            return Response::error('The "product_type" parameter is required.');
        }

        $locale = (string) $request->get('locale', 'id');
        $rawVariables = $request->get('variables');

        if (! is_array($rawVariables)) {
            return Response::error('The "variables" parameter must be an associative array of key-value replacements.');
        }

        // Format variables so both {key} and key work seamlessly
        $formattedVariables = [];
        foreach ($rawVariables as $key => $value) {
            $valStr = (string) $value;
            $cleanKey = trim((string) $key, '{}');
            $formattedVariables["{{$cleanKey}}"] = $valStr;
            $formattedVariables[$cleanKey] = $valStr;
        }

        $renderedText = WhatsappTemplate::render($productType, $locale, $formattedVariables);

        if (empty($renderedText)) {
            return Response::error("No WhatsApp template found for product_type '{$productType}' in locale '{$locale}' or fallback 'id'.");
        }

        $phoneNumber = preg_replace('/\D/', '', (string) $request->get('phone_number', ''));
        $waLink = null;
        if (! empty($phoneNumber)) {
            $waLink = 'https://wa.me/'.$phoneNumber.'?text='.rawurlencode($renderedText);
        }

        return Response::json([
            'product_type' => $productType,
            'locale' => $locale,
            'rendered_message' => $renderedText,
            'phone_number' => $phoneNumber ?: null,
            'whatsapp_url' => $waLink,
        ]);
    }
}
