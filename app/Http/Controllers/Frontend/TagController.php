<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Tag;

class TagController extends Controller
{
    public function show(string $slug)
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $articles = $tag->articles()
            ->published()
            ->with(['category', 'author'])
            ->latest('published_at')
            ->paginate(9);

        return view('frontend.tag', compact('tag', 'articles'));
    }
}
