<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index(Request $request)
    {
        $pages = Page::orderBy('menu_order')->get();
        $submissions = ContactSubmission::latest('submitted_at')->paginate(15);
        $tab = $request->get('tab', 'pages');

        return view('admin.pages.index', compact('pages', 'submissions', 'tab'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'slug' => 'nullable|string|max:200|unique:pages,slug',
            'content' => 'required|string',
            'template' => 'required|in:default,legal,contact',
            'status' => 'required|in:draft,published',
            'show_in_menu' => 'nullable|boolean',
            'show_in_footer' => 'nullable|boolean',
            'menu_order' => 'nullable|integer',
            // Geo-Targeting (PRD-ADDNEW 5.1)
            'country_rule' => 'nullable|in:all,include,exclude',
            'countries' => 'nullable|array',
            'countries.*' => 'string|size:2',
            'restriction_fallback_message' => 'nullable|string|max:1000',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|url|max:500',
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']);

        Page::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'],
            'template' => $validated['template'],
            'status' => $validated['status'],
            'country_rule' => $validated['country_rule'] ?? 'all',
            'countries' => !empty($validated['countries']) ? array_values(array_unique($validated['countries'])) : null,
            'restriction_fallback_message' => $validated['restriction_fallback_message'] ?? null,
            'show_in_menu' => $request->has('show_in_menu'),
            'show_in_footer' => $request->has('show_in_footer'),
            'menu_order' => $validated['menu_order'] ?? 0,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'og_image' => $validated['og_image'] ?? null,
        ]);

        return redirect()->route('admin.pages.index')->with('success', 'Page created successfully.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'slug' => 'required|string|max:200|unique:pages,slug,' . $page->id,
            'content' => 'required|string',
            'template' => 'required|in:default,legal,contact',
            'status' => 'required|in:draft,published',
            'show_in_menu' => 'nullable|boolean',
            'show_in_footer' => 'nullable|boolean',
            'menu_order' => 'nullable|integer',
            // Geo-Targeting (PRD-ADDNEW 5.1)
            'country_rule' => 'nullable|in:all,include,exclude',
            'countries' => 'nullable|array',
            'countries.*' => 'string|size:2',
            'restriction_fallback_message' => 'nullable|string|max:1000',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|url|max:500',
        ]);

        $page->update([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['slug']),
            'content' => $validated['content'],
            'template' => $validated['template'],
            'status' => $validated['status'],
            'country_rule' => $validated['country_rule'] ?? 'all',
            'countries' => !empty($validated['countries']) ? array_values(array_unique($validated['countries'])) : null,
            'restriction_fallback_message' => $validated['restriction_fallback_message'] ?? null,
            'show_in_menu' => $request->has('show_in_menu'),
            'show_in_footer' => $request->has('show_in_footer'),
            'menu_order' => $validated['menu_order'] ?? 0,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'og_image' => $validated['og_image'] ?? null,
        ]);

        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully.');
    }

    public function destroy(Page $page)
    {
        $page->delete();
        return redirect()->route('admin.pages.index')->with('success', 'Page deleted.');
    }

    public function toggleContact(ContactSubmission $contact)
    {
        $contact->update(['is_read' => !$contact->is_read]);
        return back()->with('success', 'Submission status updated.');
    }

    public function destroyContact(ContactSubmission $contact)
    {
        $contact->delete();
        return back()->with('success', 'Contact submission deleted.');
    }
}
