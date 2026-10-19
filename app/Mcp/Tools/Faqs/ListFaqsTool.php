<?php

namespace App\Mcp\Tools\Faqs;

use App\Enums\FaqCategory;
use App\Enums\FaqContext;
use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Faq;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_faqs')]
#[Title('List Frequently Asked Questions')]
#[Description('List frequently asked questions (FAQs) with filters by category, display context, search query, active status, and pagination.')]
class ListFaqsTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'category' => $schema->string()->description('Filter by FAQ category: "general", "tour", "umrah", "vehicle", or "payment".'),
            'context' => $schema->string()->description('Filter by display context: "home", "tour", "umrah", "vehicle", or "booking".'),
            'search' => $schema->string()->description('Search keyword matching question or answer text across all languages.'),
            'is_active' => $schema->boolean()->description('Filter by active status (true/false). Default is true.'),
            'locale' => $schema->string()->description('Preferred output locale ("id", "en", "ms"). Default is "id".'),
            'per_page' => $schema->integer()->description('Number of results per page (1-50, default: 20).'),
            'page' => $schema->integer()->description('Page number for pagination (default: 1).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $locale = (string) $request->get('locale', 'id');
        $perPage = min(50, max(1, (int) $request->get('per_page', 20)));
        $page = max(1, (int) $request->get('page', 1));

        $query = Faq::query();

        if ($request->has('is_active')) {
            $query->where('is_active', (bool) $request->get('is_active'));
        }

        if ($request->filled('category')) {
            $category = (string) $request->get('category');
            $enumCat = FaqCategory::tryFrom($category);
            $query->where('category', $enumCat?->value ?? $category);
        }

        if ($request->filled('context')) {
            $context = (string) $request->get('context');
            $enumContext = FaqContext::tryFrom($context);
            $query->forContext($enumContext ?? $context);
        }

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function (Builder $sub) use ($search): void {
                $sub->where('question->id', 'like', "%{$search}%")
                    ->orWhere('question->en', 'like', "%{$search}%")
                    ->orWhere('question->ms', 'like', "%{$search}%")
                    ->orWhere('answer->id', 'like', "%{$search}%")
                    ->orWhere('answer->en', 'like', "%{$search}%")
                    ->orWhere('answer->ms', 'like', "%{$search}%");
            });
        }

        $query->orderBy('sort_order')->orderBy('id');

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        $faqs = collect($paginator->items())->map(function (Faq $faq) use ($locale): array {
            return [
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
            ];
        })->all();

        return Response::json([
            'faqs' => $faqs,
            'pagination' => [
                'total' => $paginator->total(),
                'count' => count($faqs),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
            ],
            'locale' => $locale,
        ]);
    }
}
