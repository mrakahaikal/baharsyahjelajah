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

#[Name('create_faq')]
#[Title('Create FAQ Entry')]
#[Description('Create a new frequently asked question (FAQ) with multilingual question/answer, category, and display contexts.')]
class CreateFaqTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'question' => $schema->mixed()->description('The question text. Single string or multilingual JSON map {"id": "...", "en": "...", "ms": "..."}. Required.'),
            'answer' => $schema->mixed()->description('The answer text. Single string or multilingual JSON map. Required.'),
            'category' => $schema->string()->description('FAQ category: "general", "tour", "umrah", "vehicle", or "payment". Default is "general".'),
            'contexts' => $schema->array()->description('Display contexts array: ["home", "tour", "umrah", "vehicle", "booking"]. Auto-inferred from category if omitted.'),
            'sort_order' => $schema->integer()->description('Sorting weight for order of appearance. Default is 0.'),
            'is_active' => $schema->boolean()->description('Publish status. Default is true.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $rawQuestion = $request->get('question');
        if (empty($rawQuestion)) {
            return Response::error('The "question" parameter is required.');
        }

        $question = $this->normalizeMultilingualInput($rawQuestion);
        if (empty($question['id'])) {
            return Response::error('Question text for Indonesian (id) cannot be empty.');
        }

        $rawAnswer = $request->get('answer');
        if (empty($rawAnswer)) {
            return Response::error('The "answer" parameter is required.');
        }

        $answer = $this->normalizeMultilingualInput($rawAnswer);
        if (empty($answer['id'])) {
            return Response::error('Answer text for Indonesian (id) cannot be empty.');
        }

        $rawCategory = (string) $request->get('category', 'general');
        $categoryEnum = FaqCategory::tryFrom($rawCategory) ?? FaqCategory::General;

        $rawContexts = $request->get('contexts');
        if (is_array($rawContexts) && ! empty($rawContexts)) {
            $contexts = collect($rawContexts)
                ->map(fn ($c) => is_string($c) ? trim($c) : '')
                ->filter(fn ($c) => FaqContext::tryFrom($c) !== null)
                ->values()
                ->all();
        } else {
            $contexts = match ($categoryEnum) {
                FaqCategory::Tour => ['home', 'tour'],
                FaqCategory::Umrah => ['home', 'umrah'],
                FaqCategory::Vehicle => ['home', 'vehicle'],
                FaqCategory::Payment => ['home', 'booking'],
                default => ['home'],
            };
        }

        $faq = Faq::create([
            'question' => $question,
            'answer' => $answer,
            'category' => $categoryEnum,
            'contexts' => $contexts,
            'sort_order' => max(0, (int) $request->get('sort_order', 0)),
            'is_active' => $request->has('is_active') ? (bool) $request->get('is_active') : true,
        ]);

        return Response::json([
            'message' => 'FAQ berhasil dibuat.',
            'faq' => [
                'id' => $faq->id,
                'category' => $faq->category?->value,
                'category_label' => $faq->category?->getLabel(),
                'contexts' => $faq->contexts,
                'question' => $faq->getTranslations('question'),
                'answer' => $faq->getTranslations('answer'),
                'sort_order' => $faq->sort_order,
                'is_active' => (bool) $faq->is_active,
                'created_at' => $faq->created_at?->toIso8601String(),
            ],
        ]);
    }
}
