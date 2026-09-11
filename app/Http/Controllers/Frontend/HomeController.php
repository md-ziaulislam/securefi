<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        // 1. Featured Article for Split-Screen Hero
        $heroArticle = Article::featured()->with(['category', 'author'])->latest('published_at')->first()
            ?: Article::published()->with(['category', 'author'])->latest('published_at')->first();

        // 2. Secondary Featured Articles (Excluding hero)
        $featuredArticles = Article::featured()
            ->when($heroArticle, fn($q) => $q->where('id', '!=', $heroArticle->id))
            ->with(['category', 'author'])
            ->take(3)
            ->get();

        // 3. Latest Published Articles
        $latestArticles = Article::published()
            ->when($heroArticle, fn($q) => $q->where('id', '!=', $heroArticle->id))
            ->with(['category', 'author', 'tags'])
            ->latest('published_at')
            ->take(6)
            ->get();

        // 4. Root Categories only, with subcategories eager-loaded
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->withCount(['articles' => fn($q) => $q->published()])
            ->with(['children' => function ($q) {
                $q->where('is_active', true)
                  ->withCount(['articles' => fn($q2) => $q2->published()])
                  ->orderBy('order')->orderBy('name');
            }])
            ->orderBy('order')
            ->get();

        // 5. Trending Articles (by view count)
        $trendingArticles = Article::published()
            ->with('category')
            ->orderByDesc('view_count')
            ->take(4)
            ->get();

        return view('frontend.home', compact(
            'heroArticle',
            'featuredArticles',
            'latestArticles',
            'categories',
            'trendingArticles'
        ));
    }
}
