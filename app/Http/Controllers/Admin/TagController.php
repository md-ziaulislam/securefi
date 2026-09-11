<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::withCount('articles')->orderBy('name')->get();
        return view('admin.tags.index', compact('tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:tags,slug',
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);

        Tag::create([
            'name' => $validated['name'],
            'slug' => $slug,
        ]);

        return back()->with('success', 'Tag created.');
    }

    /**
     * Quick-create a tag inline from the article editor (AJAX).
     * Returns existing tag if a duplicate name/slug is found.
     */
    public function quickCreate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $slug = Str::slug($validated['name']);

        $tag = Tag::firstOrCreate(
            ['slug' => $slug],
            ['name' => trim($validated['name'])]
        );

        return response()->json([
            'id'   => $tag->id,
            'name' => $tag->name,
            'slug' => $tag->slug,
        ]);
    }

    public function destroy(Tag $tag)
    {
        $tag->articles()->detach();
        $tag->delete();
        return back()->with('success', 'Tag deleted.');
    }
}
