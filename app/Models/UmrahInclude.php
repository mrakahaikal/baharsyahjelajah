<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

#[Fillable(['package_id', 'item', 'type', 'sort_order'])]
class UmrahInclude extends Model
{
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

    public function scopeIncludes($query)
    {
        return $query->where('type', 'include');
    }

    public function scopeExcludes($query)
    {
        return $query->where('type', 'exclude');
    }
}
