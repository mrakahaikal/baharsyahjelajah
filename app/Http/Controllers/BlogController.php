<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlogIndexRequest;
use App\Models\Post;
use App\Models\PostCategory;
use App\Support\PostContentPresenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function __construct(private PostContentPresenter $postContentPresenter) {}

    public function index(BlogIndexRequest $request): View
    {
        $locale = app()->getLocale();
        $searchTerm = $request->searchTerm();
        $categorySlug = $request->categorySlug();

        $categories = PostCategory::query()
            ->withCount([
                'posts as published_posts_count' => fn (Builder $query) => $query->published(),
            ])
            ->whereHas('posts', fn (Builder $query) => $query->published())
            ->orderBy("name->{$locale}")
            ->get();

        $postsQuery = Post::query()
            ->published()
            ->with(['category', 'author'])
            ->when($searchTerm !== '', function (Builder $query) use ($locale, $searchTerm): void {
                $query->where(function (Builder $query) use ($locale, $searchTerm): void {
                    $query
                        ->where("title->{$locale}", 'like', "%{$searchTerm}%")
                        ->orWhere('title->id', 'like', "%{$searchTerm}%")
                        ->orWhere("excerpt->{$locale}", 'like', "%{$searchTerm}%")
                        ->orWhere('excerpt->id', 'like', "%{$searchTerm}%");
                });
            })
            ->when($categorySlug !== '', function (Builder $query) use ($categorySlug, $locale): void {
                $query->whereHas('category', function (Builder $categoryQuery) use ($categorySlug, $locale): void {
                    $categoryQuery
                        ->where("slug->{$locale}", $categorySlug)
                        ->orWhere('slug->id', $categorySlug);
                });
            });

        $featuredPost = null;

        if ($searchTerm === '' && $categorySlug === '') {
            $featuredPost = Post::query()
                ->published()
                ->with(['category', 'author'])
                ->latest('published_at')
                ->latest('id')
                ->first();

            $postsQuery->when($featuredPost, fn (Builder $query) => $query->whereKeyNot($featuredPost->id));
        }

        $posts = $postsQuery
            ->latest('published_at')
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        $activeCategory = $categories->first(
            fn (PostCategory $category): bool => $category->slug === $categorySlug
                || $category->getTranslation('slug', 'id') === $categorySlug,
        );
        $alternateUrls = $this->localizedBlogUrls();
        $canonicalUrl = $alternateUrls[$locale];
        $showFeaturedPost = $featuredPost !== null && $request->integer('page', 1) === 1;

        return view('pages.blog.index', compact(
            'activeCategory',
            'alternateUrls',
            'canonicalUrl',
            'categories',
            'categorySlug',
            'featuredPost',
            'posts',
            'searchTerm',
            'showFeaturedPost',
        ));
    }

    public function show(string $locale, string $post): View|RedirectResponse
    {
        $requestedSlug = $post;

        $post = $this->findPublishedPostByTranslatedSlug($requestedSlug);
        $localizedSlug = $post->getTranslation('slug', $locale, false)
            ?: $post->getTranslation('slug', 'id');

        if ($requestedSlug !== $localizedSlug) {
            return redirect()->route('blog.show', [
                'locale' => $locale,
                'post' => $localizedSlug,
            ], 301);
        }

        $post->load(['category', 'author']);

        $relatedPosts = Post::query()
            ->published()
            ->with(['category', 'author'])
            ->whereKeyNot($post->id)
            ->when(
                $post->post_category_id,
                fn (Builder $query) => $query->where('post_category_id', $post->post_category_id),
            )
            ->latest('published_at')
            ->limit(3)
            ->get();

        if ($relatedPosts->count() < 3) {
            $fallbackPosts = Post::query()
                ->published()
                ->with(['category', 'author'])
                ->whereKeyNot($post->id)
                ->whereNotIn('id', $relatedPosts->modelKeys())
                ->latest('published_at')
                ->limit(3 - $relatedPosts->count())
                ->get();

            $relatedPosts = $relatedPosts->concat($fallbackPosts);
        }

        $presentedContent = $this->postContentPresenter->present((string) $post->content);
        $alternateUrls = $this->localizedPostUrls($post);
        $canonicalUrl = $alternateUrls[$locale];

        return view('pages.blog.show', compact(
            'alternateUrls',
            'canonicalUrl',
            'post',
            'presentedContent',
            'relatedPosts',
        ));
    }

    private function findPublishedPostByTranslatedSlug(string $slug): Post
    {
        return Post::query()
            ->published()
            ->where(fn (Builder $query): Builder => $query
                ->where('slug->'.app()->getLocale(), $slug)
                ->orWhere('slug->id', $slug))
            ->firstOrFail();
    }

    /** @return array<string, string> */
    private function localizedBlogUrls(): array
    {
        return collect(['id', 'ms', 'en'])
            ->mapWithKeys(fn (string $locale): array => [
                $locale => route('blog.index', ['locale' => $locale]),
            ])
            ->all();
    }

    /** @return array<string, string> */
    private function localizedPostUrls(Post $post): array
    {
        return collect(['id', 'ms', 'en'])
            ->mapWithKeys(function (string $locale) use ($post): array {
                $slug = $post->getTranslation('slug', $locale, false)
                    ?: $post->getTranslation('slug', 'id');

                return [
                    $locale => route('blog.show', [
                        'locale' => $locale,
                        'post' => $slug,
                    ]),
                ];
            })
            ->all();
    }
}
