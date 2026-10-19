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

#[Name('list_whatsapp_templates')]
#[Title('List WhatsApp Message Templates')]
#[Description('List WhatsApp inquiry and booking message templates filtered by product type (tour, umrah, vehicle, visa) and locale (id, en, ms).')]
class ListWhatsappTemplatesTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'product_type' => $schema->string()->description('Filter by product type: "tour", "umrah", "vehicle", or "visa".'),
            'locale' => $schema->string()->description('Filter by language locale: "id", "en", or "ms".'),
            'search' => $schema->string()->description('Search keyword matching message template text.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $query = WhatsappTemplate::query();

        if ($request->filled('product_type')) {
            $query->where('product_type', (string) $request->get('product_type'));
        }

        if ($request->filled('locale')) {
            $query->where('locale', (string) $request->get('locale'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where('template', 'like', "%{$search}%");
        }

        $templates = $query->orderBy('product_type')
            ->orderBy('locale')
            ->get()
            ->map(fn (WhatsappTemplate $t): array => [
                'id' => $t->id,
                'product_type' => $t->product_type,
                'locale' => $t->locale,
                'template' => $t->template,
                'variables' => $t->variables ?? [],
                'created_at' => $t->created_at?->toIso8601String(),
                'updated_at' => $t->updated_at?->toIso8601String(),
            ])
            ->all();

        return Response::json([
            'total' => count($templates),
            'templates' => $templates,
        ]);
    }
}
