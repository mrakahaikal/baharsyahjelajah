<?php

namespace App\Support;

use App\Models\Post;
use App\Models\TourPackage;
use Illuminate\Support\Collection;

class ErrorPageRecommendations
{
    /**
     * @return array{
     *     packages: Collection<int, TourPackage>,
     *     posts: Collection<int, Post>
     * }
     */
    public function get(): array
    {
        $packages = TourPackage::query()
            ->whereHas('tour', fn ($query) => $query->active())
            ->with([
                'media',
                'tour:id,name,slug,is_active',
            ])
            ->latest()
            ->limit(2)
            ->get();

        $posts = Post::query()
            ->published()
            ->with(['author', 'category'])
            ->latest('published_at')
            ->limit(3)
            ->get();

        return compact('packages', 'posts');
    }
}
