<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $categories = PostCategory::query()
            ->withCount([
                'posts' => fn ($query) => $query->where('status', 'published'),
            ])
            ->orderBy('name')
            ->get();

        $posts = Post::query()
            ->where('status', 'published')
            ->with(['category', 'author'])
            ->when($request->string('category')->isNotEmpty(), function ($query) use ($request): void {
                $categorySlug = $request->string('category')->toString();

                $query->whereHas('category', function ($categoryQuery) use ($categorySlug): void {
                    $categoryQuery
                        ->where('slug->'.app()->getLocale(), $categorySlug)
                        ->orWhere('slug->id', $categorySlug);
                });
            })
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('pages.blog.index', compact('categories', 'posts'));
    }

    public function show(string $locale, string $post): View
    {
        /** @var Post $post */
        $post = $this->findByTranslatedSlug(Post::class, $post);

        abort_unless($post->status === 'published', 404);

        $post->load(['category', 'author']);

        $relatedPosts = Post::query()
            ->where('status', 'published')
            ->with('category')
            ->whereKeyNot($post->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('pages.blog.show', compact('post', 'relatedPosts'));
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function findByTranslatedSlug(string $model, string $slug): Model
    {
        return $model::query()
            ->where('slug->'.app()->getLocale(), $slug)
            ->orWhere('slug->id', $slug)
            ->firstOrFail();
    }
}
