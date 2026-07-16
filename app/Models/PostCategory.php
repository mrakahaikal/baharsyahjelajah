<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'slug', 'description'])]
class PostCategory extends Model
{
    use HasLocalizedSlug, HasTranslations;

    public array $translatable = ['name', 'slug', 'description'];

    protected $casts = [
        'name' => 'array',
        'slug' => 'array',
        'description' => 'array',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
