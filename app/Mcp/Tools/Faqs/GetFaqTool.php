<?php

namespace App\Mcp\Tools\Faqs;

use App\Enums\FaqCategory;
use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Faq;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('get_faq')]
#[Title('Get FAQ Details')]
#[Description('Get complete frequently asked question (FAQ) details by ID, including all multilingual translations and assigned contexts.')]
class GetFaqTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('FAQ numeric ID.')->required(),
            'locale' => $schema->string()->description('Preferred output locale ("id", "en", "ms"). Default is "id".'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $locale = (string) $request->get('locale', 'id');

        if (empty($id)) {
            return Response::error('The "id" parameter is required.');
        }

        $faq = Faq::find((int) $id);

        if (! $faq) {
            return Response::error("FAQ with ID {$id} not found.");
        }

        return Response::json([
            'faq' => [
                'id' => $faq->id,
                'category' => $faq->category?->value ?? (string) $faq->category,
                'category_label' => $faq->category instanceof FaqCategory ? $faq->category->getLabel() : $faq->category,
                'contexts' => $faq->contexts ?? [],
                'question' => $faq->getTranslation('question', $locale, false) ?: $faq->getTranslation('question', 'id', false),
                'question_translations' => $faq->getTranslations('question'),
                'answer' => $faq->getTranslation('answer', $locale, false) ?: $faq->getTranslation('answer', 'id', false),
                'answer_translations' => $faq->getTranslations('answer'),
                'sort_order' => $faq->sort_order,
                'is_active' => (bool) $faq->is_active,
                'created_at' => $faq->created_at?->toIso8601String(),
                'updated_at' => $faq->updated_at?->toIso8601String(),
            ],
            'locale' => $locale,
        ]);
    }
}
