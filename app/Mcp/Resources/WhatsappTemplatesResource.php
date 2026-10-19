<?php

namespace App\Mcp\Resources;

use App\Models\WhatsappTemplate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class WhatsappTemplatesResource extends Resource
{
    protected string $uri = 'whatsapp://templates';

    protected string $name = 'Daftar Template Pesan WhatsApp Resmi';

    protected string $description = 'Snapshot template pesan chat WhatsApp resmi perusahaan untuk pemesanan tur, umrah, sewa kendaraan, dan visa.';

    protected string $mimeType = 'application/json';

    public function handle(Request $request): Response
    {
        $templates = WhatsappTemplate::query()
            ->orderBy('product_type')
            ->orderBy('locale')
            ->get()
            ->map(fn (WhatsappTemplate $t): array => [
                'id' => $t->id,
                'product_type' => $t->product_type,
                'locale' => $t->locale,
                'template' => $t->template,
                'variables' => $t->variables ?? [],
            ])
            ->all();

        return Response::json([
            'total_templates' => count($templates),
            'templates' => $templates,
        ]);
    }
}
