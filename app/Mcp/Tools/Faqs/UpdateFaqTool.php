<?php

namespace App\Mcp\Tools\Faqs;

use App\Enums\FaqCategory;
use App\Enums\FaqContext;
use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Faq;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('update_faq')]
#[Title('Update FAQ Entry')]
#[Description('Update frequently asked question (FAQ) attributes, including multilingual question/answer, category, display contexts, or active status.')]
class UpdateFaqTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('FAQ numeric ID to update.')->required(),
            'question' => $schema->union(['string', 'object'])->description('New question text (single string or multilingual JSON map).'),
            'answer' => $schema->union(['string', 'object'])->description('New answer text (single string or multilingual JSON map).'),
            'category' => $schema->string()->description('Updated category: "general", "tour", "umrah", "vehicle", or "payment".'),
            'contexts' => $schema->array()->description('Updated contexts array: ["home", "tour", "umrah", "vehicle", "booking"].'),
            'sort_order' => $schema->integer()->description('Updated sorting weight.'),
            'is_active' => $schema->boolean()->description('Updated publish status.'),
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

        if ($request->has('question')) {
            $normalizedQuestion = $this->normalizeMultilingualInput($request->get('question'));
            if (! empty($normalizedQuestion['id'])) {
                $merged = array_merge($faq->getTranslations('question'), $normalizedQuestion);
                $faq->setTranslations('question', $merged);
            }
        }

        if ($request->has('answer')) {
            $normalizedAnswer = $this->normalizeMultilingualInput($request->get('answer'));
            if (! empty($normalizedAnswer['id'])) {
                $merged = array_merge($faq->getTranslations('answer'), $normalizedAnswer);
                $faq->setTranslations('answer', $merged);
            }
        }

        if ($request->filled('category')) {
            $categoryEnum = FaqCategory::tryFrom((string) $request->get('category'));
            if ($categoryEnum) {
                $faq->category = $categoryEnum;
            }
        }

        if ($request->has('contexts')) {
            $rawContexts = $request->get('contexts');
            if (is_array($rawContexts)) {
                $faq->contexts = collect($rawContexts)
                    ->map(fn ($c) => is_string($c) ? trim($c) : '')
                    ->filter(fn ($c) => FaqContext::tryFrom($c) !== null)
                    ->values()
                    ->all();
            }
        }

        if ($request->has('sort_order')) {
            $faq->sort_order = max(0, (int) $request->get('sort_order'));
        }

        if ($request->has('is_active')) {
            $faq->is_active = (bool) $request->get('is_active');
        }

        $faq->save();

        return Response::json([
            'message' => 'FAQ berhasil diperbarui.',
            'faq' => [
                'id' => $faq->id,
                'category' => $faq->category?->value,
                'category_label' => $faq->category?->getLabel(),
                'contexts' => $faq->contexts,
                'question' => $faq->getTranslations('question'),
                'answer' => $faq->getTranslations('answer'),
                'sort_order' => $faq->sort_order,
                'is_active' => (bool) $faq->is_active,
                'updated_at' => $faq->updated_at?->toIso8601String(),
            ],
        ]);
    }
}
