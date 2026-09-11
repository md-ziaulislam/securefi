<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function show(string $slug)
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $articles = $category->articles()
            ->published()
            ->with(['author', 'tags'])
            ->latest('published_at')
            ->paginate(9);

        $currentCategory = $category;
        $currentCategoryId = $category->id;

        return view('frontend.category', compact('category', 'articles', 'currentCategory', 'currentCategoryId'));
    }
}
