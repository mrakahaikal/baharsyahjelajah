<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

#[Fillable(['tour_package_id', 'item', 'type', 'sort_order'])]
class TourPackageInclude extends Model
{
    use HasTranslations;

    protected $table = 'tour_includes';

    public array $translatable = ['item'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function scopeIncludes(Builder $query): Builder
    {
        return $query->where('type', 'include');
    }

    public function scopeExcludes(Builder $query): Builder
    {
        return $query->where('type', 'exclude');
    }

    public function scopeNotes(Builder $query): Builder
    {
        return $query->where('type', 'note');
    }

    public function getIconAttribute(): string
    {
        return match ($this->type) {
            'include' => '✓',
            'exclude' => '✗',
            'note'    => 'ℹ',
            default   => '•',
        };
    }
}
