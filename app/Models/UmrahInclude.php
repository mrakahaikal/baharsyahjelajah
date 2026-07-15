<?php

namespace App\Models;

use Database\Factories\UmrahIncludeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

#[Fillable(['package_id', 'item', 'type', 'sort_order'])]
class UmrahInclude extends Model
{
    /** @use HasFactory<UmrahIncludeFactory> */
    use HasFactory;

    use HasTranslations;

    public array $translatable = ['item'];

    protected $casts = [
        'item' => 'array',
        'sort_order' => 'integer',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(UmrahPackage::class, 'package_id');
    }

    public function scopeIncludes(Builder $query): Builder
    {
        return $query->where('type', 'include');
    }

    public function scopeExcludes(Builder $query): Builder
    {
        return $query->where('type', 'exclude');
    }

    public function scopeRequirements(Builder $query): Builder
    {
        return $query->where('type', 'requirement');
    }

    public function scopeNotes(Builder $query): Builder
    {
        return $query->where('type', 'note');
    }
}
