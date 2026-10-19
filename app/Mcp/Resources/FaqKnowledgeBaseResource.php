<?php

namespace App\Mcp\Resources;

use App\Models\Faq;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class FaqKnowledgeBaseResource extends Resource
{
    protected string $uri = 'faqs://knowledge-base';

    protected string $name = 'Pusat Tanya Jawab Resmi (FAQ Knowledge Base)';

    protected string $description = 'Snapshot seluruh tanya jawab (FAQ) resmi Baharsyah Jelajah yang aktif, terkelompok berdasarkan kategori dan konteks tampilan.';

    protected string $mimeType = 'application/json';

    public function handle(Request $request): Response
    {
        $faqs = Faq::query()
            ->active()
            ->ordered()
            ->get()
            ->map(fn (Faq $f): array => [
                'id' => $f->id,
                'category' => $f->category?->value ?? (string) $f->category,
                'category_label' => $f->category?->getLabel(),
                'contexts' => $f->contexts ?? [],
                'question' => [
                    'id' => $f->getTranslation('question', 'id', false),
                    'en' => $f->getTranslation('question', 'en', false),
                    'ms' => $f->getTranslation('question', 'ms', false),
                ],
                'answer' => [
                    'id' => $f->getTranslation('answer', 'id', false),
                    'en' => $f->getTranslation('answer', 'en', false),
                    'ms' => $f->getTranslation('answer', 'ms', false),
                ],
                'sort_order' => $f->sort_order,
            ])
            ->values()
            ->all();

        $grouped = collect($faqs)->groupBy('category')->all();

        return Response::json([
            'total_active_faqs' => count($faqs),
            'categories' => array_keys($grouped),
            'by_category' => $grouped,
            'all_faqs' => $faqs,
        ]);
    }
}
