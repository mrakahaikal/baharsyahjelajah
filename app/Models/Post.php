<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'post_category_id', 'user_id', 'title', 'slug',
    'excerpt', 'content', 'cover_image', 'status', 'published_at',
])]
class Post extends Model
{
    use HasTranslations;

    public array $translatable = ['title', 'slug', 'excerpt', 'content'];

    protected $casts = [
        'title' => 'array',
        'slug' => 'array',
        'excerpt' => 'array',
        'content' => 'array',
        'published_at' => 'datetime',
    ];

    public function getCoverImageUrlAttribute(): string
    {
        if (str_starts_with($this->cover_image ?? '', 'http')) {
            return $this->cover_image;
        }

        return $this->cover_image 
            ? \Illuminate\Support\Facades\Storage::url($this->cover_image) 
            : 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&q=80&w=800';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
