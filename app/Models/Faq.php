<?php

namespace App\Models;

use App\Enums\FaqCategory;
use App\Enums\FaqContext;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'question', 'answer', 'category', 'contexts', 'sort_order', 'is_active',
])]
class Faq extends Model
{
    use HasTranslations;

    protected $attributes = [
        'sort_order' => 0,
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'category' => FaqCategory::class,
            'contexts' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public array $translatable = ['question', 'answer'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory(Builder $query, FaqCategory|string $category): Builder
    {
        return $query->where('category', $category instanceof FaqCategory ? $category->value : $category);
    }

    public function scopeForContext(Builder $query, FaqContext|string $context): Builder
    {
        return $query->whereJsonContains(
            'contexts',
            $context instanceof FaqContext ? $context->value : $context,
        );
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
