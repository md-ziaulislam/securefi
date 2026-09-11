<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleApiController extends Controller
{
    /**
     * Get paginated articles with optional category/tag/search filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Article::published()->with(['category', 'tags', 'author']);

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->query('category'));
            });
        }

        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->query('tag'));
            });
        }

        if ($request->filled('q')) {
            $term = $request->query('q');
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('excerpt', 'like', "%{$term}%");
            });
        }

        $articles = $query->latest('published_at')->paginate($request->integer('per_page', 10));

        $data = $articles->through(function ($art) {
            return [
                'id' => $art->id,
                'title' => $art->title,
                'slug' => $art->slug,
                'excerpt' => $art->excerpt,
                'featured_image' => $art->featured_image ? asset($art->featured_image) : null,
                'reading_time' => $art->reading_time,
                'view_count' => $art->view_count,
                'performance_score' => $art->performance_score,
                'is_featured' => $art->is_featured,
                'category' => [
                    'name' => $art->category->name,
                    'slug' => $art->category->slug,
                ],
                'tags' => $art->tags->map(fn($t) => ['name' => $t->name, 'slug' => $t->slug]),
                'author' => [
                    'name' => $art->author->name ?? 'SecuroFi Editorial',
                ],
                'url' => route('article.show', $art->slug),
                'published_at' => $art->published_at->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data->items(),
            'pagination' => [
                'total' => $articles->total(),
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
            ],
        ]);
    }

    /**
     * Retrieve single article details.
     */
    public function show(string $slug): JsonResponse
    {
        $article = Article::published()
            ->where('slug', $slug)
            ->with(['category', 'tags', 'author', 'seoMeta'])
            ->firstOrFail();

        // Increment views
        $article->increment('view_count');

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'content' => $article->content,
                'featured_image' => $article->featured_image ? asset($article->featured_image) : null,
                'reading_time' => $article->reading_time,
                'view_count' => $article->view_count,
                'performance_score' => $article->performance_score,
                'is_featured' => $article->is_featured,
                'category' => [
                    'name' => $article->category->name,
                    'slug' => $article->category->slug,
                ],
                'tags' => $article->tags->map(fn($t) => ['name' => $t->name, 'slug' => $t->slug]),
                'author' => [
                    'name' => $article->author->name ?? 'SecuroFi Editorial',
                ],
                'seo' => [
                    'meta_title' => $article->seoMeta?->meta_title ?? $article->title,
                    'meta_description' => $article->seoMeta?->meta_description ?? $article->excerpt,
                    'canonical_url' => $article->seoMeta?->canonical_url ?? route('article.show', $article->slug),
                ],
                'url' => route('article.show', $article->slug),
                'published_at' => $article->published_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * List all categories.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->withCount(['articles' => fn($q) => $q->published()])
            ->orderBy('order')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'description' => $c->description,
                'articles_count' => $c->articles_count,
                'url' => route('category.show', $c->slug),
            ]);

        return response()->json([
            'status' => 'success',
            'data' => $categories,
        ]);
    }

    /**
     * Platform high-level public stats.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'published_articles' => Article::published()->count(),
                'categories' => Category::where('is_active', true)->count(),
                'total_views' => Article::sum('view_count'),
            ],
        ]);
    }
}
