<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim($request->input('q', ''));

        $articles = collect();

        if (!empty($query)) {
            $articles = Article::published()
                ->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                      ->orWhere('excerpt', 'LIKE', "%{$query}%")
                      ->orWhere('content', 'LIKE', "%{$query}%");
                })
                ->with(['category', 'author'])
                ->latest('published_at')
                ->paginate(9)
                ->withQueryString();
        }

        return view('frontend.search', compact('query', 'articles'));
    }
}
