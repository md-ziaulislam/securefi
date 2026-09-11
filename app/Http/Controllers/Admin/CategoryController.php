<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        // Load all main categories with their subcategories and articles count
        $rootCategories = Category::whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->withCount('articles')->orderBy('order')->orderBy('name');
            }])
            ->withCount('articles')
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        // Build hierarchical list so subcategories appear directly under their main category
        $categories = collect();
        foreach ($rootCategories as $root) {
            $root->depth = 0;
            $categories->push($root);

            foreach ($root->children as $child) {
                $child->depth = 1;
                $categories->push($child);
            }
        }

        // Catch any orphan subcategories whose parent might not exist
        $orphanSubcategories = Category::whereNotNull('parent_id')
            ->whereNotIn('parent_id', $rootCategories->pluck('id'))
            ->withCount('articles')
            ->get();
        foreach ($orphanSubcategories as $orphan) {
            $orphan->depth = 1;
            $categories->push($orphan);
        }

        $editingCategory = null;
        if ($request->filled('edit')) {
            $editingCategory = Category::find($request->edit);
        }

        // Available parent categories for dropdown (exclude current category when editing)
        $parentQuery = Category::whereNull('parent_id')->orderBy('order')->orderBy('name');
        if ($editingCategory) {
            $parentQuery->where('id', '!=', $editingCategory->id);
        }
        $parentCategories = $parentQuery->get();

        return view('admin.categories.index', compact('categories', 'rootCategories', 'parentCategories', 'editingCategory'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:categories,id',
            'slug' => 'nullable|string|max:100|unique:categories,slug',
            'description' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:500',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);

        $category = Category::create([
            'parent_id' => !empty($validated['parent_id']) ? $validated['parent_id'] : null,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'order' => $validated['order'] ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        $type = $category->parent_id ? 'Subcategory' : 'Main category';
        return back()->with('success', "{$type} '{$category->name}' added successfully.");
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                function ($attribute, $value, $fail) use ($category) {
                    if ($value && (int) $value === (int) $category->id) {
                        $fail('A category cannot be its own parent.');
                    }
                    if ($value && $category->children()->pluck('id')->contains((int) $value)) {
                        $fail('Cannot assign a subcategory as its own parent (circular relationship).');
                    }
                },
            ],
            'slug' => 'required|string|max:100|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:500',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $category->update([
            'parent_id' => !empty($validated['parent_id']) ? $validated['parent_id'] : null,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'description' => $validated['description'],
            'meta_title' => $validated['meta_title'],
            'meta_description' => $validated['meta_description'],
            'order' => $validated['order'] ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.categories.index')->with('success', "Category '{$category->name}' updated successfully.");
    }

    public function destroy(Category $category)
    {
        if ($category->children()->count() > 0) {
            return back()->withErrors([
                'category' => "Cannot delete '{$category->name}' because it has {$category->children()->count()} subcategories attached. Please delete or reassign its subcategories first."
            ]);
        }

        if ($category->articles()->count() > 0) {
            return back()->withErrors([
                'category' => "Cannot delete '{$category->name}' because it contains {$category->articles()->count()} articles. Reassign those articles first."
            ]);
        }

        $name = $category->name;
        $category->delete();

        return back()->with('success', "Category '{$name}' deleted.");
    }
}
