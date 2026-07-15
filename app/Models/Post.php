<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Storage;
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

    protected static function booted(): void
    {
        static::deleting(fn (Post $post) => $post->destinations()->detach());
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if (str_starts_with($this->cover_image ?? '', 'http')) {
            $coverHost = parse_url($this->cover_image, PHP_URL_HOST);
            $applicationHost = parse_url(config('app.url'), PHP_URL_HOST);
            $coverPath = (string) parse_url($this->cover_image, PHP_URL_PATH);

            if ($coverHost === $applicationHost && str_starts_with($coverPath, '/storage/')) {
                $storagePath = substr($coverPath, strlen('/storage/'));

                return Storage::disk('public')->exists($storagePath)
                    ? '/storage/'.ltrim($storagePath, '/')
                    : null;
            }

            return $this->cover_image;
        }

        if (! $this->cover_image || ! Storage::disk('public')->exists($this->cover_image)) {
            return null;
        }

        return '/storage/'.ltrim($this->cover_image, '/');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function destinations(): MorphToMany
    {
        return $this->morphToMany(Destination::class, 'destinationable')
            ->withTimestamps();
    }
}
