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

#[Name('update_whatsapp_template')]
#[Title('Update WhatsApp Message Template')]
#[Description('Update message template content or variable placeholders by numeric ID or product_type and locale.')]
class UpdateWhatsappTemplateTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Template numeric ID to update.'),
            'product_type' => $schema->string()->description('Product type: "tour", "umrah", "vehicle", or "visa".'),
            'locale' => $schema->string()->description('Language locale: "id", "en", or "ms".'),
            'template' => $schema->string()->description('Updated template message text.'),
            'variables' => $schema->array()->description('Updated list of variables.'),
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

        if ($request->has('template')) {
            $templateText = (string) $request->get('template');
            $template->template = $templateText;

            if (! $request->has('variables')) {
                preg_match_all('/\{[a-zA-Z0-9_]+\}/', $templateText, $matches);
                $template->variables = array_values(array_unique($matches[0] ?? []));
            }
        }

        if ($request->has('variables')) {
            $template->variables = (array) $request->get('variables');
        }

        $template->save();

        return Response::json([
            'message' => 'Template WhatsApp berhasil diperbarui.',
            'template' => [
                'id' => $template->id,
                'product_type' => $template->product_type,
                'locale' => $template->locale,
                'template' => $template->template,
                'variables' => $template->variables,
                'updated_at' => $template->updated_at?->toIso8601String(),
            ],
        ]);
    }
}
